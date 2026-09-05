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

namespace Phalcon\Contracts\Db\Adapter;

use Phalcon\Db\ColumnInterface;
use Phalcon\Db\DialectInterface;
use Phalcon\Db\IndexInterface;
use Phalcon\Db\RawValue;
use Phalcon\Db\ReferenceInterface;
use Phalcon\Db\ResultInterface;

/**
 * Canonical contract for Phalcon\Db adapters.
 *
 * @todo v7 - these will become required interface members. They are
 *            omitted from the v5 line to avoid breaking third-party
 *            implementors:
 *              - addCheck()                : bool
 *              - createMaterializedView()  : bool
 *              - dropCheck()               : bool
 *              - dropMaterializedView()    : bool
 *              - onConflictUpdate()        : string
 *              - refreshMaterializedView() : bool
 *              - returning()               : string
 */
interface Adapter
{
    /**
     * Adds a column to a table
     */
    public function addColumn(
        string $tableName,
        string $schemaName,
        ColumnInterface $column
    ): bool;

    /**
     * Adds a foreign key to a table
     */
    public function addForeignKey(
        string $tableName,
        string $schemaName,
        ReferenceInterface $reference
    ): bool;

    /**
     * Adds an index to a table
     */
    public function addIndex(
        string $tableName,
        string $schemaName,
        IndexInterface $index
    ): bool;

    /**
     * Adds a primary key to a table
     */
    public function addPrimaryKey(
        string $tableName,
        string $schemaName,
        IndexInterface $index
    ): bool;

    /**
     * Returns the number of affected rows by the last INSERT/UPDATE/DELETE
     * reported by the database system
     */
    public function affectedRows(): int;

    /**
     * Starts a transaction in the connection
     */
    public function begin(bool $nesting = true): bool;

    /**
     * Closes active connection returning success. Phalcon automatically closes
     * and destroys active connections within Phalcon\Db\Pool
     */
    public function close(): void;

    /**
     * Commits the active transaction in the connection
     */
    public function commit(bool $nesting = true): bool;

    /**
     * This method is automatically called in \Phalcon\Db\Adapter\Pdo
     * constructor. Call it when you need to restore a database connection
     */
    public function connect(array $descriptor = []): void;

    /**
     * Creates a new savepoint
     */
    public function createSavepoint(string $name): bool;

    /**
     * Creates a table
     */
    public function createTable(
        string $tableName,
        string $schemaName,
        array $definition
    ): bool;

    /**
     * Creates a view
     */
    public function createView(
        string $viewName,
        array $definition,
        string | null $schemaName = null
    ): bool;

    /**
     * Deletes data from a table using custom RDBMS SQL syntax
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
     */
    public function dropColumn(
        string $tableName,
        string $schemaName,
        string $columnName
    ): bool;

    /**
     * Drops a foreign key from a table
     */
    public function dropForeignKey(
        string $tableName,
        string $schemaName,
        string $referenceName
    ): bool;

    /**
     * Drop an index from a table
     */
    public function dropIndex(
        string $tableName,
        string $schemaName,
        string $indexName
    ): bool;

    /**
     * Drops primary key from a table
     */
    public function dropPrimaryKey(string $tableName, string $schemaName): bool;

    /**
     * Drops a table from a schema/database
     */
    public function dropTable(
        string $tableName,
        string | null $schemaName = null,
        bool $ifExists = true
    ): bool;

    /**
     * Drops a view
     *
     * @param string $schemaName
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
     */
    public function escapeIdentifier(array | float | int | string $identifier): string;

    /**
     * Escapes a value to avoid SQL injections
     */
    public function escapeString(string $input): string;

    /**
     * Sends SQL statements to the database server returning the success state.
     * Use this method only when the SQL statement sent to the server does not
     * return any rows
     */
    public function execute(
        string $sqlStatement,
        array $bindParams = [],
        array $bindTypes = []
    ): bool;

    /**
     * Dumps the complete result of a query into an array
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
     * // Getting count of invoices
     * $invoicesCount = $connection->fetchColumn("SELECT COUNT(*) FROM co_invoices");
     * print_r($invoicesCount);
     *
     * // Getting the title of the last created invoice
     * $invoice = $connection->fetchColumn(
     *     "SELECT inv_id, inv_title FROM co_invoices ORDER BY inv_created_at DESC",
     *     1
     * );
     * print_r($invoice);
     *```
     *
     * @return bool|string
     */
    public function fetchColumn(
        string $sqlQuery,
        array $placeholders = [],
        int | string $column = 0
    ): mixed;

    /**
     * Returns the first row in a SQL query result
     */
    public function fetchOne(
        string $sqlQuery,
        int $fetchMode = 2,
        array $bindParams = [],
        array $bindTypes = []
    ): array | bool;

    /**
     * Returns a SQL modified with a FOR UPDATE clause
     */
    public function forUpdate(string $sqlQuery, string $modifier = ''): string;

    /**
     * Returns the SQL column definition from a column
     */
    public function getColumnDefinition(ColumnInterface $column): string;

    /**
     * Gets a list of columns
     */
    public function getColumnList(array $columnList): string;

    /**
     * Gets the active connection unique identifier
     */
    public function getConnectionId(): int;

    /**
     * Return the default identity value to insert in an identity column
     */
    public function getDefaultIdValue(): RawValue;

