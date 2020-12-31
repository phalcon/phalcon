<?php

/**
 * This file is part of the Phalcon.
 *
 * (c) Phalcon Team <team@phalcon.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phalcon\Db\Adapter\Pdo;

use Phalcon\Db\Adapter\Pdo\AbstractPdo;
use Phalcon\Db\Column;
use Phalcon\Db\ColumnInterface;
use Phalcon\Db\Enum;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\IndexInterface;
use Phalcon\Db\Reference;
use Phalcon\Db\ReferenceInterface;

/**
 * Specific functions for the MySQL database system
 *
 *```php
 * use Phalcon\Db\Adapter\Pdo\Mysql;
 *
 * $config = [
 *     "host"     => "localhost",
 *     "dbname"   => "blog",
 *     "port"     => 3306,
 *     "username" => "sigma",
 *     "password" => "secret",
 * ];
 *
 * $connection = new Mysql($config);
 *```
 */

class Mysql extends AbstractPdo
{
    /**
     * @var string
     */
    protected string $dialectType = "mysql";

    /**
     * @var string
     */
    protected string $type = "mysql";

    /**
     * Adds a foreign key to a table
     */
    public function addForeignKey(string $tableName, ?string $schemaName,  ReferenceInterface  $reference) : bool
    {
        //var foreignKeyCheck;

        $foreignKeyCheck = $this->{"prepare"}(
            $this->dialect->getForeignKeyChecks()
        );

        if (!$foreignKeyCheck->execute()) {
            throw new Exception(
                "DATABASE PARAMETER 'FOREIGN_KEY_CHECKS' HAS TO BE 1"
            );
        }

        return $this->{"execute"}(
            $this->dialect->addForeignKey(
                $tableName,
                $schemaName,
                $reference
            )
        );
    }
    /** lookup
     *    name => columntype, [Column::BIND_PARAM_STR]
     *    regx capture:  main_type(size) qualifier
     */
    
    static public function get_type_size($type) : array
    {
        $match = null;
        $test = strtolower($type);
        $def = [];
        if (preg_match('/(\w*)\((.*)\)\s*(\w*)|(\w*)/', $test, $match)) {
            if (count($match) === 4) {
                $def['type'] = $match[1];
                $def['size'] = intval($match[2]);
                if  ($match[3] === 'unsigned') {
                        $def['unsigned'] = true;
                }
            }
            else if (count($match)===5) {
                $def['type'] = $test;
            }
        }
        return $def;
    }
    
    protected function getStringTypes() : array
    {
        return [
            "enum" => Column::TYPE_ENUM,
            "datetime" => Column::TYPE_DATETIME,
            "date" => Column::TYPE_DATE,
            "mediumblob" => Column::TYPE_MEDIUMBLOB,
            "longblob" => Column::TYPE_LONGBLOB,
            "tinyblob" => Column::TYPE_TINYBLOB,
            "blob" => Column::TYPE_BLOB,
            "timestamp" => Column::TYPE_TIMESTAMP,
            "time" => Column::TYPE_TIME,
            "json" => Column::TYPE_JSON,
            "longtext" => Column::TYPE_LONGTEXT,
            "mediumtext" => Column::TYPE_MEDIUMTEXT,
            "tinytext" => Column::TYPE_TINYTEXT,
            "text" => Column::TYPE_TEXT,
            "varchar" => Column::TYPE_VARCHAR,
            "char" => Column::TYPE_CHAR, 
        ];
    }
    /** lookup
     *   [columntype, bind type]
     */
    protected function getNumberTypes() : array
    {
        return [
            "bigint" => [Column::TYPE_BIGINTEGER, Column::BIND_PARAM_INT],
            "mediumint" => [Column::TYPE_MEDIUMINTEGER, Column::BIND_PARAM_INT],
            "smallint" => [Column::TYPE_MEDIUMINTEGER, Column::BIND_PARAM_INT],
            "tinyint" => [Column::TYPE_TINYINTEGER, Column::BIND_PARAM_INT],
            "int" => [Column::TYPE_INTEGER, Column::BIND_PARAM_INT],
            "float" => [Column::TYPE_FLOAT, Column::BIND_PARAM_DECIMAL],
            "double" => [Column::TYPE_DOUBLE, Column::BIND_PARAM_DECIMAL],
            "decimal" => [Column::TYPE_DECIMAL, Column::BIND_PARAM_STR],
            "bit" => [Column::TYPE_BIT, Column::BIND_PARAM_INT]
        ];
    }
    
