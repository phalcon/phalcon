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

namespace Phalcon\DataMapper\Pdo;

use InvalidArgumentException;
use PDO;
use Phalcon\DataMapper\Pdo\Connection\AbstractConnection;
use Phalcon\DataMapper\Pdo\Profiler\Profiler;
use Phalcon\DataMapper\Pdo\Profiler\ProfilerInterface;

/**
 * Provides array quoting, profiling, a new `perform()` method, new `fetch*()`
 * methods
 */
class Connection extends AbstractConnection
{
    /**
     * @var array
     */
    protected array $arguments = [];

    /**
     * Constructor.
     *
     * This overrides the parent so that it can take connection attributes as a
     * constructor parameter, and set them after connection.
     *
     * @param string                 $dsn
     * @param string|null            $username
     * @param string|null            $password
     * @param array                  $options
     * @param array                  $queries
     * @param ProfilerInterface|null $profiler
     */
    public function __construct(
        string $dsn,
        string $username = null,
        string $password = null,
        array $options = [],
        array $queries = [],
        ProfilerInterface $profiler = null
    ) {
        $parts     = explode(":", $dsn);
        $available = [
            "mysql"  => true,
            "pgsql"  => true,
            "sqlite" => true,
            "mssql"  => true,
        ];

        if (true !== isset($available[$parts[0]])) {
            throw new InvalidArgumentException(
                "Driver not supported [" . $parts[0] . "]"
            );
        }


        // if no error mode is specified, use exceptions
        if (true !== isset($options[PDO::ATTR_ERRMODE])) {
            $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        }

        // Arguments store
        $this->arguments = [
            $dsn,
            $username,
            $password,
            $options,
            $queries,
        ];

        // Create a new profiler if none has been passed
        if (null === $profiler) {
            $profiler = new Profiler();
        }

        $this->setProfiler($profiler);
    }

    /**
     * The purpose of this method is to hide sensitive data from stack traces.
     *
     * @return array
     */
    public function __debugInfo(): array
    {
        return [
            "arguments" => [
                $this->arguments[0],
                "****",
                "****",
                $this->arguments[3],
                $this->arguments[4],
            ],
        ];
    }

    /**
     * Connects to the database.
     *
     * @return void
     */
    public function connect(): void
    {
        if (!$this->pdo) {
            // connect
            $this->profiler->start(__FUNCTION__);

            $dsn      = $this->arguments[0];
            $username = $this->arguments[1];
            $password = $this->arguments[2];
            $options  = $this->arguments[3];
            $queries  = $this->arguments[4];

            $this->pdo = new PDO($dsn, $username, $password, $options);

            $this->profiler->finish();

            // connection-time queries
            foreach ($queries as $query) {
                $this->exec($query);
            }
        }
    }

    /**
     * Disconnects from the database.
     *
     * @return void
     */
    public function disconnect(): void
    {
        $this->profiler->start(__FUNCTION__);

        $this->pdo = null;

        $this->profiler->finish();
    }
}
