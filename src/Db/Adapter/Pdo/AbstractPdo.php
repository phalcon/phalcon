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

namespace Phalcon\Db\Adapter\Pdo;

use PDO;
use PDOException;
use PDOStatement;
use Phalcon\Db\Adapter\AbstractAdapter;
use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Exceptions\CannotPrepareStatement;
use Phalcon\Db\Exceptions\InvalidBindParameter;
use Phalcon\Db\Exceptions\MatchedParameterNotFound;
use Phalcon\Db\Exceptions\NoActiveTransaction;
use Phalcon\Db\Result\PdoResult;
use Phalcon\Db\ResultInterface;
use Phalcon\Events\Exception as EventsException;
use Phalcon\Support\Settings;
use Throwable;

use function array_merge;
use function implode;
use function intval;
use function is_array;
use function is_int;
use function is_object;
use function is_string;
use function preg_match_all;
use function preg_replace;

/**
 * Phalcon\Db\Adapter\Pdo is the Phalcon\Db that internally uses PDO to connect
 * to a database
 *
 * ```php
 * use Phalcon\Db\Adapter\Pdo\Mysql;
 *
 * $config = [
 *     "host"     => "localhost",
 *     "dbname"   => "blog",
 *     "port"     => 3306,
 *     "username" => "sigma",
 *     "password" => "secret",
 * ];
 *
 * $connection = new Mysql($config);
 *```
 */
abstract class AbstractPdo extends AbstractAdapter
{
    /**
     * Last affected rows
     *
     * @var int
     */
    protected int $affectedRows = 0;

    /**
     * Whether to transparently reconnect and retry once when a query fails
     * because the connection was lost. Opt-in; off by default.
     *
     * @var bool
     */
    protected bool $autoReconnect = false;

    /**
     * PDO Handler
     *
     * @var PDO|null
     */
    protected PDO | null $pdo = null;

    /**
     * Constructor for Phalcon\Db\Adapter\Pdo
     *
     * @param array $descriptor = [
     *                          'host'         => 'localhost',
     *                          'port'         => '3306',
     *                          'dbname'       => 'blog',
     *                          'username'     => 'sigma'
     *                          'password'     => 'secret'
     *                          'dialectClass' => null,
     *                          'options'      => [],
     *                          'dsn'          => null,
     *                          'charset'      => 'utf8mb4',
     *                          ]
     */
    public function __construct(array $descriptor)
    {
        $this->connect($descriptor);

        parent::__construct($descriptor);
    }

    /**
     * Returns the number of affected rows by the latest INSERT/UPDATE/DELETE
     * executed in the database system
     *
     *```php
     * $connection->execute(
     *     "DELETE FROM robots"
     * );
     *
     * echo $connection->affectedRows(), " were deleted";
     *```
     */
    public function affectedRows(): int
    {
        return $this->affectedRows;
    }

    /**
     * Starts a transaction in the connection
     *
     * @param bool $nesting
     *
     * @return bool
     * @throws EventsException
     * @throws Exception
     */
    public function begin(bool $nesting = true): bool
    {
        /**
         * Increase the transaction nesting level
         */
        $this->transactionLevel++;

        /**
         * Check the transaction nesting level
         */
        if (1 === $this->transactionLevel) {
            /**
             * Notify the events manager about the started transaction
             */
            $this->fireManagerEvent("db:beginTransaction");

            return $this->pdo->beginTransaction();
        }

        /**
         * Check if the current database system supports nested transactions
         */
        if (
            0 === $this->transactionLevel ||
            false === $nesting ||
            false === $this->isNestedTransactionsWithSavepoints()
        ) {
            return false;
        }

        $savepointName = $this->getNestedTransactionSavepointName();

        /**
         * Notify the events manager about the created savepoint
         */
        $this->fireManagerEvent("db:createSavepoint", $savepointName);

        return $this->createSavepoint($savepointName);
    }

    /**
     * Closes the active connection returning success. Phalcon automatically
     * closes and destroys active connections when the request ends
     *
     * @return void
     */
    public function close(): void
    {
        $this->pdo = null;
    }

