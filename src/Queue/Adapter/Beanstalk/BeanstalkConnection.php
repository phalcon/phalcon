<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Queue\Adapter\Beanstalk;

use Phalcon\Queue\Exceptions\Exception;

use function explode;
use function fclose;
use function feof;
use function fsockopen;
use function fwrite;
use function is_resource;
use function pfsockopen;
use function rtrim;
use function stream_get_line;
use function stream_get_meta_data;
use function stream_set_timeout;
use function strlen;

/**
 * Dependency-free socket client for the Beanstalkd work queue, implementing
 * the subset of the 1.2 protocol the adapter needs (use/watch/ignore, put,
 * reserve-with-timeout, delete/release/bury/touch). Recovered and trimmed
 * from the original Phalcon\Queue\Beanstalk transport.
 */
class BeanstalkConnection
{
    /**
     * @var resource|null
     */
    protected $connection = null;

    public function __construct(
        protected string $host = "127.0.0.1",
        protected int $port = 11300,
        protected bool $persistent = false
    ) {
    }

    /**
     * Puts a reserved job into the "buried" state.
     */
    public function buryJob(string $id, int $priority): bool
    {
        $this->write("bury " . $id . " " . $priority);

        return ($this->readStatus()[0] ?? "") === "BURIED";
    }

    /**
     * Opens the socket connection to the Beanstalkd server.
     *
     * @return resource
     */
    public function connect()
    {
        if (is_resource($this->connection)) {
            $this->disconnect();
        }

        if ($this->persistent) {
            $connection = pfsockopen($this->host, $this->port);
        } else {
            $connection = fsockopen($this->host, $this->port);
        }

        if (!is_resource($connection)) {
            throw new Exception("Can't connect to the Beanstalk server");
        }

        stream_set_timeout($connection, -1, 0);

        $this->connection = $connection;

        return $connection;
    }

    /**
     * Removes a job from the server entirely.
     */
    public function deleteJob(string $id): bool
    {
        $this->write("delete " . $id);

        return ($this->readStatus()[0] ?? "") === "DELETED";
    }

    /**
     * Closes the connection to the server.
     */
    public function disconnect(): bool
    {
        if (!is_resource($this->connection)) {
            return false;
        }

        fclose($this->connection);

        $this->connection = null;

        return true;
    }

    /**
     * Removes the named tube from the watch list for the connection.
     */
    public function ignoreTube(string $tube): bool
    {
        $this->write("ignore " . $tube);

        return ($this->readStatus()[0] ?? "") === "WATCHING";
    }

    /**
     * Puts a job on the queue using the currently used tube. Returns the new
     * job id, or false when the server did not accept it.
     */
    public function put(string $data, int $priority, int $delay, int $ttr): int|false
    {
        $length = strlen($data);

        $this->write("put " . $priority . " " . $delay . " " . $ttr . " " . $length . "\r\n" . $data);

        $response = $this->readStatus();
        $status   = $response[0] ?? "";

        if ($status !== "INSERTED" && $status !== "BURIED") {
            return false;
        }

        return (int) ($response[1] ?? 0);
    }

    /**
     * Reads a packet from the socket. Verifies the connection is available
     * first.
     */
    public function read(int $length = 0): string|false
    {
        $connection = $this->connection;

        if (!is_resource($connection)) {
            $connection = $this->connect();
        }

        if ($length) {
            if (feof($connection)) {
                return false;
            }

            $data = rtrim((string) stream_get_line($connection, $length + 2), "\r\n");
            $meta = stream_get_meta_data($connection);

            if (!empty($meta["timed_out"])) {
                throw new Exception("Connection timed out");
            }
        } else {
            $data = stream_get_line($connection, 16384, "\r\n");
        }

        if ($data === "UNKNOWN_COMMAND") {
            throw new Exception("UNKNOWN_COMMAND");
        }

        if ($data === "JOB_TOO_BIG") {
            throw new Exception("JOB_TOO_BIG");
        }

        if ($data === "BAD_FORMAT") {
            throw new Exception("BAD_FORMAT");
        }

        if ($data === "OUT_OF_MEMORY") {
            throw new Exception("OUT_OF_MEMORY");
        }

        return $data;
    }

    /**
     * Reads the latest status line and splits it into tokens.
     *
     * @return string[]
     */
    public function readStatus(): array
    {
        $status = $this->read();

        if ($status === false) {
            return [];
        }

        return explode(" ", $status);
    }

    /**
     * Puts a reserved job back into the ready queue.
     */
    public function releaseJob(string $id, int $priority, int $delay): bool
    {
        $this->write("release " . $id . " " . $priority . " " . $delay);

        return ($this->readStatus()[0] ?? "") === "RELEASED";
    }

    /**
     * Reserves a ready job from a watched tube. A null timeout blocks until a
     * job is available; otherwise it blocks up to timeout seconds. Returns
     * [id, body] or null when none is reserved.
     *
     * @return array{0: string, 1: string|false}|null
     */
    public function reserve(?int $timeout = null): ?array
    {
        if ($timeout !== null) {
            $command = "reserve-with-timeout " . $timeout;
        } else {
            $command = "reserve";
        }

        $this->write($command);

        $response = $this->readStatus();

        if (($response[0] ?? "") !== "RESERVED") {
            return null;
        }

        return [$response[1] ?? "", $this->read((int) ($response[2] ?? 0))];
    }

    /**
     * Extends the time-to-run of a reserved job.
     */
    public function touchJob(string $id): bool
    {
        $this->write("touch " . $id);

        return ($this->readStatus()[0] ?? "") === "TOUCHED";
    }

    /**
     * Changes the tube new jobs are put on. By default this is "default".
     */
    public function useTube(string $tube): bool
    {
        $this->write("use " . $tube);

        return ($this->readStatus()[0] ?? "") === "USING";
    }

    /**
     * Adds the named tube to the watch list for the connection.
     */
    public function watchTube(string $tube): bool
    {
        $this->write("watch " . $tube);

        return ($this->readStatus()[0] ?? "") === "WATCHING";
    }

    /**
     * Writes data to the socket, connecting first when needed.
     */
    public function write(string $data): int|false
    {
        $connection = $this->connection;

        if (!is_resource($connection)) {
            $connection = $this->connect();
        }

        $packet = $data . "\r\n";

        return fwrite($connection, $packet, strlen($packet));
    }
}