    /**
    }
     * Returns an array of Phalcon\Db\Column objects describing a table
     *
     * ```php
     * print_r(
     *     $connection->describeColumns("posts")
     * );
     * ```
     */
    public function describeColumns(string $table, ?string $schema = null): array
    {
        /*var columns, columnType, fields, field, oldColumn, sizePattern, matches,
            matchOne, matchTwo, columnName;
        array definition; */

        $oldColumn = null;
        $sizePattern = "#\\(([0-9]+)(?:,\\s*([0-9]+))*\\)#";

        $columns = [];

        $fields = $this->fetchAll(
            $this->dialect->describeColumns($table, $schema),
            Enum::FETCH_NUM
        );

        /**
         * Get the SQL to describe a table
         * We're using FETCH_NUM to fetch the columns
         * Get the describe
         * Field Indexes: 0:name, 1:type, 2:not null, 3:key, 4:default, 5:extra
         */
        foreach($fields as $field) {
            /**
             * By default the bind types is two
             */
            $definition = [
                "bindType" =>  Column::BIND_PARAM_STR
            ];

            /**
             * By checking every column type we convert it to a
             * Phalcon\Db\Column
             */
            $columnType = $field[1];

            /**
             * The order of these IF statements matters. Since we are using
             * memstr to figure out whether a particular string exists in the
             * columnType we will end up with false positives if the order
             * changes.
             *
             * For instance if we have a `varchar` column and we check for
             * `char` first, then that will match. Therefore we have firs the IF
             * statements that are "unique" and further down the ones that can
             * appear a substrings of the columnType above them.
             */
            
            $dtype_array = explode(" ", $columnType);
            $fundament = $dtype_array[0];
            $bracket = strpos($fundament,"(");
            if ($bracket !== false) {
                $fundament = substr($fundament,0,$bracket);
            }
            $dtypes = $this->getNumberTypes()[$fundament] ?? null;
            
            if ($dtypes !== null) {
                $definition["type"] = $dtypes[0];
                $definition["isNumberic"] = true;
                $defintion["bindType"] = $dtypes[1];
            }
            else {
                $ctype = $this->getStringTypes()[$fundament] ?? null;
                if ($ctype !== null) {
                    $definition["type"] = $ctype;
                    $definition["bindType"] =  Column::BIND_PARAM_STR;
                }
            }
            
            
            

            /**
             * Positions
             */
            if ($oldColumn === null) {
                $definition["first"] = true;
            } else {
                $definition["after"] = $oldColumn;
            }

            /**
             * Check if the field is primary key
             */
            if ($field[4] === "PRI") {
                $definition["primary"] = true;
            }

            /**
             * Check if the column allows null values
             */
            if ($field[3] === "YES") {
                $definition["notNull"] = false;
            }

            /**
             * Check if the column is auto increment
             */
            if ($field[6] == "auto_increment") {
                $definition["autoIncrement"] = true;
            }

            /**
             * Check if the column has default value
             */
            $hasOnUpdate = (strpos($field[6], "on update") !== false);
            if ($field[5] !== null) {    
                if ($hasOnUpdate) {
                    $definition["default"] = $field[5] . " " . $field[6];
                } else {
                    $definition["default"] = $field[5];
                }
            } else {
                if ($hasOnUpdate) {
                    $definition["default"] = "NULL " . $field[6];
                }
            }
            
            /**
             * Check if the column has comment
             */
             if ($field[8] !== null) {
                $definition["comment"] = $field[8];
             }

            /**
             * Every route is stored as a Phalcon\Db\Column
             */
            $columnName = $field[0];
            $columns[] = new Column($columnName, $definition);
            $oldColumn = $columnName;
        }

        return $columns;
    }

