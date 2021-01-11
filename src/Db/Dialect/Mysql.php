<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Db\Dialect;

use Phalcon\Db\Dialect;
use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\IndexInterface;
use Phalcon\Db\ColumnInterface;
use Phalcon\Db\ReferenceInterface;
//use Phalcon\Db\DialectInterface;

/**
 * Generates database specific SQL for the MySQL RDBMS
 */
class Mysql extends Dialect 
{
    /**
     * @var string
     */
    protected ?string $escapeChar = "`";

    /**
     * Generates SQL to add a $column to a table
     */
    public function addColumn(string $tableName, ?string $schemaName,  ColumnInterface $column): string
    {

        $sql = "ALTER TABLE " . $this->prepareTable($tableName, $schemaName) . " ADD `" . $column->getName() . "` " . $this->getColumnDefinition($column);

        if ($column->isNotNull()) {
           $sql .= " NOT NULL";
        } else {
            // This is required for some types like TIMESTAMP
            // Query won't be executed if NULL wasn't specified
            // Even if DEFAULT NULL was specified
           $sql .= " NULL";
        }

        if ($column->hasDefault()) {
           $defaultValue = $column->getDefault();
           $upperDefaultValue = strtoupper($defaultValue);

            if (strpos($upperDefaultValue, "CURRENT_TIMESTAMP") !== false || strpos($upperDefaultValue, "NULL") !== false || is_int($defaultValue) || is_float($defaultValue)) {
               $sql .= " DEFAULT " . $defaultValue;
            } else {
               $sql .= " DEFAULT \"" . addcslashes($defaultValue, "\"") . "\"";
            }
        }

        if ($column->isAutoIncrement()) {
           $sql .= " AUTO_INCREMENT";
        }

        if ($column->isFirst()) {
           $sql .= " FIRST";
        } else {
           $afterPosition = $column->getAfterPosition();

            if($afterPosition) {
               $sql .=  " AFTER `" . $afterPosition . "`";
            }
        }

        return $sql;
    }

