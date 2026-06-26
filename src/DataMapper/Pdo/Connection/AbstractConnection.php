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

namespace Phalcon\DataMapper\Pdo\Connection;

use BadMethodCallException;
use PDO;
use PDOException;
use PDOStatement;
use Phalcon\DataMapper\Pdo\Exception\UnknownDriverMethod;
use Phalcon\DataMapper\Pdo\Profiler\ProfilerInterface;
use Throwable;

use function array_merge;
use function call_user_func_array;
use function current;
use function func_get_args;
use function get_class;
use function implode;
use function is_array;
use function is_bool;
use function is_int;
use function method_exists;
use function str_contains;

/**
 * Provides array quoting, profiling, a new `perform()` method, new `fetch*()`
 * methods
 */
abstract class AbstractConnection implements ConnectionInterface
{
    /**
     * Whether to transparently reconnect and retry once when a statement fails
     * because the connection was lost. Opt-in; off by default.
     *
     * @var bool
     */
    protected bool $autoReconnect = false;

    /**
     * @var PDO|null
     */
    protected $pdo;

    /**
     * @var ProfilerInterface
     */
    protected ProfilerInterface $profiler;

    /**
     * Current transaction nesting level. Tracked locally rather than via
     * PDO::inTransaction() because some drivers report a broken connection as
     * being "in transaction".
     *
     * @var int
     */
    protected int $transactionLevel = 0;

    /**
     * Proxies to PDO methods created for specific drivers; in particular,
     * `sqlite` and `pgsql`.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     * @throws BadMethodCallException
     */
    public function __call(string $name, array $arguments)
    {
        $this->connect();

        if (!method_exists($this->pdo, $name)) {
            $className = get_class($this);
            $message   = "Class '" . $className
                       . "' does not have a method '" . $name . "'";

            throw new UnknownDriverMethod($message);
        }

        return call_user_func_array(
            [
                $this->pdo,
                $name,
            ],
            $arguments
        );
    }

    /**
     * Return an array of available PDO drivers (empty array if none available)
     *
     * @return array
     */
    public static function getAvailableDrivers(): array
    {
        return PDO::getAvailableDrivers();
    }

    /**
     * Begins a transaction. If the profiler is enabled, the operation will
     * be recorded.
     *
     * @return bool
     */
    public function beginTransaction(): bool
    {
        $this->connect();
        $this->profiler->start(__FUNCTION__);

        $result = $this->pdo->beginTransaction();

        $this->profiler->finish();

        $this->transactionLevel++;

        return $result;
    }

    /**
     * Commits the existing transaction. If the profiler is enabled, the
     * operation will be recorded.
     *
     * @return bool
     */
    public function commit(): bool
    {
        $this->connect();
        $this->profiler->start(__FUNCTION__);

        $result = $this->pdo->commit();

        $this->profiler->finish();

        if ($this->transactionLevel > 0) {
            $this->transactionLevel--;
        }

        return $result;
    }

    /**
     * Connects to the database.
     */
    abstract public function connect(): void;

    /**
     * Disconnects from the database.
     */
    abstract public function disconnect(): void;

    /**
     * Ensures the connection is alive, reconnecting in place if it is not.
     * disconnect() is required first because connect() is idempotent and will
     * not rebuild a dead-but-present handle.
     *
     * @return void
     */
    public function ensureConnection(): void
    {
        if (!$this->ping()) {
            $this->disconnect();
            $this->connect();
        }
    }

    /**
     * Gets the most recent error code.
     *
     * @return string|null
     */
    public function errorCode(): string | null
    {
        $this->connect();

        return $this->pdo->errorCode();
    }

    /**
     * Gets the most recent error info.
     *
     * @return array
     */
    public function errorInfo(): array
    {
        $this->connect();

        return $this->pdo->errorInfo();
    }

    /**
     * Executes an SQL statement and returns the number of affected rows. If
     * the profiler is enabled, the operation will be recorded.
     *
     * @param string $statement
     *
     * @return int
     */
    public function exec(string $statement): int
    {
        $this->connect();
        $this->profiler->start(__FUNCTION__);

        try {
            $affectedRows = $this->pdo->exec($statement);
        } catch (PDOException $exception) {
            if (!$this->canReconnect($exception)) {
                throw $exception;
            }

            $this->reconnect();

            $affectedRows = $this->pdo->exec($statement);
        }

        $this->profiler->finish($statement);

        return $affectedRows;
    }

