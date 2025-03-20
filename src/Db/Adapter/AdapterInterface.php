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

namespace Phalcon\Db\Adapter;

use Phalcon\Db\ColumnInterface;
use Phalcon\Db\DialectInterface;
use Phalcon\Db\IndexInterface;
use Phalcon\Db\RawValue;
use Phalcon\Db\ReferenceInterface;
use Phalcon\Db\ResultInterface;

/**
 * Interface for Phalcon\Db adapters
 */
interface AdapterInterface
{
    /**
     * Adds a column to a table
     *
     * @param string          $tableName
     * @param string          $schemaName
     * @param ColumnInterface $column
     *
     * @return bool
     */
    public function addColumn(
        string $tableName,
        string $schemaName,
        ColumnInterface $column
    ): bool;

    /**
     * Adds a foreign key to a table
     *
     * @param string             $tableName
     * @param string             $schemaName
     * @param ReferenceInterface $reference
     *
     * @return bool
     */
    public function addForeignKey(
        string $tableName,
        string $schemaName,
        ReferenceInterface $reference
    ): bool;

    /**
     * Adds an index to a table
     *
     * @param string         $tableName
     * @param string         $schemaName
     * @param IndexInterface $index
     *
     * @return bool
     */
    public function addIndex(
        string $tableName,
        string $schemaName,
        IndexInterface $index
    ): bool;

    /**
     * Adds a primary key to a table
     *
     * @param string         $tableName
     * @param string         $schemaName
     * @param IndexInterface $index
     *
     * @return bool
     */
    public function addPrimaryKey(
        string $tableName,
        string $schemaName,
        IndexInterface $index
    ): bool;

    /**
     * Returns the number of affected rows by the last INSERT/UPDATE/DELETE
     * reported by the database system
     *
     * @return int
     */
    public function affectedRows(): int;

    /**
     * Starts a transaction in the connection
     *
     * @param bool $nesting
     *
     * @return bool
     */
    public function begin(bool $nesting = true): bool;

    /**
     * Closes active connection returning success. Phalcon automatically closes
     * and destroys active connections within Phalcon\Db\Pool
     *
     * @return void
     */
    public function close(): void;

    /**
     * Commits the active transaction in the connection
     *
     * @param bool $nesting
     *
     * @return bool
     */
    public function commit(bool $nesting = true): bool;

    /**
     * This method is automatically called in \Phalcon\Db\Adapter\Pdo
     * constructor. Call it when you need to restore a database connection
     *
     * @param array $descriptor
     *
     * @return void
     */
    public function connect(array $descriptor = []): void;

    /**
     * Creates a new savepoint
     *
     * @param string $name
     *
     * @return bool
     */
    public function createSavepoint(string $name): bool;

    /**
     * Creates a table
     *
     * @param string $tableName
     * @param string $schemaName
     * @param array  $definition
     *
     * @return bool
     */
    public function createTable(
        string $tableName,
        string $schemaName,
        array $definition
    ): bool;

    /**
     * Creates a view
     *
     * @param string      $viewName
     * @param array       $definition
     * @param string|null $schemaName
     *
     * @return bool
     */
    public function createView(
        string $viewName,
        array $definition,
        string | null $schemaName = null
    ): bool;

    /**
     * Deletes data from a table using custom RDBMS SQL syntax
     *
     * @param array|string $tableName
     * @param string|null  $whereCondition
     * @param array        $placeholders
     * @param array        $dataTypes
     *
     * @return bool
     */
    public function delete(
        array | string $tableName,
        string | null $whereCondition = null,
        array $placeholders = [],
        array $dataTypes = []
    ): bool;

    /**
     * Returns an array of Phalcon\Db\Column objects describing a table
     *
     * @param string $tableName
     * @param string $schemaName
     *
     * @return ColumnInterface[]
     */
    public function describeColumns(
        string $tableName,
        string | null $schemaName = null
    ): array;

    /**
     * Lists table indexes
     *
     * @param string $tableName
     * @param string $schemaName
     *
     * @return IndexInterface[]
     */
    public function describeIndexes(
        string $tableName,
        string | null $schemaName = null
    ): array;

