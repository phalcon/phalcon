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
use Phalcon\Db\Index;
use Phalcon\Db\IndexInterface;
use Phalcon\Db\ReferenceInterface;

use function addcslashes;
use function implode;
use function is_array;
use function substr;
use function trim;

/**
 * Generates database specific SQL for the MySQL RDBMS
 */
class Mysql extends Dialect
{
    use TextTrait;

    private const DEFAULT_SCHEMA = "DATABASE()";
    private const STR_NULL = " NULL";

    /**
     * @var string
     */
    protected string $escapeChar = "`";

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
        return $this->alter($tableName, $schemaName)
            . ' ADD '
            . $this->delimit($column->getName())
            . " "
            . $this->getColumnDefinition($column)
            . $this->checkColumnIsNull($column)
            . self::STR_NULL
            . $this->checkColumnHasDefault($column)
            . $this->checkColumnIsAutoIncrement($column)
            . $this->checkColumnFirstAfterPositions($column)
        ;
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
        $indexType = $index->getType() ? $index->getType() . ' ' : '';

        return $this->alter($tableName, $schemaName)
            .' ADD ' . $indexType . 'INDEX '
            . $this->delimit($index->getName()) . ' '
            . $this->wrap($this->getColumnList($index->getColumns()));
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
        return $this->alter($tableName, $schemaName)
            . ' ADD PRIMARY KEY '
            . $this->wrap($this->getColumnList($index->getColumns()));
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
        if (true !== isset($definition["columns"])) {
            throw new Exception(
                "The index 'columns' is required in the definition array"
            );
        }

        $tableName = $this->prepareTable($tableName, $schemaName);
        $options   = $definition["options"] ?? [];
        $temporary = $options["temporary"] ?? null;

        /**
         * Create a temporary or normal table
         */
        $temp = $temporary ? 'TEMPORARY ' : '';
        $sql = 'CREATE ' . $temp . 'TABLE ' . $tableName . " (\n\t";