    /**
     * Performs a statement and returns the number of affected rows.
     *
     * @param string $statement
     * @param array  $values
     *
     * @return int
     */
    public function fetchAffected(string $statement, array $values = []): int
    {
        $sth = $this->perform($statement, $values);

        return $sth->rowCount();
    }

    /**
     * Fetches a sequential array of rows from the database; the rows are
     * returned as associative arrays.
     *
     * @param string $statement
     * @param array  $values
     *
     * @return array
     */
    public function fetchAll(string $statement, array $values = []): array
    {
        return $this->fetchData(
            "fetchAll",
            [PDO::FETCH_ASSOC],
            $statement,
            $values
        );
    }

    /**
     * Fetches an associative array of rows from the database; the rows are
     * returned as associative arrays, and the array of rows is keyed on the
     * first column of each row.
     *
     * If multiple rows have the same first column value, the last row with
     * that value will overwrite earlier rows. This method is more resource
     * intensive and should be avoided if possible.
     *
     * @param string $statement
     * @param array  $values
     *
     * @return array
     */
    public function fetchAssoc(string $statement, array $values = []): array
    {
        $data = [];
        $sth  = $this->perform($statement, $values);

        $row = $sth->fetch(PDO::FETCH_ASSOC);
        while ($row) {
            $data[current($row)] = $row;

            $row = $sth->fetch(PDO::FETCH_ASSOC);
        }

        return $data;
    }

    /**
     * Fetches a column of rows as a sequential array (default first one).
     *
     * @param string $statement
     * @param array  $values
     * @param int    $column
     *
     * @return array
     */
    public function fetchColumn(
        string $statement,
        array $values = [],
        int $column = 0
    ): array {
        return $this->fetchData(
            "fetchAll",
            [PDO::FETCH_COLUMN, $column],
            $statement,
            $values
        );
    }

    /**
     * Fetches multiple from the database as an associative array. The first
     * column will be the index key. The default flags are
     * PDO::FETCH_ASSOC | PDO::FETCH_GROUP
     *
     * @param string $statement
     * @param array  $values
     * @param int    $flags
     *
     * @return array
     */
    public function fetchGroup(
        string $statement,
        array $values = [],
        int $flags = PDO::FETCH_ASSOC
    ): array {
        return $this->fetchData(
            "fetchAll",
            [PDO::FETCH_GROUP | $flags],
            $statement,
            $values
        );
    }

    /**
     * Fetches one row from the database as an object where the column values
     * are mapped to object properties.
     *
     * Since PDO injects property values before invoking the constructor, any
     * initializations for defaults that you potentially have in your object's
     * constructor, will override the values that have been injected by
     * `fetchObject`. The default object returned is `\stdClass`
     *
     * @param string $statement
     * @param array  $values
     * @param string $class
     * @param array  $arguments
     *
     * @return object
     */
    public function fetchObject(
        string $statement,
        array $values = [],
        string $className = "stdClass",
        array $arguments = []
    ): object {
        $sth = $this->perform($statement, $values);

        return $sth->fetchObject($className, $arguments);
    }

    /**
     * Fetches a sequential array of rows from the database; the rows are
     * returned as objects where the column values are mapped to object
     * properties.
     *
     * Since PDO injects property values before invoking the constructor, any
     * initializations for defaults that you potentially have in your object's
     * constructor, will override the values that have been injected by
     * `fetchObject`. The default object returned is `\stdClass`
     *
     * @param string $statement
     * @param array  $values
     * @param string $class
     * @param array  $arguments
     *
     * @return array
     */
    public function fetchObjects(
        string $statement,
        array $values = [],
        string $className = "stdClass",
        array $arguments = []
    ): array {
        $sth = $this->perform($statement, $values);

        return $sth->fetchAll(PDO::FETCH_CLASS, $className, $arguments);
    }