    /**
     * Lists table references
     *
     * @param string $tableName
     * @param string $schemaName
     *
     * @return ReferenceInterface[]
     */
    public function describeReferences(
        string $tableName,
        string | null $schemaName = null
    ): array;

    /**
     * Drops a column from a table
     *
     * @param string $tableName
     * @param string $schemaName
     * @param string $columnName
     *
     * @return bool
     */
    public function dropColumn(
        string $tableName,
        string $schemaName,
        string $columnName
    ): bool;

    /**
     * Drops a foreign key from a table
     *
     * @param string $tableName
     * @param string $schemaName
     * @param string $referenceName
     *
     * @return bool
     */
    public function dropForeignKey(
        string $tableName,
        string $schemaName,
        string $referenceName
    ): bool;

    /**
     * Drop an index from a table
     *
     * @param string $tableName
     * @param string $schemaName
     * @param string $indexName
     *
     * @return bool
     */
    public function dropIndex(
        string $tableName,
        string $schemaName,
        string $indexName
    ): bool;

    /**
     * Drops primary key from a table
     *
     * @param string $tableName
     * @param string $schemaName
     *
     * @return bool
     */
    public function dropPrimaryKey(string $tableName, string $schemaName): bool;

    /**
     * Drops a table from a schema/database
     *
     * @param string      $tableName
     * @param string|null $schemaName
     * @param bool        $ifExists
     *
     * @return bool
     */
    public function dropTable(
        string $tableName,
        string | null $schemaName = null,
        bool $ifExists = true
    ): bool;

    /**
     * Drops a view
     *
     * @param string $viewName
     * @param string $schemaName
     * @param bool   $ifExists
     *
     * @return bool
     */
    public function dropView(
        string $viewName,
        string | null $schemaName = null,
        bool $ifExists = true
    ): bool;

    /**
     * Escapes a column/table/schema name
     *
     * @param array|string $identifier
     *
     * @return string
     */
    public function escapeIdentifier(array | string $identifier): string;

    /**
     * Escapes a value to avoid SQL injections
     *
     * @param string $input
     *
     * @return string
     */
    public function escapeString(string $input): string;

    /**
     * Sends SQL statements to the database server returning the success state.
     * Use this method only when the SQL statement sent to the server doesn't
     * return any rows
     *
     * @param string $sqlStatement
     * @param array  $bindParams
     * @param array  $bindTypes
     *
     * @return bool
     */
    public function execute(
        string $sqlStatement,
        array $bindParams = [],
        array $bindTypes = []
    ): bool;

    /**
     * Dumps the complete result of a query into an array
     *
     * @param string $sqlQuery
     * @param int    $fetchMode
     * @param array  $bindParams
     * @param array  $bindTypes
     *
     * @return array
     */
    public function fetchAll(
        string $sqlQuery,
        int $fetchMode = 2,
        array $bindParams = [],
        array $bindTypes = []
    ): array;

    /**
     * Returns the n'th field of first row in a SQL query result
     *
     *```php
     * // Getting count of robots
     * $robotsCount = $connection->fetchColumn("SELECT COUNT(*) FROM robots");
     * print_r($robotsCount);
     *
     * // Getting name of last edited robot
     * $robot = $connection->fetchColumn(
     *     "SELECT id, name FROM robots ORDER BY modified DESC",
     *     1
     * );
     * print_r($robot);
     *```
     *
     * @param string     $sqlQuery
     * @param array      $placeholders
     * @param int|string $column
     *
     * @return string|bool
     */
    public function fetchColumn(
        string $sqlQuery,
        array $placeholders = [],
        int | string $column = 0
    ): string | bool;

    /**
     * Returns the first row in a SQL query result
     *
     * @param string $sqlQuery
     * @param int    $fetchMode
     * @param array  $bindParams
     * @param array  $bindTypes
     *
     * @return array
     */
    public function fetchOne(
        string $sqlQuery,
        int $fetchMode = 2,
        array $bindParams = [],
        array $bindTypes = []
    ): array;

    /**
     * Returns a SQL modified with a FOR UPDATE clause
     *
     * @param string $sqlQuery
     *
     * @return string
     */
    public function forUpdate(string $sqlQuery): string;