    /**
     * Commits the active transaction in the connection
     *
     * @param bool $nesting
     *
     * @return bool
     * @throws EventsException
     * @throws Exception
     */
    public function commit(bool $nesting = true): bool
    {
        /**
         * Check the transaction nesting level
         */
        if (0 === $this->transactionLevel) {
            throw new NoActiveTransaction();
        }

        if (1 === $this->transactionLevel) {
            /**
             * Notify the events manager about the committed transaction
             */
            $this->fireManagerEvent("db:commitTransaction");

            /**
             * Reduce the transaction nesting level
             */
            $this->transactionLevel--;

            $result = $this->pdo->commit();

            /**
             * When error mode is set to silent or warning, we need to check result and trigger event only when
             * $result is not false.
             */
            if ($result === false) {
                return false;
            }

            /**
             * Notify the events manager about the committed transaction
             */
            $this->fireManagerEvent('db:transactionCommitted');

            return true;
        }

        /**
         * Check if the current database system supports nested transactions
         */
        if (
            false === $nesting ||
            false === $this->isNestedTransactionsWithSavepoints()
        ) {
            /**
             * Reduce the transaction nesting level
             */
            if ($this->transactionLevel > 0) {
                $this->transactionLevel--;
            }

            return false;
        }

        /**
         * Notify the events manager about the committed savepoint
         */
        $savepointName = $this->getNestedTransactionSavepointName();

        $this->fireManagerEvent("db:releaseSavepoint", $savepointName);

        /**
         * Reduce the transaction nesting level
         */
        $this->transactionLevel--;
        $result = $this->releaseSavepoint($savepointName);

        /**
         * When error mode is set to silent or warning, we need to check result and trigger event only when
         * $result is not false.
         */
        if ($result === false) {
            return false;
        }

        /**
         * Notify the events manager about the released savepoint
         */
        $this->fireManagerEvent('db:savepointReleased', $savepointName);

        return true;
    }

    /**
     * This method is automatically called in \Phalcon\Db\Adapter\Pdo
     * constructor.
     *
     * Call it when you need to restore a database connection.
     *
     *```php
     * use Phalcon\Db\Adapter\Pdo\Mysql;
     *
     * // Make a connection
     * $connection = new Mysql(
     *     [
     *         "host"     => "localhost",
     *         "username" => "sigma",
     *         "password" => "secret",
     *         "dbname"   => "blog",
     *         "port"     => 3306,
     *     ]
     * );
     *
     * // Reconnect
     * $connection->connect();
     * ```
     */
    public function connect(array $descriptor = []): void
    {
        $dsnParts   = [];
        $descriptor = !empty($descriptor) ? $descriptor : $this->descriptor;

        // Check for a username or use null as default
        $userName = $descriptor["username"] ?? null;
        $password = $descriptor["password"] ?? null;
        $options  = [];

        /**
         * Check if the developer has defined custom options or create one from
         * scratch
         */
        if (isset($descriptor["options"]) && is_array($descriptor["options"])) {
            $options = $descriptor["options"];
        }

        if (isset($descriptor["persistent"])) {
            $options[PDO::ATTR_PERSISTENT] = (bool)$descriptor["persistent"];
        }

        if (isset($descriptor["autoReconnect"])) {
            $this->autoReconnect = (bool)$descriptor["autoReconnect"];
        }

        // Set PDO to throw exceptions when an error is encountered.
        $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

        // Check if the user has defined a custom dsn string. It should be in
        // the form of key=value with semicolons delineating sections.
        if (isset($descriptor["dsn"])) {
            $dsnParts[] = $descriptor["dsn"];
        }

        /**
         * Cleanup
         */
        unset(
            $descriptor["username"],
            $descriptor["password"],
            $descriptor["dialectClass"],
            $descriptor["options"],
            $descriptor["dsn"],
            $descriptor["autoReconnect"],
        );

        /**
         * Start with the dsn defaults and then write over it with the
         * descriptor. At this point the descriptor should be a valid DSN
         * key-value map due to all other values having been removed.
         */
        $dsnAttributesMap = array_merge($this->getDsnDefaults(), $descriptor);

        foreach ($dsnAttributesMap as $key => $value) {
            $dsnParts[] = $key . "=" . $value;
        }

        // Create the dsn attributes string.
        $dsnAttributes = implode(";", $dsnParts);

        // Create the connection using PDO
        $this->pdo = new PDO(
            $this->type . ":" . $dsnAttributes,
            $userName,
            $password,
            $options
        );
    }