    /**
     * Fetches one row from the database as an associative array.
     *
     * @param string $statement
     * @param array  $values
     *
     * @return array
     */
    public function fetchOne(string $statement, array $values = []): array
    {
        return $this->fetchData(
            "fetch",
            [PDO::FETCH_ASSOC],
            $statement,
            $values
        );
    }

    /**
     * Fetches an associative array of rows as key-value pairs (first column is
     * the key, second column is the value).
     *
     * @param string $statement
     * @param array  $values
     *
     * @return array
     */
    public function fetchPairs(string $statement, array $values = []): array
    {
        return $this->fetchData(
            "fetchAll",
            [PDO::FETCH_KEY_PAIR],
            $statement,
            $values
        );
    }

    /**
     * Fetches the very first value (i.e., first column of the first row).
     *
     * @param string $statement
     * @param array  $values
     *
     * @return mixed
     */
    public function fetchValue(string $statement, array $values = [])
    {
        $sth = $this->perform($statement, $values);

        return $sth->fetchColumn(0);
    }

    /**
     * Return the inner PDO (if any)
     *
     * @return \PDO
     */
    public function getAdapter(): PDO
    {
        $this->connect();

        return $this->pdo;
    }

    /**
     * Retrieve a database connection attribute
     *
     * @param int $attribute
     *
     * @return mixed
     */
    public function getAttribute(int $attribute)
    {
        $this->connect();

        return $this->pdo->getAttribute($attribute);
    }

    /**
     * Returns whether transparent auto-reconnect is enabled.
     *
     * @return bool
     */
    public function getAutoReconnect(): bool
    {
        return $this->autoReconnect;
    }