    /**
     * Returns the default value to make the RBDM use the default value declared
     * in the table definition
     *
     *```php
     * // Inserting a new invoice with a valid default value for the column 'inv_total'
     * $success = $connection->insert(
     *     "co_invoices",
     *     [
     *         "Test Invoice",
     *         $connection->getDefaultValue()
     *     ],
     *     [
     *         "inv_title",
     *         "inv_total",
     *     ]
     * );
     *```
     *
     * @todo Return NULL if this is not supported by the adapter
     */
    public function getDefaultValue(): RawValue | null;

    /**
     * Return descriptor used to connect to the active database
     */
    public function getDescriptor(): array;

    /**
     * Returns internal dialect instance
     */
    public function getDialect(): DialectInterface;

    /**
     * Returns the name of the dialect used
     */
    public function getDialectType(): string;

    /**
     * Return internal PDO handler
     */
    public function getInternalHandler(): mixed;

    /**
     * Returns the savepoint name to use for nested transactions
     */
    public function getNestedTransactionSavepointName(): string;

    /**
     * Active SQL statement in the object without replace bound parameters
     */
    public function getRealSQLStatement(): string;

    /**
     * Active SQL statement in the object
     */
    public function getSQLBindTypes(): array;

    /**
     * Active SQL statement in the object
     */
    public function getSQLStatement(): string;

    /**
     * Active SQL statement in the object
     */
    public function getSQLVariables(): array;

    /**
     * Returns type of database system the adapter is used for
     */
    public function getType(): string;

    /**
     * Inserts data into a table using custom RDBMS SQL syntax
     *
     * @param array $fields
     */
    public function insert(
        string $tableName,
        array $values,
        array | null $fields = null,
        array $dataTypes = []
    ): bool;

    /**
     * Inserts data into a table using custom RBDM SQL syntax
     *
     * ```php
     * // Inserting a new invoice
     * $success = $connection->insertAsDict(
     *     "co_invoices",
     *     [
     *         "inv_title" => "Test Invoice",
     *         "inv_total" => 100,
     *     ]
     * );
     *
     * // Next SQL sentence is sent to the database system
     * INSERT INTO `co_invoices` (`inv_title`, `inv_total`) VALUES ("Test Invoice", 100);
     * ```
     */
    public function insertAsDict(
        string $tableName,
        array $data,
        array $dataTypes = []
    ): bool;

    /**
     * Returns if nested transactions should use savepoints
     */
    public function isNestedTransactionsWithSavepoints(): bool;

    /**
     * Checks whether connection is under database transaction
     */
    public function isUnderTransaction(): bool;

    /**
     * Returns insert id for the auto_increment column inserted in the last SQL
     * statement
     *
     * @param string|null $name Name of the sequence object from which the ID
     *                          should be returned.
     */
    public function lastInsertId(string | null $name = null): bool | string;

    /**
     * Appends a LIMIT clause to sqlQuery argument
     */
    public function limit(string $sqlQuery, array | int $number): string;

    /**
     * List all tables on a database
     *
     * @param string $schemaName
     */
    public function listTables(string | null $schemaName = null): array;

    /**
     * List all views on a database
     *
     * @param string $schemaName
     */
    public function listViews(string | null $schemaName = null): array;

    /**
     * Modifies a table column based on a definition
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
     */
    public function query(
        string $sqlStatement,
        array $bindParams = [],
        array $bindTypes = []
    ): bool | ResultInterface;

    /**
     * Releases given savepoint
     */
    public function releaseSavepoint(string $name): bool;

    /**
     * Rollbacks the active transaction in the connection
     */
    public function rollback(bool $nesting = true): bool;

    /**
     * Rollbacks given savepoint
     */
    public function rollbackSavepoint(string $name): bool;

    /**
     * Set if nested transactions should use savepoints
     *
     * @return AdapterInterface
     */
    public function setNestedTransactionsWithSavepoints(
        bool $flag
    ): \Phalcon\Db\Adapter\AdapterInterface;

    /**
     * Returns a SQL modified with a LOCK IN SHARE MODE clause
     */
    public function sharedLock(string $sqlQuery, string $modifier = ''): string;

    /**
     * SQLite does not support the DEFAULT keyword
     *
     * @deprecated Will re removed in the next version
     */
    public function supportsDefaultValue(): bool;

    /**
     * Check whether the database system requires a sequence to produce
     * auto-numeric values
     */
    public function supportSequences(): bool;

    /**
     * Generates SQL checking for the existence of a schema.table
     *
     * @param string $schemaName
     */
    public function tableExists(
        string $tableName,
        string | null $schemaName = null
    ): bool;

    /**
     * Gets creation options from a table
     *
     * @param string $schemaName
     */
    public function tableOptions(
        string $tableName,
        string | null $schemaName = null
    ): array;

    /**
     * Updates data on a table using custom RDBMS SQL syntax
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
     * // Updating existing invoice
     * $success = $connection->updateAsDict(
     *     "co_invoices",
     *     [
     *         "inv_title" => "New Test Invoice",
     *     ],
     *     "inv_id = 101"
     * );
     *
     * // Next SQL sentence is sent to the database system
     * UPDATE `co_invoices` SET `inv_title` = "New Test Invoice" WHERE inv_id = 101
     * ```
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
     */
    public function useExplicitIdValue(): bool;

    /**
     * Generates SQL checking for the existence of a schema.view
     *
     * @param string $schemaName
     */
    public function viewExists(
        string $viewName,
        string | null $schemaName = null
    ): bool;
}