    /**
     * Converts bound parameters such as :name: or ?1 into PDO bind params ?
     *
     *```php
     * print_r(
     *     $connection->convertBoundParams(
     *         "SELECT * FROM robots WHERE name = :name:",
     *         [
     *             "Bender",
     *         ]
     *     )
     * );
     *```
     *
     * @param string $sql
     * @param array  $parameters
     *
     * @return array<string, list|string|null>
     * @throws Exception
     */
    public function convertBoundParams(
        string $sql,
        array $parameters = []
    ): array {
        $placeHolders = [];
        $bindPattern  = "/\\?(\d+)|:(\w+):/";
        $matches      = null;
        $setOrder     = 2;

        $boundSql = $sql;
        if (
            preg_match_all($bindPattern, $sql, $matches, $setOrder)
        ) {
            foreach ($matches as $placeMatch) {
                if (isset($parameters[$placeMatch[1]])) {
                    $value = $parameters[$placeMatch[1]];
                } else {
                    if (!isset($placeMatch[2])) {
                        throw new MatchedParameterNotFound();
                    }

                    if (!isset($parameters[$placeMatch[2]])) {
                        throw new MatchedParameterNotFound();
                    }

                    $value = $parameters[$placeMatch[2]];
                }

                $placeHolders[] = $value;
            }

            $boundSql = preg_replace($bindPattern, "?", $sql);
        }

        return [
            "sql"    => $boundSql,
            "params" => $placeHolders,
        ];
    }

    /**
     * Escapes a value to avoid SQL injections according to the active charset
     * in the connection
     *
     *```php
     * $escapedStr = $connection->escapeString("some dangerous value");
     *```
     *
     * @param string $input
     *
     * @return string
     */
    public function escapeString(string $input): string
    {
        return $this->pdo->quote($input);
    }

    /**
     * Ensures the connection is alive, reconnecting in place if it is not.
     *
     * @return void
     */
    public function ensureConnection(): void
    {
        if (!$this->ping()) {
            $this->connect();
        }
    }

    /**
     * Sends SQL statements to the database server returning the success state.
     * Use this method only when the SQL statement sent to the server does not
     * return any rows
     *
     *```php
     * // Inserting data
     * $success = $connection->execute(
     *     "INSERT INTO robots VALUES (1, 'Astro Boy')"
     * );
     *
     * $success = $connection->execute(
     *     "INSERT INTO robots VALUES (?, ?)",
     *     [
     *         1,
     *         "Astro Boy",
     *     ]
     * );
     *```
     *
     * @param string $sqlStatement
     * @param array  $bindParams
     * @param array  $bindTypes
     *
     * @return bool
     * @throws Exception
     * @throws EventsException
     */
    public function execute(
        string $sqlStatement,
        array $bindParams = [],
        array $bindTypes = []
    ): bool {
        /**
         * Execute the beforeQuery event if an EventsManager is available
         */
        if (null !== $this->eventsManager) {
            $this->sqlStatement = $sqlStatement;
            $this->sqlVariables = $bindParams;
            $this->sqlBindTypes = $bindTypes;

            if (false === $this->fireManagerEvent("db:beforeQuery")) {
                return false;
            }
        }

        /**
         * Initialize affectedRows to 0
         */
        $affectedRows = 0;

        $this->prepareRealSql($sqlStatement, $bindParams);

        try {
            $affectedRows = $this->executeStatement(
                $sqlStatement,
                $bindParams,
                $bindTypes
            );
        } catch (PDOException $exception) {
            if (!$this->canReconnect($exception)) {
                throw $exception;
            }

            $this->handleConnectionLost();

            $affectedRows = $this->executeStatement(
                $sqlStatement,
                $bindParams,
                $bindTypes
            );
        }

        /**
         * Execute the afterQuery event if an EventsManager is available
         */
        if (is_int($affectedRows)) {
            $this->affectedRows = $affectedRows;

            $this->fireManagerEvent("db:afterQuery");
        }

        return true;
    }