        $createLines = $this->getTableColumns($definition)
            + $this->getTableIndexes($definition)
            + $this->getTableReferences($definition)
        ;

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
        string $schemaName = null
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
        ?string $schemaName = null
    ): string {
        return "SHOW FULL COLUMNS FROM "
            . $this->prepareTable($tableName, $schemaName);
    }

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
        ?string $schemaName = null
    ): string {
        return "SHOW INDEXES FROM "
            . $this->prepareTable($tableName, $schemaName);
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
        ?string $schemaName = null
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

        if (true !== empty($schemaName)) {
            $sql .= "KCU.CONSTRAINT_SCHEMA = '"
                . $schemaName
                . "' AND KCU.TABLE_NAME = '"
                . $tableName
                . "'";
        } else {
            $sql .= "KCU.CONSTRAINT_SCHEMA = DATABASE() "
                . "AND KCU.TABLE_NAME = '" . $tableName . "'";
        }

        return $sql;
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
        return $this->alterTableDrop(
            'COLUMN',
            $columnName,
            $tableName,
            $schemaName
        );
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
        return $this->alterTableDrop(
            'FOREIGN KEY',
            $referenceName,
            $tableName,
            $schemaName
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
        return $this->alterTableDrop(
            'INDEX',
            $indexName,
            $tableName,
            $schemaName
        );
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
        return $this->alter($tableName, $schemaName)
            . " DROP PRIMARY KEY";
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
        ?string $schemaName = null,
        bool $ifExists = true
    ): string {
        return $this->drop('TABLE')
            . $this->exists($ifExists)
            . $this->prepareTable($tableName, $schemaName);
    }

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
        ?string $schemaName = null,
        bool $ifExists = true
    ): string {
        return $this->drop('VIEW')
            . $this->exists($ifExists)
            . $this->prepareTable($viewName, $schemaName);
    }

    /**
     * Gets the column name in MySQL
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
                if (true === empty($columnSql)) {
                    $columnSql .= "BIGINT";
                }

                $columnSql .= $this->getColumnSize($column)
                    . $this->checkColumnUnsigned($column);

                break;

            case Column::TYPE_BIT:
                if (true === empty($columnSql)) {
                    $columnSql .= "BIT";
                }

                $columnSql .= $this->getColumnSize($column);

                break;

            case Column::TYPE_BLOB:
                if (true === empty($columnSql)) {
                    $columnSql .= "BLOB";
                }

                break;

            case Column::TYPE_BOOLEAN:
                if (true === empty($columnSql)) {
                    $columnSql .= "TINYINT(1)";
                }

                break;

            case Column::TYPE_CHAR:
                if (true === empty($columnSql)) {
                    $columnSql .= "CHAR";
                }

                $columnSql .= $this->getColumnSize($column);

                break;

            case Column::TYPE_DATE:
                if (true === empty($columnSql)) {
                    $columnSql .= "DATE";
                }

                break;

            case Column::TYPE_DATETIME:
                if (true === empty($columnSql)) {
                    $columnSql .= "DATETIME";
                }

                if ($column->getSize() > 0) {
                    $columnSql .= $this->getColumnSize($column);
                }

                break;

            case Column::TYPE_DECIMAL:
                if (true === empty($columnSql)) {
                    $columnSql .= "DECIMAL";
                }

                $columnSql .= $this->getColumnSizeAndScale($column)
                    . $this->checkColumnUnsigned($column);

                break;

            case Column::TYPE_DOUBLE:
                if (true === empty($columnSql)) {
                    $columnSql .= "DOUBLE";
                }

                $columnSql .= $this->checkColumnSizeAndScale($column)
                    . $this->checkColumnUnsigned($column);

                break;

            case Column::TYPE_ENUM:
                if (true === empty($columnSql)) {
                    $columnSql .= "ENUM";
                }

                $columnSql .= $this->getColumnSize($column);

                break;

            case Column::TYPE_FLOAT:
                if (true === empty($columnSql)) {
                    $columnSql .= "FLOAT";
                }

                $columnSql .= $this->checkColumnSizeAndScale($column)
                    . $this->checkColumnUnsigned($column);

                break;

            case Column::TYPE_INTEGER:
                if (true === empty($columnSql)) {
                    $columnSql .= "INT";
                }

                $columnSql .= $this->getColumnSize($column)
                    . $this->checkColumnUnsigned($column);

                break;

            case Column::TYPE_JSON:
                if (true === empty($columnSql)) {
                    $columnSql .= "JSON";
                }

                break;

            case Column::TYPE_LONGBLOB:
                if (true === empty($columnSql)) {
                    $columnSql .= "LONGBLOB";
                }

                break;

            case Column::TYPE_LONGTEXT:
                if (true === empty($columnSql)) {
                    $columnSql .= "LONGTEXT";
                }

                break;

            case Column::TYPE_MEDIUMBLOB:
                if (true === empty($columnSql)) {
                    $columnSql .= "MEDIUMBLOB";
                }

                break;

            case Column::TYPE_MEDIUMINTEGER:
                if (true === empty($columnSql)) {
                    $columnSql .= "MEDIUMINT";
                }

                $columnSql .= $this->getColumnSize($column)
                    . $this->checkColumnUnsigned($column);

                break;

            case Column::TYPE_MEDIUMTEXT:
                if (true === empty($columnSql)) {
                    $columnSql .= "MEDIUMTEXT";
                }

                break;

            case Column::TYPE_SMALLINTEGER:
                if (true === empty($columnSql)) {
                    $columnSql .= "SMALLINT";
                }

                $columnSql .= $this->getColumnSize($column)
                    . $this->checkColumnUnsigned($column);

                break;

            case Column::TYPE_TEXT:
                if (true === empty($columnSql)) {
                    $columnSql .= "TEXT";
                }

                break;

            case Column::TYPE_TIME:
                if (true === empty($columnSql)) {
                    $columnSql .= "TIME";
                }

                if ($column->getSize() > 0) {
                    $columnSql .= $this->getColumnSize($column);
                }

                break;

            case Column::TYPE_TIMESTAMP:
                if (true === empty($columnSql)) {
                    $columnSql .= "TIMESTAMP";
                }

                if ($column->getSize() > 0) {
                    $columnSql .= $this->getColumnSize($column);
                }

                break;

            case Column::TYPE_TINYBLOB:
                if (true === empty($columnSql)) {
                    $columnSql .= "TINYBLOB";
                }

                break;

            case Column::TYPE_TINYINTEGER:
                if (true === empty($columnSql)) {
                    $columnSql .= "TINYINT";
                }

                $columnSql .= $this->getColumnSize($column)
                    . $this->checkColumnUnsigned($column);

                break;

            case Column::TYPE_TINYTEXT:
                if (true === empty($columnSql)) {
                    $columnSql .= "TINYTEXT";
                }

                break;

            case Column::TYPE_VARCHAR:
                if (true === empty($columnSql)) {
                    $columnSql .= "VARCHAR";
                }

                $columnSql .= $this->getColumnSize($column);

                break;

            default:
                if (true === empty($columnSql)) {
                    throw new Exception(
                        "Unrecognized MySQL data type at column " . $column->getName()
                    );
                }

                $typeValues = $column->getTypeValues();
                if (true !== empty($typeValues)) {
                    if (is_array($typeValues)) {
                        $valueSql = "";
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
     * Generates SQL to check DB parameter FOREIGN_KEY_CHECKS.
     *
     * @return string
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
     *
     * @param string|null $schemaName
     *
     * @return string
     */
    public function listTables(?string $schemaName = null): string
    {
        $schema = empty($schemaName) ? "" : " FROM " . $this->delimit($schemaName);

        return "SHOW TABLES" . $schema;
    }

    /**
     * Generates the SQL to list all views of a schema or user
     *
     * @param string|null $schemaName
     *
     * @return string
     */
    public function listViews(?string $schemaName = null): string
    {
        $schema = empty($schemaName) ? self::DEFAULT_SCHEMA : $this->delimit($schemaName, "'");

        return "SELECT `TABLE_NAME` AS view_name "
            . "FROM `INFORMATION_SCHEMA`.`VIEWS` "
            . "WHERE `TABLE_SCHEMA` = " . $schema . " "
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
        ColumnInterface $currentColumn = null
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
            . $this->checkColumnIsNull($column)
            . self::STR_NULL
            . $this->checkColumnHasDefault($column)
            . $this->checkColumnIsAutoIncrement($column)
            . $this->checkColumnComment($column)
            . $this->checkColumnFirstAfterPositions($column)
        ;
    }

    /**
     * Returns a SQL modified with a LOCK IN SHARE MODE clause
     *
     *```php
     * $sql = $dialect->sharedLock("SELECT * FROM robots");
     *
     * echo $sql; // SELECT * FROM robots LOCK IN SHARE MODE
     *```
     *
     * @param string $sqlQuery
     *
     * @return string
     */
    public function sharedLock(string $sqlQuery): string
    {
        return $sqlQuery . " LOCK IN SHARE MODE";
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
        ?string $schemaName = null
    ): string {
        return $this->getExistsSql('TABLES', $tableName, $schemaName);
    }

    /**
     * Generates the SQL to describe the table creation options
     *
     * @param string      $tableName
     * @param string|null $schemaName
     *
     * @return string
     */
    public function tableOptions(string $tableName, ?string $schemaName = null): string
    {
        $schema = empty($schemaName) ? self::DEFAULT_SCHEMA : "'" . $schemaName . "'";

        return "SELECT TABLES.TABLE_TYPE AS table_type,"
            . "TABLES.AUTO_INCREMENT AS auto_increment,"
            . "TABLES.ENGINE AS engine,"
            . "TABLES.TABLE_COLLATION AS table_collation "
            . "FROM INFORMATION_SCHEMA.TABLES WHERE "
            . "TABLES.TABLE_SCHEMA = " . $schema . " "
            . "AND TABLES.TABLE_NAME = '" . $tableName . "'";
    }

    /**
     * Generates SQL to truncate a table
     *
     * @param string $tableName
     * @param string $schemaName
     *
     * @return string
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
     *
     * @param string      $viewName
     * @param string|null $schemaName
     *
     * @return string
     */
    public function viewExists(
        string $viewName,
        ?string $schemaName = null
    ): string {
        return $this->getExistsSql('VIEWS', $viewName, $schemaName);
    }
}