    /**
     * Return the driver name
     *
     * @return string
     */
    public function getDriverName(): string
    {
        $this->connect();

        return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    /**
     * Returns the Profiler instance.
     *
     * @return ProfilerInterface
     */
    public function getProfiler(): ProfilerInterface
    {
        return $this->profiler;
    }

    /**
     * Gets the quote parameters based on the driver
     *
     * @param string $driver
     *
     * @return array
     */
    public function getQuoteNames(string $driver = ""): array
    {
        $option = $driver;
        if (empty($option)) {
            $option = $this->getDriverName();
        }

        switch ($option) {
            case "mysql":
                $quotes = [
                    "prefix"  => "`",
                    "suffix"  => "`",
                    "find"    => "`",
                    "replace" => "``",
                ];
                break;

            case "sqlsrv":
                $quotes = [
                    "prefix"  => "[",
                    "suffix"  => "]",
                    "find"    => "]",
                    "replace" => "][",
                ];
                break;

            default:
                $quotes = [
                    "prefix"  => "\"",
                    "suffix"  => "\"",
                    "find"    => "\"",
                    "replace" => "\"\"",
                ];
                break;
        }

        return $quotes;
    }

    /**
     * Is a transaction currently active? If the profiler is enabled, the
     * operation will be recorded. If the profiler is enabled, the operation
     * will be recorded.
     *
     * @return bool
     */
    public function inTransaction(): bool
    {
        $this->connect();
        $this->profiler->start(__FUNCTION__);

        $result = $this->pdo->inTransaction();

        $this->profiler->finish();

        return $result;
    }

    /**
     * Is the PDO connection active?
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        return (bool)$this->pdo;
    }

    /**
     * Returns the last inserted autoincrement sequence value. If the profiler
     * is enabled, the operation will be recorded.
     *
     * @param string $name
     *
     * @return string
     */
    public function lastInsertId(?string $name = null): string
    {
        $this->connect();

        $this->profiler->start(__FUNCTION__);

        $result = $this->pdo->lastInsertId($name);

        $this->profiler->finish();

        return (string)$result;
    }

    /**
     * Performs a query with bound values and returns the resulting
     * PDOStatement; array values will be passed through `quote()` and their
     * respective placeholders will be replaced in the query string. If the
     * profiler is enabled, the operation will be recorded.
     *
     * @param string $statement
     * @param array  $values
     *
     * @return \PDOStatement
     */
    public function perform(
        string $statement,
        array $values = []
    ): PDOStatement {
        $this->connect();

        $this->profiler->start(__FUNCTION__);

        try {
            $sth = $this->performStatement($statement, $values);
        } catch (PDOException $exception) {
            if (!$this->canReconnect($exception)) {
                throw $exception;
            }

            $this->reconnect();

            $sth = $this->performStatement($statement, $values);
        }

        $this->profiler->finish($statement, $values);

        return $sth;
    }

    /**
     * Checks whether the underlying connection is still alive by issuing a
     * trivial query. Returns false if there is no handle or the probe fails.
     *
     * @return bool
     */
    public function ping(): bool
    {
        if (null === $this->pdo) {
            return false;
        }

        try {
            $this->pdo->query("SELECT 1");
        } catch (Throwable) {
            return false;
        }

        return true;
    }

    /**
     * Prepares an SQL statement for execution.
     *
     * @param string $statement
     * @param array  $options
     *
     * @return \PDOStatement|false
     */
    public function prepare(
        string $statement,
        array $options = []
    ): PDOStatement | bool {
        $this->connect();

        $this->profiler->start(__FUNCTION__);

        try {
            $sth = $this->pdo->prepare($statement, $options);
        } catch (PDOException $exception) {
            if (!$this->canReconnect($exception)) {
                throw $exception;
            }

            $this->reconnect();

            $sth = $this->pdo->prepare($statement, $options);
        }

        $this->profiler->finish($sth->queryString);

        return $sth;
    }

    /**
     * Queries the database and returns a PDOStatement. If the profiler is
     * enabled, the operation will be recorded.
     *
     * @param string $statement
     * @param mixed  ...$fetch
     *
     * @return \PDOStatement|false
     */
    public function query(string $statement): PDOStatement | bool
    {
        $this->connect();

        $this->profiler->start(__FUNCTION__);

        $arguments = func_get_args();

        try {
            $sth = call_user_func_array([$this->pdo, "query"], $arguments);
        } catch (PDOException $exception) {
            if (!$this->canReconnect($exception)) {
                throw $exception;
            }

            $this->reconnect();

            $sth = call_user_func_array([$this->pdo, "query"], $arguments);
        }

        $this->profiler->finish($sth->queryString);

        return $sth;
    }

    /**
     * Quotes a value for use in an SQL statement. This differs from
     * `PDO::quote()` in that it will convert an array into a string of
     * comma-separated quoted values. The default type is `PDO::PARAM_STR`
     *
     * @param mixed $value
     * @param int   $type
     *
     * @return string The quoted value.
     */
    public function quote($value, int $type = PDO::PARAM_STR): string
    {
        $elements = [];

        $this->connect();

        $element = $value;
        $quotes  = $this->getQuoteNames();

        if (!is_array($element)) {
            $element = (string)$element;

            return $quotes["prefix"] . $element . $quotes["suffix"];
        }

        // quote array values, not keys, then combine with commas
        foreach ($value as $key => $element) {
            $element        = (string)$element;
            $elements[$key] = $quotes["prefix"] . $element . $quotes["suffix"];
        }

        return implode(", ", $elements);
    }

    /**
     * Rolls back the current transaction, and restores autocommit mode. If the
     * profiler is enabled, the operation will be recorded.
     *
     * @return bool
     */
    public function rollBack(): bool
    {
        $this->connect();

        $this->profiler->start(__FUNCTION__);

        $result = $this->pdo->rollBack();

        $this->profiler->finish();

        if ($this->transactionLevel > 0) {
            $this->transactionLevel--;
        }

        return $result;
    }

    /**
     * Set a database connection attribute
     *
     * @param int   $attribute
     * @param mixed $value
     *
     * @return bool
     */
    public function setAttribute(int $attribute, $value): bool
    {
        $this->connect();

        return $this->pdo->setAttribute($attribute, $value);
    }

    /**
     * Enables or disables transparent auto-reconnect on a lost connection.
     *
     * @param bool $autoReconnect
     *
     * @return static
     */
    public function setAutoReconnect(bool $autoReconnect): static
    {
        $this->autoReconnect = $autoReconnect;

        return $this;
    }

    /**
     * Sets the Profiler instance.
     *
     * @param ProfilerInterface $profiler
     */
    public function setProfiler(ProfilerInterface $profiler): static
    {
        $this->profiler = $profiler;

        return $this;
    }

    /**
     * Helper method to get data from PDO based on the method passed
     *
     * @param string $method
     * @param array  $arguments
     * @param string $statement
     * @param array  $values
     *
     * @return array
     */
    protected function fetchData(
        string $method,
        array $arguments,
        string $statement,
        array $values = []
    ): array {
        $sth    = $this->perform($statement, $values);
        $result = call_user_func_array(
            [
                $sth,
                $method,
            ],
            $arguments
        );

        /**
         * If this returns boolean or anything other than an array, return
         * an empty array back
         */
        if (!is_array($result)) {
            $result = [];
        }

        return $result;
    }

    /**
     * Recognizes a lost ("gone away") connection. Detection is driver-agnostic:
     * the driver name is not queried because the underlying connection may be
     * dead by this point. The MySQL error codes and PostgreSQL SQLSTATEs do not
     * overlap, so all known signatures are checked unconditionally.
     *
     * @param Throwable $exception
     *
     * @return bool
     */
    protected function isConnectionError(Throwable $exception): bool
    {
        if ($exception instanceof PDOException) {
            $errorInfo = $exception->errorInfo;
            if (isset($errorInfo[1])) {
                $driverCode = (int)$errorInfo[1];

                if (2006 === $driverCode || 2013 === $driverCode) {
                    return true;
                }
            }
        }

        $sqlState = (string)$exception->getCode();
        if (
            "08003" === $sqlState
            || "08006" === $sqlState
            || "57P01" === $sqlState
            || "57P02" === $sqlState
            || "57P03" === $sqlState
        ) {
            return true;
        }

        $message = $exception->getMessage();

        return str_contains($message, "server has gone away")
            || str_contains($message, "Lost connection")
            || str_contains($message, "server closed the connection unexpectedly")
            || str_contains($message, "no connection to the server");
    }

    /**
     * Bind a value using the proper PDO::PARAM_* type.
     *
     * @param \PDOStatement $statement
     * @param mixed         $name
     * @param mixed         $arguments
     */
    protected function performBind(
        PDOStatement $statement,
        $name,
        $arguments
    ): void {
        $key = $name;
        if (is_int($key)) {
            $key = $key + 1;
        }

        if (is_array($arguments)) {
            if (isset($arguments[1])) {
                $type = $arguments[1];
            } else {
                $type = PDO::PARAM_STR;
            }

            if ($type === PDO::PARAM_BOOL && is_bool($arguments[0])) {
                $arguments[0] = $arguments[0] ? "1" : "0";
            }

            $parameters = array_merge([$key], $arguments);
        } else {
            $parameters = [$key, $arguments];
        }

        call_user_func_array(
            [
                $statement,
                "bindValue",
            ],
            $parameters
        );
    }

    /**
     * Whether a failed statement may be transparently retried after
     * reconnecting. Only when auto-reconnect is on, a handle exists, we are
     * not in a transaction, and the failure is a recognized connection loss.
     *
     * @param Throwable $exception
     *
     * @return bool
     */
    private function canReconnect(Throwable $exception): bool
    {
        if (false === $this->autoReconnect) {
            return false;
        }

        if (null === $this->pdo) {
            return false;
        }

        if (0 !== $this->transactionLevel) {
            return false;
        }

        return $this->isConnectionError($exception);
    }

    /**
     * Prepares, binds, and executes a statement, returning the PDOStatement.
     *
     * @param string $statement
     * @param array  $values
     *
     * @return PDOStatement
     */
    private function performStatement(string $statement, array $values): PDOStatement
    {
        $sth = $this->prepare($statement);
        foreach ($values as $name => $value) {
            $this->performBind($sth, $name, $value);
        }

        $sth->execute();

        return $sth;
    }

    /**
     * Drops the dead handle and rebuilds it. disconnect() first is required
     * because connect() is idempotent.
     *
     * @return void
     */
    private function reconnect(): void
    {
        $this->disconnect();
        $this->connect();
    }
}