    /**
     * Generates SQL to add an $index to a table
     */
    public function addForeignKey(string $tableName, ?string $schemaName,  ReferenceInterface $reference): string
    {
       $sql = "ALTER TABLE " . $this->prepareTable($tableName, $schemaName) . " ADD";
        if ($reference->getName()) {
           $sql .= " CONSTRAINT `" . $reference->getName() . "`";
        }

       $sql .= " FOREIGN KEY (" . $this->getColumnList($reference->getColumns()) . ") REFERENCES " . $this->prepareTable($reference->getReferencedTable(), $reference->getReferencedSchema()) . "(" . $this->getColumnList($reference->getReferencedColumns()) . ")";

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
     * Generates SQL to add an $index to a table
     */
    public function addIndex(string $tableName, ?string $schemaName, IndexInterface $index): string
    {

       $sql = "ALTER TABLE " . $this->prepareTable($tableName, $schemaName);

       $indexType = $index->getType();

        if (!empty($indexType)) {
           $sql .= " ADD " . $indexType . " INDEX ";
        } else {
           $sql .= " ADD INDEX ";
        }

       $sql .= "`" . $index->getName() . "` (" . $this->getColumnList($index->getColumns()) . ")";

        return $sql;
    }

    /**
     * Generates SQL to add the primary key to a table
     */
    public function addPrimaryKey(string $tableName, ?string $schemaName, IndexInterface $index): string
    {
        return "ALTER TABLE " . $this->prepareTable($tableName, $schemaName) . " ADD PRIMARY KEY (" . $this->getColumnList($index->getColumns()) . ")";
    }

    /**
     * Generates SQL to create a table
     */
    public function createTable(string $tableName, ?string $schemaName, array $definition): string
    {
        $columns = $definition["columns"] ?? null;
        if ($columns === null) {
            throw new Exception(
                "The index 'columns' is required in the definition array"
            );
        }

       $table = $this->prepareTable($tableName, $schemaName);

       $temporary = false;
       $options = $definition["options"] ?? null;
        if ($options !== null) {
            $temporary = $options["temporary"] ?? null;
        }

        /**
         * Create a temporary or normal table
         */
        if ($temporary) {
           $sql = "CREATE TEMPORARY TABLE " . $table . " (\n\t";
        } else {
           $sql = "CREATE TABLE " . $table . " (\n\t";
        }

       $createLines = [];

        foreach($columns as $column) {
           $columnLine = "`" . $column->getName() . "` " . $this->getColumnDefinition($column);

            /**
             * Add a NOT NULL clause
             */
            if ($column->isNotNull()) {
               $columnLine .= " NOT NULL";
            } else {
                // This is required for some types like TIMESTAMP
                // Query won't be executed if NULL wasn't specified
                // Even if DEFAULT NULL was specified
               $columnLine .= " NULL";
            }

            /**
             * Add a Default clause
             */
            if ($column->hasDefault()) {
               $defaultValue = $column->getDefault();
               $upperDefaultValue = strtoupper($defaultValue);

                if (strpos($upperDefaultValue, "CURRENT_TIMESTAMP")!==false 
                        || strpos($upperDefaultValue, "NULL") !== false 
                        || is_int($defaultValue) || is_float($defaultValue)) {
                   $columnLine .= " DEFAULT " . $defaultValue;
                } else {
                   $columnLine .= " DEFAULT \"" . addcslashes($defaultValue, "\"") . "\"";
                }
            }

            /**
             * Add an AUTO_INCREMENT clause
             */
            if ($column->isAutoIncrement()) {
               $columnLine .= " AUTO_INCREMENT";
            }

            /**
             * Mark the $column as primary key
             */
            if ($column->isPrimary()) {
               $columnLine .= " PRIMARY KEY";
            }

            /**
             * Add a COMMENT clause
             */
             if ($column->getComment()) {
               $columnLine .= " COMMENT '" . $column->getComment() . "'";
            }

           $createLines[] = $columnLine;
        }

        /**
         * Create related $indexes
         */
        $indexes = $definition["indexes"] ?? null;
        if (is_array($indexes)) {
            foreach ($indexes as $index) {
               $indexName = $index->getName();
               $indexType = $index->getType();

                /**
                 * If the $index name is primary we add a primary key
                 */
                if ($indexName === "PRIMARY") {
                   $indexSql = "PRIMARY KEY (" . $this->getColumnList($index->getColumns()) . ")";
                } else {
                    if (!empty($indexType)) {
                       $indexSql = $indexType . " KEY `" . $indexName . "` (" . $this->getColumnList($index->getColumns()) . ")";
                    } else {
                       $indexSql = "KEY `" . $indexName . "` (" . $this->getColumnList($index->getColumns()) . ")";
                    }
                }

               $createLines[] = $indexSql;
            }
        }

        /**
         * Create related $references
         */
        $references = $definition["references"] ?? null;
        if (is_array($references)) {
            foreach($references as $reference) {
               $referenceSql = "CONSTRAINT `" . $reference->getName() 
                    . "` FOREIGN KEY (" . $this->getColumnList($reference->getColumns()) . ")"
                    . " REFERENCES " . $this->prepareTable($reference->getReferencedTable(), $reference->getReferencedSchema())
                    . " (" . $this->getColumnList($reference->getReferencedColumns()) . ")";

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
        }

       $sql .= join(",\n\t", $createLines) . "\n)";

        if(isset($definition["options"])) {
           $sql .= " " . $this->getTableOptions($definition);
        }

        return $sql;
    }

    /**
     * Generates SQL to create a view
     */
    public function createView(string $viewName, array  $definition, ?string $schemaName = null): string
    {
        $viewSql = $definition["sql"] ?? null;
         if ($viewSql === null) {
            throw new Exception(
                "The index 'sql' is required in the definition array"
            );
        }

        return "CREATE VIEW " . $this->prepareTable($viewName, $schemaName) . " AS " . $viewSql;
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
    public function describeColumns(string $table, ?string $schema = null): string
    {
        return "SHOW FULL COLUMNS FROM " . $this->prepareTable($table, $schema);
    }

    /**
     * Generates SQL to query $indexes on a table
     */
    public function describeIndexes(string $table, ?string $schema = null): string
    {
        return "SHOW INDEXES FROM " . $this->prepareTable($table, $schema);
    }

    /**
     * Generates SQL to query foreign keys on a table
     */
    public function describeReferences(string $table, ?string $schema = null): string
    {
       $sql = "SELECT DISTINCT KCU.TABLE_NAME, KCU.COLUMN_NAME, KCU.CONSTRAINT_NAME, " . 
               "KCU.REFERENCED_TABLE_SCHEMA, KCU.REFERENCED_TABLE_NAME, KCU.REFERENCED_COLUMN_NAME, " . 
               "RC.UPDATE_RULE, RC.DELETE_RULE FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS " . 
               "KCU LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS AS RC ON RC.CONSTRAINT_NAME = KCU.CONSTRAINT_NAME " . 
               "AND RC.CONSTRAINT_SCHEMA = KCU.CONSTRAINT_SCHEMA WHERE KCU.REFERENCED_TABLE_NAME IS NOT NULL AND ";
       

        if($schema) {
           $sql .= "KCU.CONSTRAINT_SCHEMA = '" . $schema . "' AND KCU.TABLE_NAME = '" . $table . "'";
        } else {
           $sql .= "KCU.CONSTRAINT_SCHEMA = DATABASE() AND KCU.TABLE_NAME = '" . $table . "'";
        }

        return $sql;
    }

    /**
     * Generates SQL to delete a $column from a table
     */
    public function dropColumn(string $tableName, ?string $schemaName, string $columnName): string
    {
        return "ALTER TABLE " . $this->prepareTable($tableName, $schemaName) . " DROP COLUMN `" . $columnName . "`";
    }

    /**
     * Generates SQL to delete a foreign key from a table
     */
    public function dropForeignKey(string $tableName, ?string $schemaName, string $referenceName): string
    {
        return "ALTER TABLE " . $this->prepareTable($tableName, $schemaName) . " DROP FOREIGN KEY `" . $referenceName . "`";
    }

    /**
     * Generates SQL to delete an $index from a table
     */
    public function dropIndex(string $tableName, ?string $schemaName, string $indexName): string
    {
        return "ALTER TABLE " . $this->prepareTable($tableName, $schemaName) . " DROP INDEX `" . $indexName . "`";
    }

    /**
     * Generates SQL to delete primary key from a table
     */
    public function dropPrimaryKey(string $tableName, ?string $schemaName): string
    {
        return "ALTER TABLE " . $this->prepareTable($tableName, $schemaName) . " DROP PRIMARY KEY";
    }

    /**
     * Generates SQL to drop a table
     */
    public function dropTable(string $tableName, string $schemaName = null, bool $ifExists = true): string
    {
       $table = $this->prepareTable($tableName, $schemaName);

        if ($ifExists) {
            return "DROP TABLE IF EXISTS " . $table;
        }

        return "DROP TABLE " . $table;
    }

    /**
     * Generates SQL to drop a view
     */
    public function dropView(string $viewName, string $schemaName = null, bool $ifExists = true): string
    {
       $view = $this->prepareTable($viewName, $schemaName);

        if ($ifExists) {
            return "DROP VIEW IF EXISTS " . $view;
        }

        return "DROP VIEW " . $view;
    }

    /**
     * Gets the $column name in MySQL
     */
    public function getColumnDefinition( ColumnInterface  $column): string
    {
       $columnSql  = $this->checkColumnTypeSql($column);
       $columnType = $this->checkColumnType($column);

        switch ($columnType) {

            case Column::TYPE_BIGINTEGER:
                if (empty ($columnSql)) {
                   $columnSql .= "BIGINT";
                }

               $columnSql .= $this->getColumnSize($column) . $this->checkColumnUnsigned($column);

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

               $columnSql .= $this->getColumnSizeAndScale($column) . $this->checkColumnUnsigned($column);

                break;

            case Column::TYPE_DOUBLE:
                if (empty($columnSql)) {
                   $columnSql .= "DOUBLE";
                }

               $columnSql .= $this->checkColumnSizeAndScale($column) . $this->checkColumnUnsigned($column);

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

               $columnSql .= $this->checkColumnSizeAndScale($column) . $this->checkColumnUnsigned($column);

                break;

            case Column::TYPE_INTEGER:
                if (empty($columnSql)) {
                   $columnSql .= "INT";
                }

               $columnSql .= $this->getColumnSize($column) . $this->checkColumnUnsigned($column);

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

               $columnSql .= $this->getColumnSize($column) . $this->checkColumnUnsigned($column);

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

               $columnSql .= $this->getColumnSize($column) . $this->checkColumnUnsigned($column);

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

               $columnSql .= $this->getColumnSize($column) . $this->checkColumnUnsigned($column);

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

            default:
                if (empty($columnSql)) {
                    throw new Exception(
                        "Unrecognized MySQL data type at $column " . $column->getName()
                    );
                }

                $typeValues = $column->getTypeValues();
                if (!empty($typeValues)) {
                    if (is_array($typeValues)) {
                        $valueSql = "";

                        foreach($typeValues as $value){
                           $valueSql .= "\"" . addcslashes($value, "\"") . "\", ";
                        }

                       $columnSql .= "(" . substr($valueSql, 0, -2) . ")";
                    } else {
                       $columnSql .= "(\"" . addcslashes($typeValues, "\"") . "\")";
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
    public function listTables(string $schemaName = null): string
    {
        if (!empty($schemaName)) {
            return "SHOW TABLES FROM `" . $schemaName . "`";
        }

        return "SHOW TABLES";
    }

    /**
     * Generates the SQL to list all views of a schema or user
     */
    public function listViews(?string $schemaName = null): string
    {
        if (!empty($schemaName)) {
            return "SELECT `TABLE_NAME` AS view_name FROM `INFORMATION_SCHEMA`.`VIEWS` WHERE `TABLE_SCHEMA` = '" . $schemaName . "' ORDER BY view_name";
        }

        return "SELECT `TABLE_NAME` AS view_name FROM `INFORMATION_SCHEMA`.`VIEWS` WHERE `TABLE_SCHEMA` = DATABASE() ORDER BY view_name";
    }

    /**
     * Generates SQL to modify a $column in a table
     */
    public function modifyColumn(string $tableName, ?string $schemaName,  ColumnInterface  $column,  ?ColumnInterface  $currentColumn = null): string
    {
        $columnDefinition = $this->getColumnDefinition($column);
        $sql = "ALTER TABLE " . $this->prepareTable($tableName, $schemaName);

        if (!is_object($currentColumn)) {
           $currentColumn = $column;
        }

        if ($column->getName() !== $currentColumn->getName()) {
           $sql .= " CHANGE COLUMN `" . $currentColumn->getName() . "` `" . $column->getName() . "` " . $columnDefinition;
        } else {
           $sql .= " MODIFY `" . $column->getName() . "` " . $columnDefinition;
        }

        if ($column->isNotNull()) {
           $sql .= " NOT NULL";
        } else {
            // This is required for some types like TIMESTAMP
            // Query won't be executed if NULL wasn't specified
            // Even if DEFAULT NULL was specified
           $sql .= " NULL";
        }

        if ($column->hasDefault()) {
           $defaultValue = $column->getDefault();
           $upperDefaultValue = strtoupper($defaultValue);

            if (strpos($upperDefaultValue, "CURRENT_TIMESTAMP") !== false 
                    || strpos($upperDefaultValue, "NULL")  !== false 
                    || is_int($defaultValue) || is_float($defaultValue)) {
               $sql .= " DEFAULT " . $defaultValue;
            }  else {
               $sql .= " DEFAULT \"" . addcslashes($defaultValue, "\"") . "\"";
            }
        }

        if ($column->isAutoIncrement()) {
           $sql .= " AUTO_INCREMENT";
        }

        /**
        * Add a COMMENT clause
        */
        if ($column->getComment()) {
           $sql .= " COMMENT '" . $column->getComment() . "'";
        }

        if ($column->isFirst()) {
           $sql .= " FIRST";
        } else {
           $afterPosition = $column->getAfterPosition();

            if ($afterPosition) {
               $sql .=  " AFTER `" . $afterPosition . "`";
            }
        }

        return $sql;
    }

    /**
     * Returns a SQL modified with a LOCK IN SHARE MODE clause
     *
     *```php
     * $sql = $dialect->sharedLock("SELECT * FROM robots");
     *
     * echo $sql; // SELECT * FROM robots LOCK IN SHARE MODE
     *```
     */
    public function sharedLock(string  $sqlQuery): string
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
     */
    public function tableExists(string $tableName, string $schemaName = null): string
    {
        if ($schemaName) {
            return "SELECT IF(COUNT(*) > 0, 1, 0) FROM `INFORMATION_SCHEMA`.`TABLES` WHERE `TABLE_NAME`= '" 
            . $tableName . "' AND `TABLE_SCHEMA` = '" . $schemaName . "'";
        }

        return "SELECT IF(COUNT(*) > 0, 1, 0) FROM `INFORMATION_SCHEMA`.`TABLES` WHERE `TABLE_NAME` = '" 
        . $tableName . "' AND `TABLE_SCHEMA` = DATABASE()";
    }

    /**
     * Generates the SQL to describe the table creation options
     */
    public function tableOptions(string $table, ?string $schema = null): string
    {
       $sql = "SELECT TABLES.TABLE_TYPE AS table_type,TABLES.AUTO_INCREMENT AS auto_increment,TABLES.ENGINE AS engine,TABLES.TABLE_COLLATION AS table_collation FROM INFORMATION_SCHEMA.TABLES WHERE ";

        if ($schema) {
            return $sql . "TABLES.TABLE_SCHEMA = '" . $schema . "' AND TABLES.TABLE_NAME = '" . $table . "'";
        }

        return $sql . "TABLES.TABLE_SCHEMA = DATABASE() AND TABLES.TABLE_NAME = '" . $table . "'";
    }

    /**
     * Generates SQL to truncate a table
     */
    public function truncateTable(string $tableName, ?string $schemaName): string
    {
        if ($schemaName) {
           $table = "`" . $schemaName . "`.`" . $tableName . "`";
        } else {
           $table = "`" . $tableName . "`";
        }

        return "TRUNCATE TABLE " . $table;
    }

    /**
     * Generates SQL checking for the existence of a schema.view
     */
    public function viewExists(string $viewName, string $schemaName = null): string
    {
        if ($schemaName)  {
            return "SELECT IF(COUNT(*) > 0, 1, 0) FROM `INFORMATION_SCHEMA`.`VIEWS` WHERE `TABLE_NAME`= '" . $viewName . "' AND `TABLE_SCHEMA`='" . $schemaName . "'";
        }

        return "SELECT IF(COUNT(*) > 0, 1, 0) FROM `INFORMATION_SCHEMA`.`VIEWS` WHERE `TABLE_NAME`='" . $viewName . "' AND `TABLE_SCHEMA` = DATABASE()";
    }

    /**
     * Generates SQL to add the table creation options
     */
    protected function getTableOptions(array $definition): string
    {
        $options = $definition["options"] ?? null;
        if ($options===null) {
            return "";
        }

        $tableOptions = [];

        /**
         * Check if there is an ENGINE option
         */
        $engine = $options["ENGINE"] ?? null;
        if (!empty($engine)){
               $tableOptions[] = "ENGINE=" . $engine;
        }

        /**
         * Check if there is an AUTO_INCREMENT option
         */
        $autoIncrement = $options["AUTO_INCREMENT"] ?? null;
        if (!empty($autoIncrement)) {
           $tableOptions[] = "AUTO_INCREMENT=" . autoIncrement;
        }
        

        /**
         * Check if there is a TABLE_COLLATION option
         */
        $tableCollation = $options["TABLE_COLLATION"] ?? null;
            if (!empty($tableCollation)) {
               $collationParts = explode("_", $tableCollation);
                $tableOptions[] = "DEFAULT CHARSET=" . $collationParts[0];
                $tableOptions[] = "COLLATE=" . $tableCollation;
            }
 

        return join(" ", $tableOptions);
    }

    /**
     * Checks if the size and/or scale are present and encloses those values
     * in parentheses if need be
     */
    private function checkColumnSizeAndScale(ColumnInterface  $column): string
    {
        if ($column->getSize()) {
           $columnSql .= "(" . $column->getSize();

            if ($column->getScale()) {
               $columnSql .= "," . $column->getScale() . ")";
            } else {
               $columnSql .= ")";
            }
        }

        return $columnSql;
    }

    /**
     * Checks if a $column is unsigned or not and returns the relevant SQL syntax
     */
    private function checkColumnUnsigned(ColumnInterface $column): string
    {
        if ($column->isUnsigned()) {
            return " UNSIGNED";
        }

        return "";
    }
}