    /**
     * Lists table indexes
     *
     * ```php
     * print_r(
     *     $connection->describeIndexes("robots_parts")
     * );
     * ```
     */
    public function describeIndexes(string $table, ?string $schema = null): array
    {
        //var indexes, index, keyName, indexType, indexObjects, columns, name;

        $indexes = [];

        foreach($this->fetchAll($this->dialect->describeIndexes($table, $schema), Enum::FETCH_ASSOC) as $index) {
            $keyName = $index["Key_name"];
            $indexType = $index["Index_type"];

            if (!isset($indexes[$keyName])) {
                $indexes[$keyName] = [];
            }

            if (!isset( $indexes[$keyName]["columns"])) {
                $columns = [];
            } else {
                $columns = $indexes[$keyName]["columns"];
            }

            $columns[] = $index["Column_name"];
            $indexes[keyName]["columns"] = $columns;

            if ($keyName === "PRIMARY") {
                $indexes[$keyName]["type"] = "PRIMARY";
            } elseif ($indexType === "FULLTEXT") {
                $indexes[$keyName]["type"] = "FULLTEXT";
            } elseif ($index["Non_unique"] == 0) {
                $indexes[$keyName]["type"] = "UNIQUE";
            } else {
                $indexes[$keyName]["type"] = null;
            }
        }

        $indexObjects = [];

        foreach($indexes as $name => $index) {
            $indexObjects[$name] = new Index(
                $name,
                $index["columns"],
                $index["type"]
            );
        }

        return $indexObjects;
    }

    /**
     * Lists table references
     *
     *```php
     * print_r(
     *     $connection->describeReferences("robots_parts")
     * );
     *```
     */
    public function describeReferences(string $table, string $schema = null) : array
    {
        /*var references, reference, arrayReference, constraintName,
            referenceObjects, name, referencedSchema, referencedTable, columns,
            referencedColumns, referenceUpdate, referenceDelete;*/

        $references = [];

        foreach( $this->fetchAll($this->dialect->describeReferences($table, $schema), Enum::FETCH_NUM) as $reference){

            $constraintName = $reference[2];

            if(!isset($references[$constraintName])) {
                $referencedSchema  = $reference[3];
                $referencedTable   = $reference[4];
                $referenceUpdate   = $reference[6];
                $referenceDelete   = $reference[7];
                $columns           = [];
                $referencedColumns = [];
            } else {
                $referencedSchema  = $references[$constraintName]["referencedSchema"];
                $referencedTable   = $references[$constraintName]["referencedTable"];
                $columns           = $references[$constraintName]["columns"];
                $referencedColumns = $references[$constraintName]["referencedColumns"];
                $referenceUpdate   = $references[$constraintName]["onUpdate"];
                $referenceDelete   = $references[$constraintName]["onDelete"];
            }

            $columns[] = $reference[1];
            $referencedColumns[] = $reference[5];

            $references[$constraintName] = [
                "referencedSchema"  => $referencedSchema,
                "referencedTable"   => $referencedTable,
                "columns"           => columns,
                "referencedColumns" => $referencedColumns,
                "onUpdate"          => $referenceUpdate,
                "onDelete"          => $referenceDelete
            ];
        }

        $referenceObjects = [];
        foreach($references as $name => $arrayReference) {
            $referenceObjects[name] = new Reference(
                $name,
                [
                    "referencedSchema"  => $arrayReference["referencedSchema"],
                    "referencedTable"   => $arrayReference["referencedTable"],
                    "columns"           => $arrayReference["columns"],
                    "referencedColumns" => $arrayReference["referencedColumns"],
                    "onUpdate"          => $arrayReference["onUpdate"],
                    "onDelete"          => $arrayReference["onDelete"]
                ]
            );
        }

        return $referenceObjects;
    }

    /**
     * Returns PDO adapter DSN defaults as a key-value map.
     */
    protected function getDsnDefaults() : array
    {
        // In modern MySQL the "utf8mb4" charset is more ideal than just "uf8".
        return [
            "charset" => "utf8mb4"
        ];
    }
    public function getDialectType() : string {
        return $this->dialecttype;
    }    
    public function getType() : string {
        return $this->type;
    }
}

