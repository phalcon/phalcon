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
use Phalcon\Db\Exception;
use Phalcon\Db\IndexInterface;
use Phalcon\Db\ReferenceInterface;

use function addcslashes;
use function is_array;
use function strtoupper;
use function substr;

/**
 * Generates database specific SQL for the PostgreSQL RDBMS
 */
class Postgresql extends Dialect
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
     * @throws Exception
     */
    public function addColumn(
        string $tableName,
        string $schemaName,
        ColumnInterface $column
    ): string {
        $columnDefinition = $this->getColumnDefinition($column);

        $sql = "ALTER TABLE "
            . $this->prepareTable($tableName, $schemaName)
            . " ADD COLUMN "
            . "\""
            . $column->getName()
            . "\" " . $columnDefinition;

        if ($column->hasDefault()) {
            $sql .= " DEFAULT " . $this->castDefault($column);
        }

        if ($column->isNotNull()) {
            $sql .= " NOT";
        }

        $sql .= " NULL";

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
        $sql = "ALTER TABLE "
            . $this->prepareTable($tableName, $schemaName)
            . " ADD";

        if (!empty($reference->getName())) {
            $sql .= " CONSTRAINT \"" . $reference->getName() . "\"";
        }

        $sql .= " FOREIGN KEY ("
            . $this->getColumnList($reference->getColumns())
            . ") REFERENCES \"" .
            $reference->getReferencedTable()
            . "\" ("
            . $this->getColumnList($reference->getReferencedColumns())
            . ")";

        $onDelete = $reference->getOnDelete();
        if (!empty($onDelete)) {
            $sql .= " ON DELETE " . $onDelete;
        }

        $onUpdate = $reference->getOnUpdate();
        if (!empty($onUpdate)) {
            $sql .= " ON UPDATE " . $onUpdate;
        }

        return $sql;
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
        if ($index->getName() === "PRIMARY") {
            return $this->addPrimaryKey($tableName, $schemaName, $index);
        }

        $sql = "CREATE";

        $indexType = $index->getType();
        if (!empty($indexType)) {
            $sql .= " " . $indexType;
        }
        $sql .= " INDEX \""
            . $index->getName()
            . "\" ON "
            . $this->prepareTable($tableName, $schemaName)
            . " ("
            . $this->getColumnList($index->getColumns())
            . ")";

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
        return "ALTER TABLE "
            . $this->prepareTable($tableName, $schemaName)
            . " ADD CONSTRAINT \""
            . $tableName
            . "_PRIMARY\" PRIMARY KEY ("
            . $this->getColumnList($index->getColumns())
            . ")";
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
        if (!isset($definition["columns"])) {
            throw new Exception(
                "The index 'columns' is required in the definition array"
            );
        }

        $tableName = $this->prepareTable($tableName, $schemaName);
        $options   = $definition["options"] ?? [];
        $temporary = $options["temporary"] ?? "";
        $temporary = empty($temporary) ? "" : " TEMPORARY";
        /**
         * Create a temporary or normal table
         */
        $sql = "CREATE" . $temporary . " TABLE " . $tableName . " (\n\t";

        /**
         * Create related indexes
         */
        $indexSqlAfterCreate = "";
        $createLines         = [];
        $primaryColumns      = [];

        $columns = $definition["columns"];
        foreach ($columns as $column) {
            $columnDefinition = $this->getColumnDefinition($column);
            $columnLine       = "\""
                . $column->getName() . "\" "
                . $columnDefinition;

            /**
             * Add a Default clause
             */
            if ($column->hasDefault()) {
                $columnLine .= " DEFAULT " . $this->castDefault($column);
            }

            /**
             * Add a NOT NULL clause
             */
            if ($column->isNotNull()) {
                $columnLine .= " NOT";
            }

            $columnLine .= " NULL";

            /**
             * Mark the column as primary key
             */
            if ($column->isPrimary()) {
                $primaryColumns[] = $column->getName();
            }

            $createLines[] = $columnLine;

            /**
             * Add a COMMENT clause
             */
            if (!empty($column->getComment())) {
                $indexSqlAfterCreate .= " COMMENT ON COLUMN "
                    . $tableName
                    . ".\""
                    . $column->getName()
                    . "\" IS '"
                    . $column->getComment()
                    . "';";
            }
        }

        if (!empty($primaryColumns)) {
            $createLines[] = "PRIMARY KEY ("
                . $this->getColumnList($primaryColumns)
                . ")";
        }

        $indexes = $definition["indexes"] ?? [];
        foreach ($indexes as $index) {
            $indexName = $index->getName();
            $indexType = $index->getType();
            $indexSql  = "";

            /**
             * If the index name is primary we add a primary key
             */
            if ($indexName === "PRIMARY") {
                $indexSql = "CONSTRAINT \"PRIMARY\" PRIMARY KEY ("
                    . $this->getColumnList($index->getColumns())
                    . ")";
            } elseif (!empty($indexType)) {
                $indexSql = "CONSTRAINT \""
                    . $indexName
                    . "\" "
                    . $indexType
                    . " (" . $this->getColumnList($index->getColumns())
                    . ")";
            } else {
                $indexSqlAfterCreate .= "CREATE INDEX \""
                    . $index->getName()
                    . "\" ON "
                    . $this->prepareTable($tableName, $schemaName)
                    . " ("
                    . $this->getColumnList($index->getColumns())
                    . ");";
            }

            if (!empty($indexSql)) {
                $createLines[] = $indexSql;
            }
        }

        /**
         * Create related references
         */
        $references = $definition["references"] ?? [];
        foreach ($references as $reference) {
            $referenceSql = "CONSTRAINT \""
                . $reference->getName()
                . "\" FOREIGN KEY ("
                . $this->getColumnList($reference->getColumns())
                . ") REFERENCES "
                . $this->prepareTable($reference->getReferencedTable(), $schemaName)
                . " ("
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
        if (isset($definition["options"])) {
            $sql .= " " . $this->getTableOptions($definition);
        }

        $sql .= ";" . $indexSqlAfterCreate;

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
        if (empty($schemaName)) {
            $schemaName = "public";
        }

        return "SELECT DISTINCT c.column_name AS Field, "
            . "c.data_type AS Type, "
            . "c.character_maximum_length AS Size, "
            . "c.numeric_precision AS NumericSize, "
            . "c.numeric_scale AS NumericScale, "
            . "c.is_nullable AS Null, "
            . "CASE WHEN pkc.column_name NOTNULL THEN 'PRI' ELSE '' END AS Key, "
            . "CASE WHEN c.data_type "
            . "LIKE '%int%' AND c.column_default "
            . "LIKE '%nextval%' THEN 'auto_increment' ELSE '' END AS Extra, "
            . "c.ordinal_position AS Position, "
            . "c.column_default, "
            . "des.description "
            . "FROM information_schema.columns c "
            . "LEFT JOIN ( "
            . "SELECT kcu.column_name, kcu.table_name, kcu.table_schema "
            . "FROM information_schema.table_constraints tc "
            . "INNER JOIN information_schema.key_column_usage kcu "
            . "on (kcu.constraint_name = tc.constraint_name "
            . "and kcu.table_name=tc.table_name "
            . "and kcu.table_schema=tc.table_schema) "
            . "WHERE tc.constraint_type='PRIMARY KEY') pkc "
            . "ON (c.column_name=pkc.column_name "
            . "AND c.table_schema = pkc.table_schema "
            . "AND c.table_name=pkc.table_name) "
            . "LEFT JOIN ( "
            . "SELECT objsubid, description, relname, nspname "
            . "FROM pg_description "
            . "JOIN pg_class ON pg_description.objoid = pg_class.oid "
            . "JOIN pg_namespace ON pg_class.relnamespace = pg_namespace.oid ) des "
            . "ON ( des.objsubid = C.ordinal_position "
            . "AND C.table_schema = des.nspname AND C.TABLE_NAME = des.relname ) "
            . "WHERE c.table_schema='" . $schemaName
            . "' AND c.table_name='" . $tableName
            . "' ORDER BY c.ordinal_position";
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
        return "SELECT 0 as c0, "
            . "t.relname as table_name, "
            . "i.relname as key_name, "
            . "3 as c3, "
            . "a.attname as column_name "
            . "FROM pg_class t, pg_class i, pg_index ix, pg_attribute a "
            . "WHERE t.oid = ix.indrelid "
            . "AND i.oid = ix.indexrelid "
            . "AND a.attrelid = t.oid "
            . "AND a.attnum = ANY(ix.indkey) "
            . "AND t.relkind = 'r' "
            . "AND t.relname = '" . $tableName . "' "
            . "ORDER BY t.relname, i.relname;";
    }

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
    ): string {
        if (empty($schemaName)) {
            $schemaName = "public";
        }

        return "SELECT DISTINCT tc.table_name AS TABLE_NAME, "
            . "kcu.column_name AS COLUMN_NAME, "
            . "tc.constraint_name AS CONSTRAINT_NAME, "
            . "tc.table_catalog AS REFERENCED_TABLE_SCHEMA, "
            . "ccu.table_name AS REFERENCED_TABLE_NAME, "
            . "ccu.column_name AS REFERENCED_COLUMN_NAME, "
            . "rc.update_rule AS UPDATE_RULE, "
            . "rc.delete_rule AS DELETE_RULE "
            . "FROM information_schema.table_constraints AS tc "
            . "JOIN information_schema.key_column_usage AS kcu "
            . "ON tc.constraint_name = kcu.constraint_name "
            . "JOIN information_schema.constraint_column_usage AS ccu "
            . "ON ccu.constraint_name = tc.constraint_name "
            . "JOIN information_schema.referential_constraints rc "
            . "ON tc.constraint_catalog = rc.constraint_catalog "
            . "AND tc.constraint_schema = rc.constraint_schema "
            . "AND tc.constraint_name = rc.constraint_name "
            . "AND tc.constraint_type = 'FOREIGN KEY' "
            . "WHERE constraint_type = 'FOREIGN KEY' "
            . "AND tc.table_schema = '" . $schemaName . "' "
            . "AND tc.table_name='" . $tableName . "'";
    }

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
    ): string {
        return "ALTER TABLE "
            . $this->prepareTable($tableName, $schemaName)
            . " DROP COLUMN \""
            . $columnName
            . "\"";
    }

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
    ): string {
        return "ALTER TABLE "
            . $this->prepareTable($tableName, $schemaName)
            . " DROP CONSTRAINT \""
            . $referenceName
            . "\"";
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
        return "DROP INDEX \"" . $indexName . "\"";
    }

    /**
     * Generates SQL to delete primary key from a table
     *
     * @param string $tableName
     * @param string $schemaName
     *
     * @return string
     */
    public function dropPrimaryKey(
        string $tableName,
        string $schemaName
    ): string {
        return "ALTER TABLE "
            . $this->prepareTable($tableName, $schemaName)
            . " DROP CONSTRAINT \""
            . $tableName
            . "_PRIMARY\"";
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
     * Gets the column name in PostgreSQL
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

        switch ($columnType) {
            case Column::TYPE_BIGINTEGER:
                if (empty($columnSql)) {
                    if ($column->isAutoIncrement()) {
                        $columnSql .= "BIGSERIAL";
                    } else {
                        $columnSql .= "BIGINT";
                    }
                }

                break;

            case Column::TYPE_BOOLEAN:
                if (empty($columnSql)) {
                    $columnSql .= "BOOLEAN";
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
            case Column::TYPE_TIMESTAMP:
                if (empty($columnSql)) {
                    $columnSql .= "TIMESTAMP";
                }
                break;

            case Column::TYPE_DECIMAL:
                if (empty($columnSql)) {
                    $columnSql .= "NUMERIC";
                }

                $columnSql .= $this->getColumnSizeAndScale($column);

                break;

            case Column::TYPE_FLOAT:
                if (empty($columnSql)) {
                    $columnSql .= "FLOAT";
                }

                break;

            case Column::TYPE_INTEGER:
                if (empty($columnSql)) {
                    if ($column->isAutoIncrement()) {
                        $columnSql .= "SERIAL";
                    } else {
                        $columnSql .= "INT";
                    }
                }

                break;

            case Column::TYPE_SMALLINTEGER:
                if (empty($columnSql)) {
                    $columnSql .= "SMALLINT";
                }

                break;

            case Column::TYPE_JSON:
                if (empty($columnSql)) {
                    $columnSql .= "JSON";
                }

                break;

            case Column::TYPE_JSONB:
                if (empty($columnSql)) {
                    $columnSql .= "JSONB";
                }

                break;

            case Column::TYPE_TEXT:
                if (empty($columnSql)) {
                    $columnSql .= "TEXT";
                }

                break;

            case Column::TYPE_VARCHAR:
                if (empty($columnSql)) {
                    $columnSql .= "CHARACTER VARYING";
                }

                $columnSql .= $this->getColumnSize($column);

                break;

            default:
                if (empty($columnSql)) {
                    throw new Exception(
                        "Unrecognized PostgreSQL data type at column " . $column->getName()
                    );
                }

                $typeValues = $column->getTypeValues();
                if (!empty($typeValues)) {
                    if (is_array($typeValues)) {
                        $valueSql = "";
                        foreach ($typeValues as $value) {
                            $valueSql .= "'"
                                . addcslashes($value, "\'")
                                . "', ";
                        }

                        $columnSql .= "("
                            . substr($valueSql, 0, -2)
                            . ")";
                    } else {
                        $columnSql .= "('"
                            . addcslashes($typeValues, "\'")
                            . "')";
                    }
                }
        }

        return $columnSql;
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
     * @param string|null $schemaName
     *
     * @return string
     */
    public function listTables(string | null $schemaName = null): string
    {
        if (empty($schemaName)) {
            $schemaName = "public";
        }

        return "SELECT table_name "
            . "FROM information_schema.tables "
            . "WHERE table_schema = '" . $schemaName . "' "
            . "ORDER BY table_name";
    }

    /**
     * Generates the SQL to list all views of a schema or user
     *
     * @param string|null $schemaName
     *
     * @return string
     */
    public function listViews(string | null $schemaName = null): string
    {
        if (empty($schemaName)) {
            $schemaName = "public";
        }

        return "SELECT viewname AS view_name "
            . "FROM pg_views "
            . "WHERE schemaname = '" . $schemaName . "' "
            . "ORDER BY view_name";
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
        ColumnInterface | null $currentColumn = null
    ): string {
        $sql = "";

        $columnDefinition = $this->getColumnDefinition($column);
        $sqlAlterTable    = "ALTER TABLE "
            . $this->prepareTable($tableName, $schemaName);

        if (null === $currentColumn) {
            $currentColumn = $column;
        }

        // Rename
        if ($column->getName() !== $currentColumn->getName()) {
            $sql .= $sqlAlterTable
                . " RENAME COLUMN \""
                . $currentColumn->getName()
                . "\" TO \""
                . $column->getName()
                . "\";";
        }

        // Change type
        if ($column->getType() !== $currentColumn->getType()) {
            $sql .= $sqlAlterTable
                . " ALTER COLUMN \""
                . $column->getName()
                . "\" TYPE "
                . $columnDefinition
                . ";";
        }

        // NULL
        if ($column->isNotNull() !== $currentColumn->isNotNull()) {
            if ($column->isNotNull()) {
                $sql .= $sqlAlterTable
                    . " ALTER COLUMN \""
                    . $column->getName()
                    . "\" SET NOT NULL;";
            } else {
                $sql .= $sqlAlterTable
                    . " ALTER COLUMN \""
                    . $column->getName()
                    . "\" DROP NOT NULL;";
            }
        }

        /**
         * Add a COMMENT clause
         */
        if (!empty($column->getComment())) {
            $sql .= "COMMENT ON COLUMN "
                . $this->prepareTable($tableName, $schemaName)
                . ".\""
                . $column->getName()
                . "\" IS '"
                . $column->getComment()
                . "';";
        }

        // DEFAULT
        if ($column->getDefault() !== $currentColumn->getDefault()) {
            if (
                empty($column->getDefault()) &&
                !empty($currentColumn->getDefault())
            ) {
                $sql .= $sqlAlterTable
                    . " ALTER COLUMN \""
                    . $column->getName()
                    . "\" DROP DEFAULT;";
            }

            if ($column->hasDefault()) {
                $defaultValue = $this->castDefault($column);

                if (str_contains(strtoupper($columnDefinition), "BOOLEAN")) {
                    $sql .= " ALTER COLUMN \""
                        . $column->getName()
                        . "\" SET DEFAULT "
                        . $defaultValue;
                } else {
                    $sql .= $sqlAlterTable
                        . " ALTER COLUMN \""
                        . $column->getName()
                        . "\" SET DEFAULT "
                        . $defaultValue;
                }
            }
        }

        return $sql;
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
     *
     * @param string      $tableName
     * @param string|null $schemaName
     *
     * @return string
     */
    public function tableExists(
        string $tableName,
        string | null $schemaName = null
    ): string {
        if (!empty($schemaName)) {
            $schemaName = "public";
        }

        return "SELECT CASE "
            . "WHEN COUNT(*) > 0 THEN 1 ELSE 0 END "
            . "FROM information_schema.tables "
            . "WHERE table_schema = '" . $schemaName . "' "
            . "AND table_name='" . $tableName . "'";
    }

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
    ): string {
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
        return "TRUNCATE TABLE " . $this->prepareTable($tableName, $schemaName);
    }

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
    ): string {
        if (!empty($schemaName)) {
            $schemaName = "public";
        }

        return "SELECT "
            . "CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END "
            . "FROM pg_views "
            . "WHERE viewname='" . $viewName
            . "' AND schemaname='" . $schemaName . "'";
    }

    /**
     * @param ColumnInterface $column
     *
     * @return string
     * @throws Exception
     */
    protected function castDefault(ColumnInterface $column): string
    {
        $defaultValue     = $column->getDefault();
        $columnDefinition = $this->getColumnDefinition($column);
        $columnType       = $column->getType();

        if (str_contains(strtoupper($columnDefinition), "BOOLEAN")) {
            return $defaultValue;
        }

        if (
            is_string($defaultValue) &&
            str_contains(strtoupper($defaultValue), "CURRENT_TIMESTAMP")
        ) {
            return "CURRENT_TIMESTAMP";
        }

        if (
            $columnType === Column::TYPE_INTEGER ||
            $columnType === Column::TYPE_BIGINTEGER ||
            $columnType === Column::TYPE_DECIMAL ||
            $columnType === Column::TYPE_FLOAT ||
            $columnType === Column::TYPE_DOUBLE
        ) {
            $preparedValue = (string)$defaultValue;
        } else {
            $preparedValue = "'"
                . addcslashes($defaultValue, "\'")
                . "'";
        }

        return $preparedValue;
    }

    /**
     * @param array $definition
     *
     * @return string
     */
    protected function getTableOptions(array $definition): string
    {
        return "";
    }
}
