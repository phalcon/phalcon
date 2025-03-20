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

namespace Phalcon\Db;

/**
 * Interface for Phalcon\Db dialects
 */
interface DialectInterface
{
    /**
     * Generates SQL to add a column to a table
     *
     * @param string          $tableName
     * @param string          $schemaName
     * @param ColumnInterface $column
     *
     * @return string
     */
    public function addColumn(
        string $tableName,
        string $schemaName,
        ColumnInterface $column
    ): string;

    /**
     * Generates SQL to add an index to a table
     *
     * @param string             $tableName
     * @param string             $schemaName
     * @param ReferenceInterface $reference
     *
     * @return string
     */
    public function addForeignKey(
        string $tableName,
        string $schemaName,
        ReferenceInterface $reference
    ): string;

    /**
     * Generates SQL to add an index to a table
     *
     * @param string         $tableName
     * @param string         $schemaName
     * @param IndexInterface $index
     *
     * @return string
     */
    public function addIndex(
        string $tableName,
        string $schemaName,
        IndexInterface $index
    ): string;

    /**
     * Generates SQL to add the primary key to a table
     *
     * @param string         $tableName
     * @param string         $schemaName
     * @param IndexInterface $index
     *
     * @return string
     */
    public function addPrimaryKey(
        string $tableName,
        string $schemaName,
        IndexInterface $index
    ): string;

    /**
     * Generate SQL to create a new savepoint
     *
     * @param string $name
     *
     * @return string
     */
    public function createSavepoint(string $name): string;

    /**
     * Generates SQL to create a table
     *
     * @param string $tableName
     * @param string $schemaName
     * @param array  $definition
     *
     * @return string
     */
    public function createTable(
        string $tableName,
        string $schemaName,
        array $definition
    ): string;

    /**
     * Generates SQL to create a view
     *
     * @param string      $viewName
     * @param array       $definition
     * @param string|null $schemaName
     *
     * @return string
     */
    public function createView(
        string $viewName,
        array $definition,
        string | null $schemaName = null
    ): string;

    /**
     * Generates SQL to describe a table
     *
     * @param string      $tableName
     * @param string|null $schemaName
     *
     * @return string
     */
    public function describeColumns(
        string $tableName,
        string | null $schemaName = null
    ): string;

    /**
     * Generates SQL to query indexes on a table
     *
     * @param string      $tableName
     * @param string|null $schemaName
     *
     * @return string
     */
    public function describeIndexes(
        string $tableName,
        string | null $schemaName = null
    ): string;

    /**
     * Generates SQL to query foreign keys on a table
     *
     * @param string      $tableName
     * @param string|null $schemaName
     *
     * @return string
     */
    public function describeReferences(
        string $tableName,
        string | null $schemaName = null
    ): string;

    /**
     * Generates SQL to delete a column from a table
     *
     * @param string $tableName
     * @param string $schemaName
     * @param string $columnName
     *
     * @return string
     */
    public function dropColumn(
        string $tableName,
        string $schemaName,
        string $columnName
    ): string;

    /**
     * Generates SQL to delete a foreign key from a table
     *
     * @param string $tableName
     * @param string $schemaName
     * @param string $referenceName
     *
     * @return string
     */
    public function dropForeignKey(
        string $tableName,
        string $schemaName,
        string $referenceName
    ): string;

    /**
     * Generates SQL to delete an index from a table
     *
     * @param string $tableName
     * @param string $schemaName
     * @param string $indexName
     *
     * @return string
     */
    public function dropIndex(
        string $tableName,
        string $schemaName,
        string $indexName
    ): string;

    /**
     * Generates SQL to delete primary key from a table
     *
     * @param string $tableName
     * @param string $schemaName
     *
     * @return string
     */
    public function dropPrimaryKey(string $tableName, string $schemaName): string;

