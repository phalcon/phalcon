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

namespace Phalcon\Db\Dialect;

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\IndexInterface;
use Phalcon\Db\Dialect;
use Phalcon\Db\DialectInterface;
use Phalcon\Db\ColumnInterface;
use Phalcon\Db\ReferenceInterface;

use function str_contains;

/**
 * Generates database specific SQL for the SQLite RDBMS
 */
class Sqlite extends Dialect
{
    /**
     * @var string
     */
    protected string $escapeChar = "\"";

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
    ): string {
        $sql = "ALTER TABLE "
            . $this->prepareTable($tableName, $schemaName)
            . " ADD COLUMN "
            . "\""
            . $column->getName()
            . "\" "
            . $this->getColumnDefinition($column);

        if (true === $column->hasDefault()) {
            $defaultValue = $column->getDefault();

            if (str_contains(strtoupper($defaultValue), "CURRENT_TIMESTAMP")) {
                $sql .= " DEFAULT CURRENT_TIMESTAMP";
            } else {
                $sql .= " DEFAULT \"" . addcslashes($defaultValue, "\"") . "\"";
            }
        }

        if (true === $column->isNotNull()) {
            $sql .= " NOT";
        }
        $sql .= " NULL";

        if (true === $column->isAutoincrement()) {
            $sql .= " PRIMARY KEY AUTOINCREMENT";
        }

        return $sql;
    }

    /**
     * Generates SQL to add an index to a table
     *
     * @param string             $tableName
     * @param string             $schemaName
     * @param ReferenceInterface $reference
     *
     * @return string
     * @throws Exception
     */
    public function addForeignKey(
        string $tableName,
        string $schemaName,
        ReferenceInterface $reference
    ): string {
        throw new Exception(
            "Adding a foreign key constraint to an existing table is not supported by SQLite"
        );
    }

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
    ): string {
        $indexType = $index->getType();

        if (true !== empty($indexType)) {
            $sql = "CREATE " . $indexType . " INDEX ";
        } else {
            $sql = "CREATE INDEX ";
        }

        if (true !== empty($schemaName)) {
            $sql .= "\"" . $schemaName . "\".";
        }

        $sql .= "\""
            . $index->getName()
            . "\" ON \""
            . $tableName
            . "\" (" . $this->getColumnList($index->getColumns()) . ")";

        return $sql;
    }

    /**
     * Generates SQL to add the primary key to a table
     *
     * @param string         $tableName
     * @param string         $schemaName
     * @param IndexInterface $index
     *
     * @return string
     * @throws Exception
     */
    public function addPrimaryKey(
        string $tableName,
        string $schemaName,
        IndexInterface $index
    ): string {
        throw new Exception(
            "Adding a primary key after table has been created is not supported by SQLite"
        );
    }

    /**
     * Generates SQL to create a table
     *
     * @param string $tableName
     * @param string $schemaName
     * @param array  $definition
     *
     * @return string
     * @throws Exception
     */
    public function createTable(
        string $tableName,
        string $schemaName,
        array $definition
    ): string {
        $table     = $this->prepareTable($tableName, $schemaName);
        $options   = $definition["options"] ?? [];
        $temporary = $options["temporary"] ?? null;

        if (true !== isset($definition["columns"])) {
            throw new Exception(
                "The index 'columns' is required in the definition array"
            );
        }

        /**
         * Create a temporary or normal table
         */
        $sql = "CREATE ";
        if ($temporary) {
            $sql = "TEMPORARY";
        }
        $sql .= " TABLE " . $table;

        $sql .= " (\n\t";

        $hasPrimary  = false;
        $createLines = [];
        $columns     = $definition["columns"];

        foreach ($columns as $column) {
            $columnLine = "`"
                . $column->getName()
                . "` "
                . $this->getColumnDefinition($column);

            /**
             * Mark the column as primary key
             */
            if (true === $column->isPrimary() && true !== $hasPrimary) {
                $columnLine .= " PRIMARY KEY";
                $hasPrimary = true;
            }

            /**
             * Add an AUTOINCREMENT clause
             */
            if (true === $column->isAutoIncrement() && true === $hasPrimary) {
                $columnLine .= " AUTOINCREMENT";
            }

            /**
             * Add a Default clause
             */
            if (true === $column->hasDefault()) {
                $defaultValue = $column->getDefault();

                if (str_contains(strtoupper($defaultValue), "CURRENT_TIMESTAMP")) {
                    $columnLine .= " DEFAULT CURRENT_TIMESTAMP";
                } else {
                    $columnLine .= " DEFAULT \""
                        . addcslashes($defaultValue, "\"") . "\"";
                }
            }

            /**
             * Add a NOT NULL clause
             */
            if (true === $column->isNotNull()) {
                $columnLine .= " NOT";
            }
            $columnLine .= " NULL";

            $createLines[] = $columnLine;
        }

        /**
         * Create related indexes
         */
        $indexes = $definition["indexes"] ?? [];
        foreach ($indexes as $index) {
            $indexName = $index->getName();
            $indexType = $index->getType();

            /**
             * If the index name is primary we add a primary key
             */
            if ("PRIMARY" === $indexName && true !== $hasPrimary) {
                $createLines[] = "PRIMARY KEY ("
                    . $this->getColumnList($index->getColumns()) . ")";
            } elseif (
                true !== empty($indexType) &&
                str_contains(strtoupper($indexType), "UNIQUE")
            ) {
                $createLines[] = "UNIQUE ("
                    . $this->getColumnList($index->getColumns()) . ")";
            }
        }

        /**
         * Create related references
         */
        $references = $definition["references"] ?? [];
        foreach ($references as $reference) {
            $referenceSql = "CONSTRAINT `"
                . $reference->getName()
                . "` FOREIGN KEY ("
                . $this->getColumnList($reference->getColumns())
                . ") REFERENCES `"
                . $reference->getReferencedTable()
                . "`("
                . $this->getColumnList($reference->getReferencedColumns())
                . ")";

            $onDelete = $reference->getOnDelete();
            if (true !== empty($onDelete)) {
                $referenceSql .= " ON DELETE " . $onDelete;
            }

            $onUpdate = $reference->getOnUpdate();
            if (true !== empty($onUpdate)) {
                $referenceSql .= " ON UPDATE " . $onUpdate;
            }

            $createLines[] = $referenceSql;
        }

        $sql .= join(",\n\t", $createLines) . "\n)";

        return $sql;
    }

    /**
     * Generates SQL to create a view
     */
    public function createView(string $viewName, array $definition, string schemaName = null): string
    {
        var viewSql;

        if unlikely !fetch viewSql, definition["sql"] {
            throw new Exception(
                "The index 'sql' is required in the definition array"
            );
        }

        return "CREATE VIEW " . this->prepareTable(viewName, schemaName) . " AS " . viewSql;
    }

    /**
     * Generates SQL describing a table
     *
     * ```php
     * print_r(
     *     $dialect->describeColumns("posts")
     * );
     * ```
     */
    public function describeColumns(string $table, string schema = null): string
    {
        return "PRAGMA table_info('" . table . "')";
    }

    /**
     * Generates SQL to query indexes detail on a table
     */
    public function describeIndex(string $index): string
    {
        return "PRAGMA index_info('" . index . "')";
    }

    /**
     * Generates SQL to query indexes on a table
     */
    public function describeIndexes(string $table, string schemaName = ""): string
    {
        return "PRAGMA index_list('" . table . "')";
    }

    /**
     * Generates SQL to query foreign keys on a table
     */
    public function describeReferences(string $table, string $schemaName = ""): string
    {
        return "PRAGMA foreign_key_list('" . table . "')";
    }

    /**
     * Generates SQL to delete a column from a table
     */
    public function dropColumn(string $tableName, string $schemaName, string $columnName): string
    {
        throw new Exception("Dropping DB column is not supported by SQLite");
    }

    /**
     * Generates SQL to delete a foreign key from a table
     */
    public function dropForeignKey(string $tableName, string $schemaName, string $referenceName): string
    {
        throw new Exception(
            "Dropping a foreign key constraint is not supported by SQLite"
        );
    }

    /**
     * Generates SQL to delete an index from a table
     */
    public function dropIndex(string $tableName, string $schemaName, string $indexName): string
    {
        if schemaName {
            return "DROP INDEX \"" . schemaName . "\".\"" . indexName . "\"";
        }

        return "DROP INDEX \"" . indexName . "\"";
    }

    /**
     * Generates SQL to delete primary key from a table
     */
    public function dropPrimaryKey(string $tableName, string $schemaName): string
    {
        throw new Exception(
            "Removing a primary key after table has been created is not supported by SQLite"
        );
    }

    /**
     * Generates SQL to drop a table
     */
    public function dropTable(string $tableName, string schemaName = null, bool! ifExists = true): string
    {
        var table;

        let table = this->prepareTable(tableName, schemaName);

        if ifExists {
            return "DROP TABLE IF EXISTS " . table;
        }

        return "DROP TABLE " . table;
    }

    /**
     * Generates SQL to drop a view
     */
    public function dropView(string $viewName, string schemaName = null, bool! ifExists = true): string
    {
        var view;

        let view = this->prepareTable(viewName, schemaName);

        if ifExists {
            return "DROP VIEW IF EXISTS " . view;
        }

        return "DROP VIEW " . view;
    }

    /**
     * Returns a SQL modified with a FOR UPDATE clause. For SQLite it returns
     * the original query
     */
    public function forUpdate(string $sqlQuery): string
    {
        return sqlQuery;
    }

    /**
     * Gets the column name in SQLite
     */
    public function getColumnDefinition(ColumnInterface $column): string
    {
        var columnType, columnSql, typeValues;

        let columnSql  = this->checkColumnTypeSql(column);
        let columnType = this->checkColumnType(column);

        // SQLite has dynamic column typing. The conversion below maximizes
        // compatibility with other DBMS's while following the type affinity
        // rules: https://www.sqlite.org/datatype3.html.
        switch columnType {

            case Column::TYPE_BIGINTEGER:
                if empty columnSql {
                    let columnSql .= "BIGINT";
                }

                if column->isUnsigned() {
                    let columnSql .= " UNSIGNED";
                }

                break;

            case Column::TYPE_BLOB:
                if empty columnSql {
                    let columnSql .= "BLOB";
                }

                break;

            case Column::TYPE_BOOLEAN:
                if empty columnSql {
                    let columnSql .= "TINYINT";
                }

                break;

            case Column::TYPE_CHAR:
                if empty columnSql {
                    let columnSql .= "CHARACTER";
                }

                let columnSql .= this->getColumnSize(column);

                break;

            case Column::TYPE_DATE:
                if empty columnSql {
                    let columnSql .= "DATE";
                }

                break;

            case Column::TYPE_DATETIME:
                if empty columnSql {
                    let columnSql .= "DATETIME";
                }

                break;

            case Column::TYPE_DECIMAL:
                if empty columnSql {
                    let columnSql .= "NUMERIC";
                }

                let columnSql .= this->getColumnSizeAndScale(column);

                break;

            case Column::TYPE_DOUBLE:
                if empty columnSql {
                    let columnSql .= "DOUBLE";
                }

                if column->isUnsigned() {
                    let columnSql .= " UNSIGNED";
                }

                break;

            case Column::TYPE_FLOAT:
                if empty columnSql {
                    let columnSql .= "FLOAT";
                }

                break;

            case Column::TYPE_INTEGER:
                if empty columnSql {
                    let columnSql .= "INTEGER";
                }

                break;

            case Column::TYPE_LONGBLOB:
                if empty columnSql {
                    let columnSql .= "LONGBLOB";
                }

                break;

            case Column::TYPE_MEDIUMBLOB:
                if empty columnSql {
                    let columnSql .= "MEDIUMBLOB";
                }

                break;

            case Column::TYPE_TEXT:
                if empty columnSql {
                    let columnSql .= "TEXT";
                }

                break;

            case Column::TYPE_TIMESTAMP:
                if empty columnSql {
                    let columnSql .= "TIMESTAMP";
                }

                break;

            case Column::TYPE_TINYBLOB:
                if empty columnSql {
                    let columnSql .= "TINYBLOB";
                }

                break;

            case Column::TYPE_VARCHAR:
                if empty columnSql {
                    let columnSql .= "VARCHAR";
                }

                let columnSql .= this->getColumnSize(column);

                break;

            default:
                if empty columnSql {
                    throw new Exception(
                        "Unrecognized SQLite data type at column " . column->getName()
                    );
                }

                let typeValues = column->getTypeValues();
                if !empty typeValues {
                    if typeof typeValues == "array" {
                        var value, valueSql;

                        let valueSql = "";

                        for value in typeValues {
                            let valueSql .= "\"" . addcslashes(value, "\"") . "\", ";
                        }

                        let columnSql .= "(" . substr(valueSql, 0, -2) . ")";
                    } else {
                        let columnSql .= "(\"" . addcslashes(typeValues, "\"") . "\")";
                    }
                }
        }

        return columnSql;
    }

    /**
     * Generates the SQL to get query list of indexes
     *
     * ```php
     * print_r(
     *     $dialect->listIndexesSql("blog")
     * );
     * ```
     */
    public function listIndexesSql(string $table, string schema = null, string keyName = null): string
    {
        string sql;

        let sql = "SELECT sql FROM sqlite_master WHERE type = 'index' AND tbl_name = ". this->escape(table) ." COLLATE NOCASE";

        if keyName {
            let sql .= " AND name = ". this->escape(keyName) ." COLLATE NOCASE";
        }

        return sql;
    }

    /**
     * List all tables in database
     *
     * ```php
     * print_r(
     *     $dialect->listTables("blog")
     * );
     * ```
     */
    public function listTables(string schemaName = ""): string
    {
        return "SELECT tbl_name FROM sqlite_master WHERE type = 'table' ORDER BY tbl_name";
    }

    /**
     * Generates the SQL to list all views of a schema or user
     */
    public function listViews(string $schemaName = ""): string
    {
        return "SELECT tbl_name FROM sqlite_master WHERE type = 'view' ORDER BY tbl_name";
    }

    /**
     * Generates SQL to modify a column in a table
     */
    public function modifyColumn(string $tableName, string $schemaName, ColumnInterface $column, <ColumnInterface> currentColumn = null): string
    {
        throw new Exception("Altering a DB column is not supported by SQLite");
    }

    /**
     * Returns a SQL modified a shared lock statement. For now this method
     * returns the original query
     */
    public function sharedLock(string $sqlQuery): string
    {
        return sqlQuery;
    }

    /**
     * Generates SQL checking for the existence of a schema.table
     *
     * ```php
     * echo $dialect->tableExists("posts", "blog");
     *
     * echo $dialect->tableExists("posts");
     * ```
     */
    public function tableExists(string $tableName, string schemaName = ""): string
    {
        return "SELECT CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END FROM sqlite_master WHERE type='table' AND tbl_name='" . tableName . "'";
    }

    /**
     * Generates the SQL to describe the table creation options
     *
     * @param string $table
     * @param string $schemaName
     *
     * @return string
     */
    public function tableOptions(string $table, string $schemaName = ""): string
    {
        return "";
    }

    /**
     * Generates SQL to truncate a table
     */
    public function truncateTable(string $tableName, string $schemaName): string
    {
        string table;

        if schemaName {
            let table = "\"" . schemaName . "\".\"" . tableName . "\"";
        } else {
            let table = "\"" . tableName . "\"";
        }

        return "DELETE FROM " . table;
    }

    /**
     * Generates SQL checking for the existence of a schema.view
     *
     * @param string $viewName
     * @param string $schemaName
     *
     * @return string
     */
    public function viewExists(string $viewName, string $schemaName = ""): string
    {
        return "SELECT CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END FROM "
            . "sqlite_master WHERE type='view' AND tbl_name='" . $viewName . "'";
    }
}