    /**
     * Executes a prepared statement binding. This function uses integer indexes
     * starting from zero
     *
     *```php
     * use Phalcon\Db\Column;
     *
     * $statement = $db->prepare(
     *     "SELECT * FROM robots WHERE name = :name"
     * );
     *
     * $result = $connection->executePrepared(
     *     $statement,
     *     [
     *         "name" => "Voltron",
     *     ],
     *     [
     *         "name" => Column::BIND_PARAM_STR,
     *     ]
     * );
     *```
     */
    public function executePrepared(
        PDOStatement $statement,
        array $placeholders,
        array $dataTypes = []
    ): PDOStatement {
        $forceCasting = Settings::get('db.force_casting');
        foreach ($placeholders as $wildcard => $value) {
            if (is_int($wildcard)) {
                $parameter = $wildcard + 1;
            } elseif (is_string($wildcard)) {
                $parameter = $wildcard;
            } else {
                throw new InvalidBindParameter();
            }

            if (isset($dataTypes[$wildcard])) {
                $type = $dataTypes[$wildcard];

                /**
                 * The bind type needs to be string because the precision
                 * is lost if it is cast as a double
                 */
                if ($type === Column::BIND_PARAM_DECIMAL) {
                    $castValue = (string)$value;
                    $type      = Column::BIND_SKIP;
                } else {
                    $castValue = $value;
                    if (true === $forceCasting && !is_array($value)) {
                        $castValue = match ($type) {
                            Column::BIND_PARAM_INT  => intval($value),
                            Column::BIND_PARAM_STR  => (string)$value,
                            Column::BIND_PARAM_NULL => null,
                            Column::BIND_PARAM_BOOL => (bool)$value,
                            default                 => $value,
                        };
                    }
                }

                /**
                 * 1024 : ignore the bind type
                 */
                if (!is_array($castValue)) {
                    if ($type === Column::BIND_SKIP) {
                        $statement->bindValue($parameter, $castValue);
                    } else {
                        $statement->bindValue($parameter, $castValue, $type);
                    }
                } else {
                    foreach ($castValue as $position => $itemValue) {
                        if ($type == Column::BIND_SKIP) {
                            $statement->bindValue(
                                $parameter . $position,
                                $itemValue
                            );
                        } else {
                            $statement->bindValue(
                                $parameter . $position,
                                $itemValue,
                                $type
                            );
                        }
                    }
                }
            } elseif (!is_array($value)) {
                $statement->bindValue($parameter, $value);
            } else {
                foreach ($value as $position => $itemValue) {
                    $statement->bindValue($parameter . $position, $itemValue);
                }
            }
        }

        $statement->execute();

        return $statement;
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
     * Return the error info, if any
     *
     * @return array
     */
    public function getErrorInfo(): array
    {
        return $this->pdo->errorInfo();
    }

    /**
     * Return internal PDO handler
     *
     * @return PDO|null
     */
    public function getInternalHandler(): PDO | null
    {
        return $this->pdo;
    }

    /**
     * Returns the current transaction nesting level
     *
     * @return int
     */
    public function getTransactionLevel(): int
    {
        return $this->transactionLevel;
    }

    /**
     * Checks whether the connection is under a transaction
     *
     *```php
     * $connection->begin();
     *
     * // true
     * var_dump(
     *     $connection->isUnderTransaction()
     * );
     *```
     *
     * @return bool
     */
    public function isUnderTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    /**
     * Returns the insert id for the auto_increment/serial column inserted in
     * the latest executed SQL statement
     *
     *```php
     * // Inserting a new robot
     * $success = $connection->insert(
     *     "robots",
     *     [
     *         "Astro Boy",
     *         1952,
     *     ],
     *     [
     *         "name",
     *         "year",
     *     ]
     * );
     *
     * // Getting the generated id
     * $id = $connection->lastInsertId();
     *```
     *
     * @param string|null $name
     *
     * @return string|bool
     */
    public function lastInsertId(string | null $name = null): string | bool
    {
        return $this->pdo->lastInsertId($name);
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
     * Returns a PDO prepared statement to be executed with 'executePrepared'
     *
     *```php
     * use Phalcon\Db\Column;
     *
     * $statement = $db->prepare(
     *     "SELECT * FROM robots WHERE name = :name"
     * );
     *
     * $result = $connection->executePrepared(
     *     $statement,
     *     [
     *         "name" => "Voltron",
     *     ],
     *     [
     *         "name" => Column::BIND_PARAM_INT,
     *     ]
     * );
     *```
     *
     * @param string $sqlStatement
     *
     * @return PDOStatement
     */
    public function prepare(string $sqlStatement): PDOStatement
    {
        return $this->pdo->prepare($sqlStatement);
    }

    /**
     * Sends SQL statements to the database server returning the success state.
     * Use this method only when the SQL statement sent to the server is
     * returning rows
     *
     *```php
     * // Querying data
     * $resultset = $connection->query(
     *     "SELECT * FROM robots WHERE type = 'mechanical'"
     * );
     *
     * $resultset = $connection->query(
     *     "SELECT * FROM robots WHERE type = ?",
     *     [
     *         "mechanical",
     *     ]
     * );
     *```
     *
     * @param string $sqlStatement
     * @param array  $bindParams
     * @param array  $bindTypes
     *
     * @return ResultInterface|bool
     * @throws EventsException
     * @throws Exception
     */
    public function query(
        string $sqlStatement,
        array $bindParams = [],
        array $bindTypes = []
    ): ResultInterface | bool {
        /**
         * Execute the beforeQuery event if an EventsManager is available
         */
        if (null !== $this->eventsManager) {
            $this->sqlStatement = $sqlStatement;
            $this->sqlVariables = $bindParams;
            $this->sqlBindTypes = $bindTypes;

            if (false === $this->fireManagerEvent("db:beforeQuery")) {
                return false;
            }
        }

        $params = (!empty($bindParams)) ? $bindParams : [];
        $types  = (!empty($bindTypes)) ? $bindTypes : [];

        $this->prepareRealSql($sqlStatement, $bindParams);

        try {
            $statement = $this->queryStatement($sqlStatement, $params, $types);
        } catch (PDOException $exception) {
            if (!$this->canReconnect($exception)) {
                throw $exception;
            }

            $this->handleConnectionLost();

            $statement = $this->queryStatement($sqlStatement, $params, $types);
        }

        /**
         * Execute the afterQuery event if an EventsManager is available
         *
         * @todo check this path. Why would $statement not be an object
         */
        if (is_object($statement)) {
            $this->fireManagerEvent("db:afterQuery");

            return new PdoResult(
                $this,
                $statement,
                $sqlStatement,
                $bindParams,
                $bindTypes
            );
        }

        return $statement;
    }

    /**
     * Rollbacks the active transaction in the connection
     *
     * @param bool $nesting
     *
     * @return bool
     * @throws EventsException
     * @throws Exception
     */
    public function rollback(bool $nesting = true): bool
    {
        /**
         * Check the transaction nesting level
         */
        if (0 === $this->transactionLevel) {
            throw new NoActiveTransaction();
        }

        if (1 === $this->transactionLevel) {
            /**
             * Notify the events manager about the rollbacked transaction
             */
            $this->fireManagerEvent("db:rollbackTransaction");

            /**
             * Reduce the transaction nesting level
             */
            $this->transactionLevel--;

            $result = $this->pdo->rollback();

            $this->fireManagerEvent('db:transactionRolledBack');

            return $result;
        }

        /**
         * Check if the current database system supports nested transactions
         */
        if (
            false === $nesting ||
            false === $this->isNestedTransactionsWithSavepoints()
        ) {
            /**
             * Reduce the transaction nesting level
             */
            if ($this->transactionLevel > 0) {
                $this->transactionLevel--;
            }

            return false;
        }

        $savepointName = $this->getNestedTransactionSavepointName();

        /**
         * Notify the events manager about the rolled back savepoint
         */
        $this->fireManagerEvent("db:rollbackSavepoint", $savepointName);

        /**
         * Reduce the transaction nesting level
         */
        $this->transactionLevel--;

        return $this->rollbackSavepoint($savepointName);
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
     * Returns PDO adapter DSN defaults as a key-value map.
     */
    abstract protected function getDsnDefaults(): array;

    /**
     * Recognizes whether an exception represents a lost ("gone away")
     * connection. The base adapter cannot know driver specifics, so it
     * returns false; concrete adapters override this.
     *
     * @param Throwable $exception
     *
     * @return bool
     */
    protected function isConnectionError(Throwable $exception): bool
    {
        return false;
    }

    /**
     * Constructs the SQL statement (with parameters)
     *
     * @see https://stackoverflow.com/a/8403150
     */
    protected function prepareRealSql(string $statement, array $parameters): void
    {
        $result = $statement;
        $values = $parameters;

        if (!empty($parameters)) {
            $keys = [];
            foreach ($parameters as $key => $value) {
                if (is_string($key)) {
                    $keys[] = "/:" . $key . "/";
                } else {
                    $keys[] = "/[?]/";
                }

                if (is_string($value)) {
                    $values[$key] = "'" . $value . "'";
                } elseif (is_array($value)) {
                    $values[$key] = "'" . implode("','", $value) . "'";
                } elseif (null === $value) {
                    $values[$key] = "NULL";
                }
            }

            $result = preg_replace($keys, $values, $statement, 1);
        }

        $this->realSqlStatement = $result;
    }

    /**
     * Whether a failed query may be transparently retried after reconnecting.
     * Only when auto-reconnect is on, we are not in a transaction, and the
     * failure is a recognized connection loss.
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

        if (0 !== $this->transactionLevel) {
            return false;
        }

        return $this->isConnectionError($exception);
    }

    /**
     * Runs the actual write against PDO and returns the affected-rows count
     * (or the raw exec() return for unprepared statements).
     *
     * @param string $sqlStatement
     * @param array  $bindParams
     * @param array  $bindTypes
     *
     * @return int|false
     */
    private function executeStatement(
        string $sqlStatement,
        array $bindParams,
        array $bindTypes
    ): int | false {
        $affectedRows = 0;

        if (!empty($bindParams)) {
            $statement = $this->pdo->prepare($sqlStatement);

            if (false !== $statement) {
                $newStatement = $this->executePrepared(
                    $statement,
                    $bindParams,
                    $bindTypes
                );

                $affectedRows = $newStatement->rowCount();
            }
        } else {
            $affectedRows = $this->pdo->exec($sqlStatement);
        }

        return $affectedRows;
    }

    /**
     * Notifies listeners that the connection was lost and re-establishes it.
     *
     * @return void
     */
    private function handleConnectionLost(): void
    {
        $this->fireManagerEvent("db:connectionLost");

        $this->connect();
    }

    /**
     * Prepares and executes a read statement, returning the live PDOStatement.
     *
     * @param string $sqlStatement
     * @param array  $params
     * @param array  $types
     *
     * @return PDOStatement
     * @throws CannotPrepareStatement
     */
    private function queryStatement(
        string $sqlStatement,
        array $params,
        array $types
    ): PDOStatement {
        $statement = $this->pdo->prepare($sqlStatement);
        if (false === $statement) {
            throw new CannotPrepareStatement();
        }

        return $this->executePrepared($statement, $params, $types);
    }
}
