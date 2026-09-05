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

use Phalcon\Contracts\Db\DbTypes;
use Phalcon\Db\CheckInterface;
use Phalcon\Db\Column;
use Phalcon\Db\ColumnInterface;
use Phalcon\Db\Dialect;
use Phalcon\Db\Dialect\Traits\TextTrait;
use Phalcon\Db\Exception;
use Phalcon\Db\Exceptions\MissingDefinitionKey;
use Phalcon\Db\Exceptions\MysqlOnConflictNotSupported;
use Phalcon\Db\Exceptions\UnrecognizedDataType;
use Phalcon\Db\IndexInterface;
use Phalcon\Db\ReferenceInterface;

use function implode;
use function is_array;
use function str_replace;
use function substr;

/**
 * Generates database specific SQL for the MySQL RDBMS
 *
 * @phpstan-import-type db_column_list from DbTypes
 */
class Mysql extends Dialect
{
    use TextTrait;

    protected string $escapeChar = "`";

    protected array $supportedOperators = ["->", "->>"];

    /**
     * Generates SQL to add a CHECK constraint to an existing table.
     * Enforced by MySQL 8.0.16+.
     */
    public function addCheck(
        string $tableName,
        string $schemaName,
        CheckInterface $check
    ): string {
        return $this->alter($tableName, $schemaName)
            . ' ADD ' . $this->getCheckClause($check, '`');
    }

    /**
     * Generates SQL to add a column to a table
     *
     * @throws Exception
     */
    public function addColumn(
        string $tableName,
        string $schemaName,
        ColumnInterface $column
    ): string {
        return $this->alter($tableName, $schemaName)
            . ' ADD '
            . $this->delimit($column->getName())
            . " "
            . $this->getColumnDefinition($column)
            . $this->checkColumnIsGenerated($column)
            . $this->checkColumnIsNull($column)
            . $this->getNullString()
            . $this->checkColumnIsInvisible($column)
            . $this->checkColumnHasDefault($column)
            . $this->checkColumnIsAutoIncrement($column)
            . $this->checkColumnFirstAfterPositions($column);
    }

    /**
     * Generates SQL to add an index to a table
     *
     * @throws Exception
     */
    public function addForeignKey(
        string $tableName,
        string $schemaName,
        ReferenceInterface $reference
    ): string {
        return $this->alter($tableName, $schemaName)
            . ' ADD'
            . $this->checkReferenceConstraint($reference)
            . ' FOREIGN KEY '
            . $this->wrap($this->getColumnList($reference->getColumns()))
            . ' REFERENCES '
            . $this->prepareTable($reference->getReferencedTable(), $reference->getReferencedSchema())
            . $this->wrap($this->getColumnList($reference->getReferencedColumns()))
            . $this->checkReferenceOnDelete($reference)
            . $this->checkReferenceOnUpdate($reference);
    }

    /**
     * Generates SQL to add an index to a table
     *
     * @throws Exception
     */
    public function addIndex(
        string $tableName,
        string $schemaName,
        IndexInterface $index
    ): string {
        $indexType = $index->getType() ? $index->getType() . ' ' : '';

        $sql = $this->alter($tableName, $schemaName)
            . ' ADD ' . $indexType . 'INDEX '
            . $this->delimit($index->getName()) . ' '
            . $this->wrap($this->getIndexColumnList($index));

        if ($index->isInvisible()) {
            $sql .= ' INVISIBLE';
        }

        return $sql;
    }

    /**
     * Generates SQL to add the primary key to a table
     *
     * @throws Exception
     */
    public function addPrimaryKey(
        string $tableName,
        string $schemaName,
        IndexInterface $index
    ): string {
        /** @var db_column_list $columns */
        $columns = $index->getColumns();

        return $this->alter($tableName, $schemaName)
            . ' ADD PRIMARY KEY '
            . $this->wrap($this->getColumnList($columns));
    }