    /**
     * Returns the SQL column definition from a column
     *
     * @param ColumnInterface $column
     *
     * @return string
     */
    public function getColumnDefinition(ColumnInterface $column): string;

    /**
     * Gets a list of columns
     *
     * @param array $columnList
     *
     * @return string
     */
    public function getColumnList(array $columnList): string;

    /**
     * Gets the active connection unique identifier
     *
     * @return string
     */
    public function getConnectionId(): string;

    /**
     * Return the default identity value to insert in an identity column
     *
     * @return RawValue
     */
    public function getDefaultIdValue(): RawValue;

    /**
     * Returns the default value to make the RBDM use the default value declared
     * in the table definition
     *
     *```php
     * // Inserting a new robot with a valid default value for the column 'year'
     * $success = $connection->insert(
     *     "robots",
     *     [
     *         "Astro Boy",
     *         $connection->getDefaultValue()
     *     ],
     *     [
     *         "name",
     *         "year",
     *     ]
     * );
     *```
     *
     * @return RawValue|null
     * @todo Return NULL if this is not supported by the adapter
     *
     */
    public function getDefaultValue(): RawValue | null;

    /**
     * Return descriptor used to connect to the active database
     *
     * @return array
     */
    public function getDescriptor(): array;

    /**
     * Returns internal dialect instance
     *
     * @return DialectInterface
     */
    public function getDialect(): DialectInterface;

    /**
     * Returns the name of the dialect used
     *
     * @return string
     */
    public function getDialectType(): string;

    /**
     * Return internal PDO handler
     *
     * @return mixed
     */
    public function getInternalHandler(): mixed;

    /**
     * Returns the savepoint name to use for nested transactions
     *
     * @return string
     */
    public function getNestedTransactionSavepointName(): string;

    /**
     * Active SQL statement in the object without replace bound parameters
     *
     * @return string
     */
    public function getRealSQLStatement(): string;

    /**
     * Active SQL statement in the object
     *
     * @return array
     */
    public function getSQLBindTypes(): array;

    /**
     * Active SQL statement in the object
     *
     * @return string
     */
    public function getSQLStatement(): string;

    /**
     * Active SQL statement in the object
     *
     * @return array
     */
    public function getSQLVariables(): array;

    /**
     * Returns type of database system the adapter is used for
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Inserts data into a table using custom RDBMS SQL syntax
     *
     * @param string $tableName
     * @param array  $values
     * @param array  $fields
     * @param array  $dataTypes
     *
     * @return bool
     */
    public function insert(
        string $tableName,
        array $values,
        array $fields = [],
        array $dataTypes = []
    ): bool;

    /**
     * Inserts data into a table using custom RBDM SQL syntax
     *
     * ```php
     * // Inserting a new robot
     * $success = $connection->insertAsDict(
     *     "robots",
     *     [
     *         "name" => "Astro Boy",
     *         "year" => 1952,
     *     ]
     * );
     *
     * // Next SQL sentence is sent to the database system
     * INSERT INTO `robots` (`name`, `year`) VALUES ("Astro boy", 1952);
     * ```
     *
     * @param string $tableName
     * @param array  $data
     * @param array  $dataTypes
     *
     * @return bool
     */
    public function insertAsDict(
        string $tableName,
        array $data,
        array $dataTypes = []
    ): bool;

    /**
     * Returns if nested transactions should use savepoints
     *
     * @return bool
     */
    public function isNestedTransactionsWithSavepoints(): bool;

    /**
     * Checks whether connection is under database transaction
     *
     * @return bool
     */
    public function isUnderTransaction(): bool;

    /**
     * Returns insert id for the auto_increment column inserted in the last SQL
     * statement
     *
     * @param string|null $name Name of the sequence object from which the ID
     *                          should be returned.
     *
     * @return string|bool
     */
    public function lastInsertId(string | null $name = null): string | bool;

    /**
     * Appends a LIMIT clause to sqlQuery argument
     *
     * @param string    $sqlQuery
     * @param array|int $number
     *
     * @return string
     */
    public function limit(string $sqlQuery, array | int $number): string;

    /**
     * List all tables on a database
     *
     * @param string $schemaName
     *
     * @return array
     */
    public function listTables(string | null $schemaName = null): array;

