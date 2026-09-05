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

namespace Phalcon\Contracts\Db;

use Phalcon\Db\ColumnInterface;
use Phalcon\Db\Dialect as DbDialect;
use Phalcon\Db\IndexInterface;
use Phalcon\Db\ReferenceInterface;

/**
 * Canonical contract for Phalcon\Db dialects.
 *
 * @todo v7 - these will become required interface members. They are
 *            omitted from the v5 line to avoid breaking third-party
 *            implementors:
 *              - addCheck()                : string
 *              - createMaterializedView()  : string
 *              - dropCheck()               : string
 *              - dropMaterializedView()    : string
 *              - onConflictUpdate()        : string
 *              - refreshMaterializedView() : string
 *              - returning()               : string
 */
interface Dialect
{
    /**
     * No row-lock modifier - the default behavior for `forUpdate()`.
     */
    public const LOCK_NONE = '';

    /**
     * Append `NOWAIT` to the `FOR UPDATE` clause.
     */
    public const LOCK_NOWAIT = 'NOWAIT';

    /**
     * Append `SKIP LOCKED` to the `FOR UPDATE` clause.
     */
    public const LOCK_SKIP_LOCKED = 'SKIP LOCKED';

    /**
     * Generates SQL to add a column to a table
     */
    public function addColumn(
        string $tableName,
        string $schemaName,
        ColumnInterface $column
    ): string;

    /**
     * Generates SQL to add an index to a table
     */
    public function addForeignKey(
        string $tableName,
        string $schemaName,
        ReferenceInterface $reference
    ): string;

    /**
     * Generates SQL to add an index to a table
     */
    public function addIndex(
        string $tableName,
        string $schemaName,
        IndexInterface $index
    ): string;

    /**
     * Generates SQL to add the primary key to a table
     */
    public function addPrimaryKey(
        string $tableName,
        string $schemaName,
        IndexInterface $index
    ): string;

    /**
     * Generate SQL to create a new savepoint
     */
    public function createSavepoint(string $name): string;

    /**
     * Generates SQL to create a table
     */
    public function createTable(
        string $tableName,
        string $schemaName,
        array $definition
    ): string;

    /**
     * Generates SQL to create a view
     */
    public function createView(
        string $viewName,
        array $definition,
        string | null $schemaName = null
    ): string;

    /**
     * Generates SQL to describe a table
     */
    public function describeColumns(
        string $tableName,
        string | null $schemaName = null
    ): string;

    /**
     * Generates SQL to query indexes on a table.
     *
     * The base adapter consumes the result as `FETCH_NUM` rows by position:
     * column index 2 must be the index key name and column index 4 the indexed
     * column name.
     */
    public function describeIndexes(
        string $tableName,
        string | null $schemaName = null
    ): string;

    /**
     * Generates SQL to query foreign keys on a table.
     *
     * The base adapter consumes the result as `FETCH_NUM` rows by position:
     * index 1 the local column, index 2 the constraint name, index 3 the
     * referenced schema, index 4 the referenced table, and index 5 the
     * referenced column.
     */
    public function describeReferences(
        string $tableName,
        string | null $schemaName = null
    ): string;

    /**
     * Generates SQL to delete a column from a table
     */
    public function dropColumn(
        string $tableName,
        string $schemaName,
        string $columnName
    ): string;

    /**
     * Generates SQL to delete a foreign key from a table
     */
    public function dropForeignKey(
        string $tableName,
        string $schemaName,
        string $referenceName
    ): string;

    /**
     * Generates SQL to delete an index from a table
     */
    public function dropIndex(
        string $tableName,
        string $schemaName,
        string $indexName
    ): string;

    /**
     * Generates SQL to delete primary key from a table
     */
    public function dropPrimaryKey(string $tableName, string $schemaName): string;

    /**
     * Generates SQL to drop a table
     *
     * @param string|null $schemaName
     */
    public function dropTable(
        string $tableName,
        string $schemaName,
        bool $ifExists = true
    ): string;

    /**
     * Generates SQL to drop a view
     */
    public function dropView(
        string $viewName,
        string | null $schemaName = null,
        bool $ifExists = true
    ): string;

    /**
     * Returns a SQL modified with a FOR UPDATE clause
     */
    public function forUpdate(string $sqlQuery, string $modifier = ''): string;

    /**
     * Gets the column name in RDBMS
     */
    public function getColumnDefinition(ColumnInterface $column): string;

    /**
     * Gets a list of columns
     */
    public function getColumnList(array $columnList): string;

    /**
     * Returns registered functions
     */
    public function getCustomFunctions(): array;

    /**
     * Transforms an intermediate representation for an expression into a
     * database system valid expression
     */
    public function getSqlExpression(
        array $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string;

    /**
     * Generates the SQL for LIMIT clause
     */
    public function limit(string $sqlQuery, array | int $number): string;

    /**
     * List all tables in database
     */
    public function listTables(string | null $schemaName = null): string;

    /**
     * Generates SQL to modify a column in a table
     */
    public function modifyColumn(
        string $tableName,
        string $schemaName,
        ColumnInterface $column,
        ColumnInterface | null $currentColumn = null
    ): string;

    /**
     * Registers custom SQL functions
     */
    public function registerCustomFunction(
        string $name,
        callable $customFunction
    ): DbDialect;

    /**
     * Generate SQL to release a savepoint
     */
    public function releaseSavepoint(string $name): string;

    /**
     * Generate SQL to rollback a savepoint
     */
    public function rollbackSavepoint(string $name): string;

    /**
     * Builds a SELECT statement
     */
    public function select(array $definition): string;

    /**
     * Returns a SQL modified with a LOCK IN SHARE MODE clause
     */
    public function sharedLock(string $sqlQuery, string $modifier = ''): string;

    /**
     * Checks whether the platform supports releasing savepoints.
     */
    public function supportsReleaseSavepoints(): bool;

    /**
     * Checks whether the platform supports savepoints
     */
    public function supportsSavepoints(): bool;

    /**
     * Generates SQL checking for the existence of a schema.table
     */
    public function tableExists(
        string $tableName,
        string | null $schemaName = null
    ): string;

    /**
     * Generates the SQL to describe the table creation options
     */
    public function tableOptions(
        string $tableName,
        string | null $schemaName = null
    ): string;

    /**
     * Generates SQL checking for the existence of a schema.view
     */
    public function viewExists(
        string $viewName,
        string | null $schemaName = null
    ): string;
}