    /**
     * Generates SQL to create a table
     *
     * @throws Exception
     */
    public function createTable(
        string $tableName,
        string $schemaName,
        array $definition
    ): string {
        if (!isset($definition["columns"])) {
            throw new MissingDefinitionKey("columns");
        }

        $tableName = $this->prepareTable($tableName, $schemaName);
        $options   = $definition["options"] ?? [];
        $temporary = $options["temporary"] ?? null;

        /**
         * Create a temporary or normal table
         */
        $temp = $temporary ? 'TEMPORARY ' : '';
        $sql  = 'CREATE ' . $temp . 'TABLE ' . $tableName . " (\n\t";

        $createLines = array_merge(
            $this->getTableColumns($definition),
            $this->getTableIndexes($definition),
            $this->getTableReferences($definition),
            $this->getTableChecks($definition)
        );

        /**
         * Create related references
         */

        $sql .= implode(",\n\t", $createLines) . "\n)";

        if (isset($definition["options"])) {
            $sql .= " " . $this->getTableOptions($definition);
        }

        return $sql;
    }

    /**
     * Generates SQL to create a view
     *
     * @throws Exception
     */
    public function createView(
        string $viewName,
        array $definition,
        string | null $schemaName = null
    ): string {
        if (!isset($definition["sql"])) {
            throw new MissingDefinitionKey("sql");
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
     */
    public function describeColumns(
        string $tableName,
        string | null $schemaName = null
    ): string {
        $schemaClause = $schemaName
            ? "'" . $this->escapeStringLiteral($schemaName) . "'"
            : 'DATABASE()';

        /**
         * The result-set shape mirrors `SHOW FULL COLUMNS FROM ...` so the
         * adapter loop continues to read by ordinal index:
         *   0:Field, 1:Type, 2:Collation, 3:Null, 4:Key, 5:Default, 6:Extra,
         *   7:Privileges, 8:Comment
         * Position 9 - GenerationExpression - is appended for the generated
         * column round-trip.
         */
        return "SELECT COLUMN_NAME AS `Field`, COLUMN_TYPE AS `Type`, "
            . "COLLATION_NAME AS `Collation`, IS_NULLABLE AS `Null`, "
            . "COLUMN_KEY AS `Key`, COLUMN_DEFAULT AS `Default`, "
            . "EXTRA AS `Extra`, PRIVILEGES AS `Privileges`, "
            . "COLUMN_COMMENT AS `Comment`, "
            . "GENERATION_EXPRESSION AS `GenerationExpression` "
            . "FROM `INFORMATION_SCHEMA`.`COLUMNS` "
            . "WHERE `TABLE_SCHEMA` = " . $schemaClause . " "
            . "AND `TABLE_NAME` = '" . $this->escapeStringLiteral($tableName) . "' "
            . "ORDER BY `ORDINAL_POSITION`";
    }

    /**
     * Generates SQL to query indexes on a table
     */
    public function describeIndexes(
        string $tableName,
        string | null $schemaName = null
    ): string {
        return "SHOW INDEXES FROM "
            . $this->prepareTable($tableName, $schemaName);
    }

    /**
     * Generates SQL to query foreign keys on a table
     */
    public function describeReferences(
        string $tableName,
        string | null $schemaName = null
    ): string {
        $sql = "SELECT DISTINCT KCU.TABLE_NAME, KCU.COLUMN_NAME, "
            . "KCU.CONSTRAINT_NAME, KCU.REFERENCED_TABLE_SCHEMA, "
            . "KCU.REFERENCED_TABLE_NAME, KCU.REFERENCED_COLUMN_NAME, "
            . "RC.UPDATE_RULE, RC.DELETE_RULE "
            . "FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KCU "
            . "LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS AS RC "
            . "ON RC.CONSTRAINT_NAME = KCU.CONSTRAINT_NAME "
            . "AND RC.CONSTRAINT_SCHEMA = KCU.CONSTRAINT_SCHEMA "
            . "WHERE KCU.REFERENCED_TABLE_NAME IS NOT NULL AND ";

        if (!empty($schemaName)) {
            $sql .= "KCU.CONSTRAINT_SCHEMA = '"
                . $this->escapeStringLiteral($schemaName)
                . "' AND KCU.TABLE_NAME = '"
                . $this->escapeStringLiteral($tableName)
                . "'";
        } else {
            $sql .= "KCU.CONSTRAINT_SCHEMA = DATABASE() "
                . "AND KCU.TABLE_NAME = '" . $this->escapeStringLiteral($tableName) . "'";
        }

        return $sql;
    }

    /**
     * Generates SQL to delete a CHECK constraint from a table.
     */
    public function dropCheck(
        string $tableName,
        string $schemaName,
        string $checkName
    ): string {
        return $this->alter($tableName, $schemaName)
            . ' DROP CHECK '
            . $this->delimit($checkName);
    }

    /**
     * Generates SQL to delete a column from a table
     */
    public function dropColumn(
        string $tableName,
        string $schemaName,
        string $columnName
    ): string {
        return $this->alterTableDrop(
            'COLUMN',
            $columnName,
            $tableName,
            $schemaName
        );
    }

    /**
     * Generates SQL to delete a foreign key from a table
     */
    public function dropForeignKey(
        string $tableName,
        string $schemaName,
        string $referenceName
    ): string {
        return $this->alterTableDrop(
            'FOREIGN KEY',
            $referenceName,
            $tableName,
            $schemaName
        );
    }

    /**
     * Generates SQL to delete an index from a table
     */
    public function dropIndex(
        string $tableName,
        string $schemaName,
        string $indexName
    ): string {
        return $this->alterTableDrop(
            'INDEX',
            $indexName,
            $tableName,
            $schemaName
        );
    }

    /**
     * Generates SQL to delete primary key from a table
     */
    public function dropPrimaryKey(
        string $tableName,
        string $schemaName
    ): string {
        return $this->alter($tableName, $schemaName)
            . " DROP PRIMARY KEY";
    }

    /**
     * Generates SQL to drop a table
     */
    public function dropTable(
        string $tableName,
        string | null $schemaName = null,
        bool $ifExists = true
    ): string {
        return $this->drop('TABLE')
            . $this->exists($ifExists)
            . $this->prepareTable($tableName, $schemaName);
    }

    /**
     * Generates SQL to drop a view
     */
    public function dropView(
        string $viewName,
        string | null $schemaName = null,
        bool $ifExists = true
    ): string {
        return $this->drop('VIEW')
            . $this->exists($ifExists)
            . $this->prepareTable($viewName, $schemaName);
    }

    /**
     * Gets the column name in MySQL
     *
     * @throws Exception
     */
    public function getColumnDefinition(ColumnInterface $column): string
    {
        $columnSql  = $this->checkColumnTypeSql($column);
        $columnType = $this->checkColumnType($column);

        switch ($columnType) {
            case Column::TYPE_BIGINTEGER:
                if (empty($columnSql)) {
                    $columnSql .= "BIGINT";
                }

                $columnSql .= $this->getColumnSize($column)
                    . $this->checkColumnUnsigned($column);

                break;

            case Column::TYPE_BIT:
                if (empty($columnSql)) {
                    $columnSql .= "BIT";
                }

                $columnSql .= $this->getColumnSize($column);

                break;

            case Column::TYPE_BLOB:
                if (empty($columnSql)) {
                    $columnSql .= "BLOB";
                }

                break;

            case Column::TYPE_BOOLEAN:
                if (empty($columnSql)) {
                    $columnSql .= "TINYINT(1)";
                }

                break;

            case Column::TYPE_CHAR:
                if (empty($columnSql)) {
                    $columnSql .= "CHAR";
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

                if ($column->getSize() > 0) {
                    $columnSql .= $this->getColumnSize($column);
                }

                break;

            case Column::TYPE_DECIMAL:
                if (empty($columnSql)) {
                    $columnSql .= "DECIMAL";
                }

                $columnSql .= $this->getColumnSizeAndScale($column)
                    . $this->checkColumnUnsigned($column);

                break;

            case Column::TYPE_DOUBLE:
                if (empty($columnSql)) {
                    $columnSql .= "DOUBLE";
                }

                $columnSql .= $this->checkColumnSizeAndScale($column)
                    . $this->checkColumnUnsigned($column);

                break;

            case Column::TYPE_ENUM:
                if (empty($columnSql)) {
                    $columnSql .= "ENUM";
                }

                $columnSql .= $this->getColumnSize($column);

                break;

            case Column::TYPE_FLOAT:
                if (empty($columnSql)) {
                    $columnSql .= "FLOAT";
                }

                $columnSql .= $this->checkColumnSizeAndScale($column)
                    . $this->checkColumnUnsigned($column);

                break;

            case Column::TYPE_INTEGER:
                if (empty($columnSql)) {
                    $columnSql .= "INT";
                }

                $columnSql .= $this->getColumnSize($column)
                    . $this->checkColumnUnsigned($column);

                break;

            case Column::TYPE_JSON:
                if (empty($columnSql)) {
                    $columnSql .= "JSON";
                }

                break;

            case Column::TYPE_LONGBLOB:
                if (empty($columnSql)) {
                    $columnSql .= "LONGBLOB";
                }

                break;

            case Column::TYPE_LONGTEXT:
                if (empty($columnSql)) {
                    $columnSql .= "LONGTEXT";
                }

                break;

            case Column::TYPE_MEDIUMBLOB:
                if (empty($columnSql)) {
                    $columnSql .= "MEDIUMBLOB";
                }

                break;

            case Column::TYPE_MEDIUMINTEGER:
                if (empty($columnSql)) {
                    $columnSql .= "MEDIUMINT";
                }

                $columnSql .= $this->getColumnSize($column)
                    . $this->checkColumnUnsigned($column);

                break;

            case Column::TYPE_MEDIUMTEXT:
                if (empty($columnSql)) {
                    $columnSql .= "MEDIUMTEXT";
                }

                break;

            case Column::TYPE_SMALLINTEGER:
                if (empty($columnSql)) {
                    $columnSql .= "SMALLINT";
                }

                $columnSql .= $this->getColumnSize($column)
                    . $this->checkColumnUnsigned($column);

                break;

            case Column::TYPE_TEXT:
                if (empty($columnSql)) {
                    $columnSql .= "TEXT";
                }

                break;

            case Column::TYPE_TIME:
                if (empty($columnSql)) {
                    $columnSql .= "TIME";
                }

                if ($column->getSize() > 0) {
                    $columnSql .= $this->getColumnSize($column);
                }

                break;

            case Column::TYPE_TIMESTAMP:
                if (empty($columnSql)) {
                    $columnSql .= "TIMESTAMP";
                }

                if ($column->getSize() > 0) {
                    $columnSql .= $this->getColumnSize($column);
                }

                break;

            case Column::TYPE_TINYBLOB:
                if (empty($columnSql)) {
                    $columnSql .= "TINYBLOB";
                }

                break;

            case Column::TYPE_TINYINTEGER:
                if (empty($columnSql)) {
                    $columnSql .= "TINYINT";
                }

                $columnSql .= $this->getColumnSize($column)
                    . $this->checkColumnUnsigned($column);

                break;

            case Column::TYPE_TINYTEXT:
                if (empty($columnSql)) {
                    $columnSql .= "TINYTEXT";
                }

                break;

            case Column::TYPE_VARCHAR:
                if (empty($columnSql)) {
                    $columnSql .= "VARCHAR";
                }

                $columnSql .= $this->getColumnSize($column);

                break;

            case Column::TYPE_GEOMETRY:
                if (empty($columnSql)) {
                    $columnSql .= "GEOMETRY";
                }

                break;

            case Column::TYPE_POINT:
                if (empty($columnSql)) {
                    $columnSql .= "POINT";
                }

                break;

            case Column::TYPE_LINESTRING:
                if (empty($columnSql)) {
                    $columnSql .= "LINESTRING";
                }

                break;

            case Column::TYPE_POLYGON:
                if (empty($columnSql)) {
                    $columnSql .= "POLYGON";
                }

                break;

            case Column::TYPE_MULTIPOINT:
                if (empty($columnSql)) {
                    $columnSql .= "MULTIPOINT";
                }

                break;

            case Column::TYPE_MULTILINESTRING:
                if (empty($columnSql)) {
                    $columnSql .= "MULTILINESTRING";
                }

                break;

            case Column::TYPE_MULTIPOLYGON:
                if (empty($columnSql)) {
                    $columnSql .= "MULTIPOLYGON";
                }

                break;

            case Column::TYPE_GEOMETRYCOLLECTION:
                if (empty($columnSql)) {
                    $columnSql .= "GEOMETRYCOLLECTION";
                }

                break;

            default:
                if (empty($columnSql)) {
                    throw new UnrecognizedDataType("MySQL", $column->getName());
                }

                $typeValues = $column->getTypeValues();
                if (!empty($typeValues)) {
                    if (is_array($typeValues)) {
                        $valueSql = "";
                        foreach ($typeValues as $value) {
                            $valueSql .= "'"
                                . $this->escapeStringLiteral($value)
                                . "', ";
                        }

                        $columnSql .= "("
                            . substr($valueSql, 0, -2)
                            . ")";
                    } else {
                        $columnSql .= "('"
                            . $this->escapeStringLiteral((string) $typeValues)
                            . "')";
                    }
                }
        }

        return $columnSql;
    }

    /**
     * Generates SQL to check DB parameter FOREIGN_KEY_CHECKS.
     */
    public function getForeignKeyChecks(): string
    {
        return "SELECT @@foreign_key_checks";
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
    public function listTables(string | null $schemaName = null): string
    {
        $schema = empty($schemaName) ? "" : " FROM " . $this->escape($schemaName);

        return "SHOW TABLES" . $schema;
    }

    /**
     * Generates the SQL to list all views of a schema or user
     */
    public function listViews(string | null $schemaName = null): string
    {
        return "SELECT `TABLE_NAME` AS view_name "
            . "FROM `INFORMATION_SCHEMA`.`VIEWS` "
            . "WHERE `TABLE_SCHEMA` = " . $this->getMysqlSchemaString($schemaName) . " "
            . "ORDER BY view_name";
    }

    /**
     * Generates SQL to modify a column in a table
     *
     * @throws Exception
     */
    public function modifyColumn(
        string $tableName,
        string $schemaName,
        ColumnInterface $column,
        ColumnInterface | null $currentColumn = null
    ): string {
        $columnDefinition = $this->getColumnDefinition($column);

        if (null === $currentColumn) {
            $currentColumn = $column;
        }

        $modify = ' MODIFY ';
        if ($column->getName() !== $currentColumn->getName()) {
            $modify = ' CHANGE COLUMN '
                . $this->delimit($currentColumn->getName())
                . ' ';
        }

        return $this->alter($tableName, $schemaName)
            . $modify
            . $this->delimit($column->getName())
            . ' '
            . $columnDefinition
            . $this->checkColumnIsGenerated($column)
            . $this->checkColumnIsNull($column)
            . $this->getNullString()
            . $this->checkColumnIsInvisible($column)
            . $this->checkColumnHasDefault($column)
            . $this->checkColumnIsAutoIncrement($column)
            . $this->checkColumnComment($column)
            . $this->checkColumnFirstAfterPositions($column);
    }

    /**
     * MySQL does not support the SQL-standard `ON CONFLICT DO UPDATE`
     * upsert syntax - it has its own `INSERT ... ON DUPLICATE KEY UPDATE`.
     *
     * @throws Exception
     */
    public function onConflictUpdate(
        string $sqlQuery,
        array $conflictColumns,
        array $updateColumns
    ): string {
        throw new MysqlOnConflictNotSupported();
    }

    /**
     * Returns a SQL modified with a LOCK IN SHARE MODE clause
     *
     *```php
     * $sql = $dialect->sharedLock("SELECT * FROM co_invoices");
     *
     * echo $sql; // SELECT * FROM co_invoices LOCK IN SHARE MODE
     *```
     */
    public function sharedLock(string $sqlQuery, string $modifier = ''): string
    {
        return $sqlQuery . " LOCK IN SHARE MODE";
    }

    /**
     * MySQL does not support the SQL-standard `ON CONFLICT (...) DO UPDATE`
     * upsert clause; `onConflictUpdate()` throws.
     */
    public function supportsOnConflictUpdate(): bool
    {
        return false;
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
        if (!empty($schemaName)) {
            return "SELECT IF(COUNT(*) > 0, 1, 0) FROM `INFORMATION_SCHEMA`.`TABLES` WHERE `TABLE_NAME`= '"
                . $this->escapeStringLiteral($tableName)
                . "' AND `TABLE_SCHEMA` = '"
                . $this->escapeStringLiteral($schemaName) . "'";
        }

        return "SELECT IF(COUNT(*) > 0, 1, 0) FROM `INFORMATION_SCHEMA`.`TABLES` WHERE `TABLE_NAME` = '"
            . $this->escapeStringLiteral($tableName) . "' AND `TABLE_SCHEMA` = DATABASE()";
    }

    /**
     * Generates the SQL to describe the table creation options
     */
    public function tableOptions(
        string $tableName,
        string | null $schemaName = null
    ): string {
        return "SELECT TABLES.TABLE_TYPE AS table_type,"
            . "TABLES.AUTO_INCREMENT AS auto_increment,"
            . "TABLES.ENGINE AS engine,"
            . "TABLES.TABLE_COLLATION AS table_collation,"
            . "TABLES.TABLE_COMMENT AS table_comment "
            . "FROM INFORMATION_SCHEMA.TABLES WHERE "
            . "TABLES.TABLE_SCHEMA = " . $this->getMysqlSchemaString($schemaName) . " "
            . "AND TABLES.TABLE_NAME = '" . $this->escapeStringLiteral($tableName) . "'";
    }

    /**
     * Generates SQL to truncate a table
     */
    public function truncateTable(
        string $tableName,
        string $schemaName = ''
    ): string {
        $schema = empty($schemaName) ? '' : $this->delimit($schemaName) . '.';

        return "TRUNCATE TABLE " . $schema . $this->delimit($tableName);
    }

    /**
     * Generates SQL checking for the existence of a schema.view
     */
    public function viewExists(
        string $viewName,
        string | null $schemaName = null
    ): string {
        if (!empty($schemaName)) {
            return "SELECT IF(COUNT(*) > 0, 1, 0) FROM `INFORMATION_SCHEMA`.`VIEWS` WHERE `TABLE_NAME`= '"
                . $this->escapeStringLiteral($viewName)
                . "' AND `TABLE_SCHEMA`='"
                . $this->escapeStringLiteral($schemaName) . "'";
        }

        return "SELECT IF(COUNT(*) > 0, 1, 0) FROM `INFORMATION_SCHEMA`.`VIEWS` WHERE `TABLE_NAME`='"
            . $this->escapeStringLiteral($viewName) . "' AND `TABLE_SCHEMA` = DATABASE()";
    }

    /**
     * Escape a string literal for a single quoted SQL string. MySQL treats the
     * backslash as an escape character, so it must be doubled together with the
     * single quote.
     */
    protected function escapeStringLiteral(string $value): string
    {
        return str_replace(
            ["\\", "'"],
            ["\\\\", "''"],
            $value
        );
    }
}