    /**
     * List all views on a database
     *
     * @param string $schemaName
     *
     * @return array
     */
    public function listViews(string | null $schemaName = null): array;

    /**
     * Modifies a table column based on a definition
     *
     * @param string               $tableName
     * @param string               $schemaName
     * @param ColumnInterface      $column
     * @param ColumnInterface|null $currentColumn
     *
     * @return bool
     */
    public function modifyColumn(
        string $tableName,
        string $schemaName,
        ColumnInterface $column,
        ColumnInterface | null $currentColumn = null
    ): bool;

    /**
     * Sends SQL statements to the database server returning the success state.
     * Use this method only when the SQL statement sent to the server returns
     * rows
     *
     * @param string $sqlStatement
     * @param array  $bindParams
     * @param array  $bindTypes
     *
     * @return ResultInterface|bool
     */
    public function query(
        string $sqlStatement,
        array $bindParams = [],
        array $bindTypes = []
    ): ResultInterface | bool;

    /**
     * Releases given savepoint
     *
     * @param string $name
     *
     * @return bool
     */
    public function releaseSavepoint(string $name): bool;

    /**
     * Rollbacks the active transaction in the connection
     *
     * @param bool $nesting
     *
     * @return bool
     */
    public function rollback(bool $nesting = true): bool;

    /**
     * Rollbacks given savepoint
     *
     * @param string $name
     *
     * @return bool
     */
    public function rollbackSavepoint(string $name): bool;

    /**
     * Set if nested transactions should use savepoints
     *
     * @param bool $flag
     *
     * @return AdapterInterface
     */
    public function setNestedTransactionsWithSavepoints(
        bool $flag
    ): AdapterInterface;

    /**
     * Returns a SQL modified with a LOCK IN SHARE MODE clause
     *
     * @param string $sqlQuery
     *
     * @return string
     */
    public function sharedLock(string $sqlQuery): string;

    /**
     * Check whether the database system requires a sequence to produce
     * auto-numeric values
     *
     * @return bool
     */
    public function supportSequences(): bool;

    /**
     * SQLite does not support the DEFAULT keyword
     *
     * @return bool
     * @deprecated Will re removed in the next version
     *
     */
    public function supportsDefaultValue(): bool;

    /**
     * Generates SQL checking for the existence of a schema.table
     *
     * @param string $tableName
     * @param string $schemaName
     *
     * @return bool
     */
    public function tableExists(
        string $tableName,
        string | null $schemaName = null
    ): bool;

    /**
     * Gets creation options from a table
     *
     * @param string $tableName
     * @param string $schemaName
     *
     * @return array
     */
    public function tableOptions(
        string $tableName,
        string | null $schemaName = null
    ): array;

    /**
     * Updates data on a table using custom RDBMS SQL syntax
     *
     * @param string       $tableName
     * @param array        $fields
     * @param array        $values
     * @param array|string $whereCondition
     * @param array        $dataTypes
     *
     * @return bool
     */
    public function update(
        string $tableName,
        array $fields,
        array $values,
        array | string $whereCondition = [],
        array $dataTypes = []
    ): bool;

    /**
     * Updates data on a table using custom RBDM SQL syntax
     * Another, more convenient syntax
     *
     * ```php
     * // Updating existing robot
     * $success = $connection->updateAsDict(
     *     "robots",
     *     [
     *         "name" => "New Astro Boy",
     *     ],
     *     "id = 101"
     * );
     *
     * // Next SQL sentence is sent to the database system
     * UPDATE `robots` SET `name` = "Astro boy" WHERE id = 101
     * ```
     *
     * @param string       $tableName
     * @param array        $data
     * @param array|string $whereCondition
     * @param array        $dataTypes
     *
     * @return bool
     */
    public function updateAsDict(
        string $tableName,
        array $data,
        array | string $whereCondition = [],
        array $dataTypes = []
    ): bool;

    /**
     * Check whether the database system requires an explicit value for identity
     * columns
     *
     * @return bool
     */
    public function useExplicitIdValue(): bool;

    /**
     * Generates SQL checking for the existence of a schema.view
     *
     * @param string $viewName
     * @param string $schemaName
     *
     * @return bool
     */
    public function viewExists(
        string $viewName,
        string | null $schemaName = null
    ): bool;
}