    /**
     * Generates SQL to drop a table
     *
     * @param string      $tableName
     * @param string|null $schemaName
     * @param bool        $ifExists
     *
     * @return string
     */
    public function dropTable(
        string $tableName,
        string | null $schemaName = null,
        bool $ifExists = true
    ): string;

    /**
     * Generates SQL to drop a view
     *
     * @param string      $viewName
     * @param string|null $schemaName
     * @param bool        $ifExists
     *
     * @return string
     */
    public function dropView(
        string $viewName,
        string | null $schemaName = null,
        bool $ifExists = true
    ): string;

    /**
     * Returns a SQL modified with a FOR UPDATE clause
     *
     * @param string $sqlQuery
     *
     * @return string
     */
    public function forUpdate(string $sqlQuery): string;

    /**
     * Gets the column name in RDBMS
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
     * Returns registered functions
     *
     * @return array
     */
    public function getCustomFunctions(): array;

    /**
     * Transforms an intermediate representation for an expression into a
     * database system valid expression
     *
     * @param array  $expression
     * @param string $escapeChar
     * @param array  $bindCounts
     *
     * @return string
     */
    public function getSqlExpression(
        array $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string;

    /**
     * Generates the SQL for LIMIT clause
     *
     * @param string    $sqlQuery
     * @param array|int $number
     *
     * @return string
     */
    public function limit(string $sqlQuery, array | int $number): string;

    /**
     * List all tables in database
     *
     * @param string|null $schemaName
     *
     * @return string
     */
    public function listTables(string | null $schemaName = null): string;

    /**
     * Generates the SQL to list all views of a schema or user
     *
     * @param string|null $schemaName
     *
     * @return string
     */
    public function listViews(string | null $schemaName = null): string;

    /**
     * Generates SQL to modify a column in a table
     *
     * @param string               $tableName
     * @param string               $schemaName
     * @param ColumnInterface      $column
     * @param ColumnInterface|null $currentColumn
     *
     * @return string
     */
    public function modifyColumn(
        string $tableName,
        string $schemaName,
        ColumnInterface $column,
        ColumnInterface | null $currentColumn = null
    ): string;

    /**
     * Registers custom SQL functions
     *
     * @param string   $name
     * @param callable $customFunction
     *
     * @return Dialect
     */
    public function registerCustomFunction(
        string $name,
        callable $customFunction
    ): Dialect;

    /**
     * Generate SQL to release a savepoint
     *
     * @param string $name
     *
     * @return string
     */
    public function releaseSavepoint(string $name): string;

    /**
     * Generate SQL to rollback a savepoint
     *
     * @param string $name
     *
     * @return string
     */
    public function rollbackSavepoint(string $name): string;

    /**
     * Builds a SELECT statement
     *
     * @param array $definition
     *
     * @return string
     */
    public function select(array $definition): string;

    /**
     * Returns a SQL modified with a LOCK IN SHARE MODE clause
     *
     * @param string $sqlQuery
     *
     * @return string
     */
    public function sharedLock(string $sqlQuery): string;

    /**
     * Checks whether the platform supports releasing savepoints.
     *
     * @return bool
     */
    public function supportsReleaseSavepoints(): bool;

    /**
     * Checks whether the platform supports savepoints
     *
     * @return bool
     */
    public function supportsSavepoints(): bool;

    /**
     * Generates SQL checking for the existence of a schema.table
     *
     * @param string      $tableName
     * @param string|null $schemaName
     *
     * @return string
     */
    public function tableExists(
        string $tableName,
        string | null $schemaName = null
    ): string;

    /**
     * Generates the SQL to describe the table creation options
     *
     * @param string      $tableName
     * @param string|null $schemaName
     *
     * @return string
     */
    public function tableOptions(
        string $tableName,
        string | null $schemaName = null
    ): string;

    /**
     * Generates SQL checking for the existence of a schema.view
     *
     * @param string      $viewName
     * @param string|null $schemaName
     *
     * @return string
     */
    public function viewExists(
        string $viewName,
        string | null $schemaName = null
    ): string;
}
