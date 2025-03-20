<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by AtlasPHP
 *
 * @link    https://github.com/atlasphp/Atlas.Pdo
 * @license https://github.com/atlasphp/Atlas.Pdo/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\DataMapper\Pdo;

use InvalidArgumentException;
use PDO;
use Phalcon\DataMapper\Pdo\Connection\AbstractConnection;
use Phalcon\DataMapper\Pdo\Profiler\ProfilerInterface;

/**
 * Provides array quoting, profiling, a new `perform()` method, new `fetch*()`
 * methods
 *
 * @method int|false exec(string $statement = '')
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
     * @param PDO|string               $dsnPdo
     * @param string|null              $username
     * @param string|null              $password
     * @param array<int, int>          $options
     * @param array<array-key, string> $queries
     * @param ProfilerInterface|null   $profiler
     */
    public function __construct(
        PDO | string $dsnPdo,
        string | null $username = null,
        string | null $password = null,
        array $options = [],
        array $queries = [],
        ProfilerInterface | null $profiler = null
    ) {
        if ($dsnPdo instanceof PDO) {
            $this->pdo = $dsnPdo;
        } else {
            $parts     = explode(":", $dsnPdo);
            $available = [
                "mysql"  => true,
                "pgsql"  => true,
                "sqlite" => true,
                "mssql"  => true,
            ];

            if (!isset($available[$parts[0]])) {
                throw new InvalidArgumentException(
                    "Driver not supported [" . $parts[0] . "]"
                );
            }


            // if no error mode is specified, use exceptions
            if (!isset($options[PDO::ATTR_ERRMODE])) {
                $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
            }

            // Arguments store
            $this->arguments = [
                $dsnPdo,
                $username,
                $password,
                $options,
                $queries,
            ];
        }

        $this->profiler = $profiler;
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
            $this->profileStart(__METHOD__);

            [$dsn, $username, $password, $options, $queries] = $this->arguments;
            $this->pdo = new PDO($dsn, $username, $password, $options);

            $this->profileFinish();

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
        $this->pdo = null;
    }

    /**
     * @param mixed ...$arguments
     *
     * @return callable
     */
    public static function factory(mixed ...$arguments): callable
    {
        return fn() => static::new(...$arguments);
    }

    /**
     * @param mixed ...$arguments
     *
     * @return Connection
     */
    public static function new(mixed ...$arguments): Connection
    {
        $dsn = $arguments[0] ?? '';
        if (is_string($dsn) && empty($dsn)) {
            throw new InvalidArgumentException(
                "DSN cannot be empty"
            );
        }

        return new static(...$arguments);
    }
}
