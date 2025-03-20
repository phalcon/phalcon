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
use Phalcon\Db\ColumnInterface;
use Phalcon\Db\Dialect;
use Phalcon\Db\Dialect\Traits\TextTrait;
use Phalcon\Db\Exception;
use Phalcon\Db\IndexInterface;
use Phalcon\Db\ReferenceInterface;

use function addcslashes;
use function is_array;
use function is_string;
use function strtoupper;
use function substr;

/**
 * Generates database specific SQL for the SQLite RDBMS
 */
class Sqlite extends Dialect
{
    use TextTrait;

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
     * @throws Exception
     */
    public function addColumn(
        string $tableName,
        string $schemaName,
        ColumnInterface $column
    ): string {
        $sql = $this->alter($tableName, $schemaName)
            . " ADD COLUMN "
            . $this->delimit($column->getName(), '"')
            . ' '
            . $this->getColumnDefinition($column);

        if (true === $column->hasDefault()) {
            $defaultValue = $column->getDefault();

            if (is_string($defaultValue)) {
                if (str_contains(strtoupper($defaultValue), "CURRENT_TIMESTAMP")) {
                    $sql .= " DEFAULT CURRENT_TIMESTAMP";
                } else {
                    $sql .= ' DEFAULT "'
                        . addcslashes($defaultValue, '"')
                        . '"';
                }
            } else {
                $sql .= " DEFAULT " . $defaultValue;
            }
        }

        $sql .= $this->checkColumnIsNull($column)
            . $this->getNullString();


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
     * @throws Exception
     */
    public function addIndex(
        string $tableName,
        string $schemaName,
        IndexInterface $index
    ): string {
        $indexType = $index->getType();

        if (!empty($indexType)) {
            $sql = "CREATE " . $indexType . " INDEX ";
        } else {
            $sql = "CREATE INDEX ";
        }

        if (!empty($schemaName)) {
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
        $tableName = $this->prepareTable($tableName, $schemaName);
        $options   = $definition["options"] ?? [];
        $temporary = $options["temporary"] ?? null;

        if (!isset($definition["columns"])) {
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
        $sql .= " TABLE " . $tableName;

        $sql .= " (\n\t";

        $hasPrimary  = false;
        $createLines = [];
        $columns     = $definition["columns"];

        foreach ($columns as $column) {
            $columnLine = $this->delimit($column->getName())
                . ' '
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

                if (is_string($defaultValue)) {
                    if (str_contains(strtoupper($defaultValue), "CURRENT_TIMESTAMP")) {
                        $columnLine .= " DEFAULT CURRENT_TIMESTAMP";
                    } else {
                        $columnLine .= " DEFAULT \""
                            . addcslashes($defaultValue, "\"")
                            . "\"";
                    }
                } else {
                    $columnLine .= " DEFAULT " . $defaultValue;
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
                !empty($indexType) &&
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
            if (!empty($onDelete)) {
                $referenceSql .= " ON DELETE " . $onDelete;
            }

            $onUpdate = $reference->getOnUpdate();
            if (!empty($onUpdate)) {
                $referenceSql .= " ON UPDATE " . $onUpdate;
            }

            $createLines[] = $referenceSql;
        }

        $sql .= implode(",\n\t", $createLines) . "\n)";

        return $sql;
    }

    /**
     * Generates SQL to create a view
     *
     * @param string      $viewName
     * @param array       $definition
     * @param string|null $schemaName
     *
     * @return string
     * @throws Exception
     */
    public function createView(
        string $viewName,
        array $definition,
        string | null $schemaName = null
    ): string {
        if (!isset($definition["sql"])) {
            throw new Exception(
                "The index 'sql' is required in the definition array"
            );
        }

        return "CREATE VIEW "
            . $this->prepareTable($viewName, $schemaName)
            . " AS "
            . $definition["sql"];
    }

    /**
     * Generates SQL describing a table
     *
     * ```php
     * print_r(
     *     $dialect->describeColumns("posts")
     * );
     * ```
     *
     * @param string      $tableName
     * @param string|null $schemaName
     *
     * @return string
     */
    public function describeColumns(
        string $tableName,
        string | null $schemaName = null
    ): string {
        return "PRAGMA table_info('" . $tableName . "')";
    }

    /**
     * Generates SQL to query indexes detail on a table
     *
     * @param string $index
     *
     * @return string
     */
    public function describeIndex(string $index): string
    {
        return "PRAGMA index_info('" . $index . "')";
    }

    /**
     * Generates SQL to query indexes on a table
     *
     * @param string $tableName
     * @param string $schemaName
     *
     * @return string
     */
    public function describeIndexes(
        string $tableName,
        string | null $schemaName = null
    ): string {
        return "PRAGMA index_list('" . $tableName . "')";
    }

    /**
     * Generates SQL to query foreign keys on a table
     *
     * @param string $tableName
     * @param string $schemaName
     *
     * @return string
     */
    public function describeReferences(
        string $tableName,
        string | null $schemaName = null
    ): string {
        return "PRAGMA foreign_key_list('" . $tableName . "')";
    }

    /**
     * Generates SQL to delete a column from a table
     *
     * @param string $tableName
     * @param string $schemaName
     * @param string $columnName
     *
     * @return string
     * @throws Exception
     */
    public function dropColumn(
        string $tableName,
        string $schemaName,
        string $columnName
    ): string {
        throw new Exception("Dropping DB column is not supported by SQLite");
    }

    /**
     * Generates SQL to delete a foreign key from a table
     *
     * @param string $tableName
     * @param string $schemaName
     * @param string $referenceName
     *
     * @return string
     * @throws Exception
     */
    public function dropForeignKey(
        string $tableName,
        string $schemaName,
        string $referenceName
    ): string {
        throw new Exception(
            "Dropping a foreign key constraint is not supported by SQLite"
        );
    }

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
    ): string {
        if (!empty($schemaName)) {
            return "DROP INDEX \"" . $schemaName . "\".\"" . $indexName . "\"";
        }

        return "DROP INDEX \"" . $indexName . "\"";
    }

    /**
     * Generates SQL to delete primary key from a table
     *
     * @param string $tableName
     * @param string $schemaName
     *
     * @return string
     * @throws Exception
     */
    public function dropPrimaryKey(
        string $tableName,
        string $schemaName
    ): string {
        throw new Exception(
            "Removing a primary key after table has been created is not supported by SQLite"
        );
    }

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
    ): string {
        $tableName = $this->prepareTable($tableName, $schemaName);

        if (true === $ifExists) {
            return "DROP TABLE IF EXISTS " . $tableName;
        }

        return "DROP TABLE " . $tableName;
    }

    /**
     * Generates SQL to drop a view
     *
     * @param string $viewName
     * @param string $schemaName
     * @param bool   $ifExists
     *
     * @return string
     */
    public function dropView(
        string $viewName,
        string | null $schemaName = null,
        bool $ifExists = true
    ): string {
        $view = $this->prepareTable($viewName, $schemaName);

        if (true === $ifExists) {
            return "DROP VIEW IF EXISTS " . $view;
        }

        return "DROP VIEW " . $view;
    }

    /**
     * Returns a SQL modified with a FOR UPDATE clause. For SQLite, it returns
     * the original query
     *
     * @param string $sqlQuery
     *
     * @return string
     */
    public function forUpdate(string $sqlQuery): string
    {
        return $sqlQuery;
    }

    /**
     * Gets the column name in SQLite
     *
     * @param ColumnInterface $column
     *
     * @return string
     * @throws Exception
     */
    public function getColumnDefinition(ColumnInterface $column): string
    {
        $columnSql  = $this->checkColumnTypeSql($column);
        $columnType = $this->checkColumnType($column);

        // SQLite has dynamic column typing. The conversion below maximizes
        // compatibility with other DBMS's while following the type affinity
        // rules: https://www.sqlite.org/datatype3.html.
        switch ($columnType) {
            case Column::TYPE_BIGINTEGER:
                if (empty($columnSql)) {
                    $columnSql .= "BIGINT";
                }

                if (true === $column->isUnsigned()) {
                    $columnSql .= " UNSIGNED";
                }

                break;

            case Column::TYPE_BLOB:
                if (empty($columnSql)) {
                    $columnSql .= "BLOB";
                }

                break;

            case Column::TYPE_BOOLEAN:
                if (empty($columnSql)) {
                    $columnSql .= "TINYINT";
                }

                break;

            case Column::TYPE_CHAR:
                if (empty($columnSql)) {
                    $columnSql .= "CHARACTER";
                }

                $columnSql .= $this->getColumnSize($column);

                break;

            case Column::TYPE_DATE:
                if (empty($columnSql)) {
                    $columnSql .= "DATE";
                }

                break;

            case Column::TYPE_DATETIME:
                if (empty($columnSql)) {
                    $columnSql .= "DATETIME";
                }

                break;

            case Column::TYPE_DECIMAL:
                if (empty($columnSql)) {
                    $columnSql .= "NUMERIC";
                }

                $columnSql .= $this->getColumnSizeAndScale($column);

                break;

            case Column::TYPE_DOUBLE:
                if (empty($columnSql)) {
                    $columnSql .= "DOUBLE";
                }

                if (true === $column->isUnsigned()) {
                    $columnSql .= " UNSIGNED";
                }

                break;

            case Column::TYPE_FLOAT:
                if (empty($columnSql)) {
                    $columnSql .= "FLOAT";
                }

                break;

            case Column::TYPE_INTEGER:
                if (empty($columnSql)) {
                    $columnSql .= "INTEGER";
                }

                break;

            case Column::TYPE_LONGBLOB:
                if (empty($columnSql)) {
                    $columnSql .= "LONGBLOB";
                }

                break;

            case Column::TYPE_MEDIUMBLOB:
                if (empty($columnSql)) {
                    $columnSql .= "MEDIUMBLOB";
                }

                break;

            case Column::TYPE_TEXT:
                if (empty($columnSql)) {
                    $columnSql .= "TEXT";
                }

                break;

            case Column::TYPE_TIMESTAMP:
                if (empty($columnSql)) {
                    $columnSql .= "TIMESTAMP";
                }

                break;

            case Column::TYPE_TINYBLOB:
                if (empty($columnSql)) {
                    $columnSql .= "TINYBLOB";
                }

                break;

            case Column::TYPE_VARCHAR:
                if (empty($columnSql)) {
                    $columnSql .= "VARCHAR";
                }

                $columnSql .= $this->getColumnSize($column);

                break;

            default:
                if (empty($columnSql)) {
                    throw new Exception(
                        "Unrecognized SQLite data type at column "
                        . $column->getName()
                    );
                }

                $valueSql   = "";
                $typeValues = $column->getTypeValues();
                if (!empty($typeValues)) {
                    if (is_array($typeValues)) {
                        foreach ($typeValues as $value) {
                            $valueSql .= "\""
                                . addcslashes($value, "\"")
                                . "\", ";
                        }

                        $columnSql .= "("
                            . substr($valueSql, 0, -2)
                            . ")";
                    } else {
                        $columnSql .= "(\""
                            . addcslashes($typeValues, "\"")
                            . "\")";
                    }
                }
        }

        return $columnSql;
    }

    /**
     * Generates the SQL to get query list of indexes
     *
     * ```php
     * print_r(
     *     $dialect->listIndexesSql("blog")
     * );
     * ```
     *
     * @param string      $tableName
     * @param string      $schemaName
     * @param string|null $keyName
     *
     * @return string
     */
    public function listIndexesSql(
        string $tableName,
        string | null $schemaName = null,
        string | null $keyName = null
    ): string {
        $sql = "SELECT sql "
            . "FROM sqlite_master "
            . "WHERE type = 'index' "
            . "AND tbl_name = " . $this->escape($tableName) . " COLLATE NOCASE";

        if (!empty($keyName)) {
            $sql .= " AND name = " . $this->escape($keyName) . " COLLATE NOCASE";
        }

        return $sql;
    }

    /**
     * List all tables in database
     *
     * ```php
     * print_r(
     *     $dialect->listTables("blog")
     * );
     * ```
     *
     * @param string $schemaName
     *
     * @return string
     */
    public function listTables(string | null $schemaName = null): string
    {
        return "SELECT tbl_name "
            . "FROM sqlite_master "
            . "WHERE type = 'table' ORDER BY tbl_name";
    }

    /**
     * Generates the SQL to list all views of a schema or user
     *
     * @param string $schemaName
     *
     * @return string
     */
    public function listViews(string | null $schemaName = null): string
    {
        return "SELECT tbl_name "
            . "FROM sqlite_master "
            . "WHERE type = 'view' "
            . "ORDER BY tbl_name";
    }

    /**
     * Generates SQL to modify a column in a table
     *
     * @param string               $tableName
     * @param string               $schemaName
     * @param ColumnInterface      $column
     * @param ColumnInterface|null $currentColumn
     *
     * @return string
     * @throws Exception
     */
    public function modifyColumn(
        string $tableName,
        string $schemaName,
        ColumnInterface $column,
        ?ColumnInterface $currentColumn = null
    ): string {
        throw new Exception("Altering a DB column is not supported by SQLite");
    }

    /**
     * Returns a SQL modified a shared lock statement. For now this method
     * returns the original query
     *
     * @param string $sqlQuery
     *
     * @return string
     */
    public function sharedLock(string $sqlQuery): string
    {
        return $sqlQuery;
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
    public function tableExists(
        string $tableName,
        string | null $schemaName = null
    ): string {
        return "SELECT CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END "
            . "FROM sqlite_master "
            . "WHERE type='table' AND tbl_name='" . $tableName . "'";
    }

    /**
     * Generates the SQL to describe the table creation options
     *
     * @param string $tableName
     * @param string $schemaName
     *
     * @return string
     */
    public function tableOptions(string $tableName, string | null $schemaName = null): string
    {
        return "";
    }

    /**
     * Generates SQL to truncate a table
     *
     * @param string      $tableName
     * @param string|null $schemaName
     *
     * @return string
     */
    public function truncateTable(
        string $tableName,
        string | null $schemaName = ''
    ): string {
        $schema = "";
        if (!empty($schemaName)) {
            $schema = "\"" . $schemaName . "\".";
        }

        return "DELETE FROM " . $schema . "\"" . $tableName . "\"";
    }

    /**
     * Generates SQL checking for the existence of a schema.view
     *
     * @param string $viewName
     * @param string $schemaName
     *
     * @return string
     */
    public function viewExists(string $viewName, string | null $schemaName = null): string
    {
        return "SELECT CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END FROM "
            . "sqlite_master WHERE type='view' AND tbl_name='" . $viewName . "'";
    }
}
