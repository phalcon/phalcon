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

namespace Phalcon\Db\Adapter\Pdo;

use Phalcon\Db\Adapter\Pdo\AbstractPdo as PdoAdapter;
use Phalcon\Db\Column;
use Phalcon\Db\ColumnInterface;
use Phalcon\Db\Enum;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\IndexInterface;
use Phalcon\Db\RawValue;
use Phalcon\Db\Reference;
use Phalcon\Db\ReferenceInterface;

use function preg_match;
use function preg_replace;
use function strcasecmp;
use function strtolower;
use function trigger_error;

/**
 * Specific functions for the SQLite database system
 *
 * ```php
 * use Phalcon\Db\Adapter\Pdo\Sqlite;
 *
 * $connection = new Sqlite(
 *     [
 *         "dbname" => "/tmp/test.sqlite",
 *     ]
 * );
 * ```
 */
class Sqlite extends PdoAdapter
{
    /**
     * @var string
     */
    protected string $dialectType = "sqlite";

    /**
     * @var string
     */
    protected string $type = "sqlite";

    /**
     * Constructor for Phalcon\Db\Adapter\Pdo\Sqlite
     *
     * @param array $descriptor
     */
    public function __construct(array $descriptor)
    {
        if (isset($descriptor["charset"])) {
            trigger_error(
                "SQLite does not allow the charset to be changed in the DSN."
            );
        }

        parent::__construct($descriptor);
    }

    /**
     * This method is automatically called in Phalcon\Db\Adapter\Pdo
     * constructor. Call it when you need to restore a database connection.
     *
     * @param array $descriptor
     *
     * @return void
     * @throws Exception
     */
    public function connect(array $descriptor = []): void
    {
        if (empty($descriptor)) {
            $descriptor = $this->descriptor;
        }

        if (isset($descriptor["dbname"])) {
            $descriptor["dsn"] = $descriptor["dbname"];

            unset($descriptor["dbname"]);
        } elseif (!isset($descriptor["dsn"])) {
            throw new Exception(
                "The database must be specified with either 'dbname' or 'dsn'."
            );
        }

        parent::connect($descriptor);
    }

    /**
     * Returns an array of Phalcon\Db\Column objects describing a table
     *
     * ```php
     * print_r(
     *     $connection->describeColumns("posts")
     * );
     * ```
     *
     * @param string $tableName
     * @param string $schemaName
     *
     * @return array|ColumnInterface[]
     * @throws Exception
     */
    public function describeColumns(
        string $tableName,
        string | null $schemaName = null
    ): array {
        $oldColumn   = null;
        $sizePattern = "#\\((\d+)(?:,\\s*(\d+))*\\)#";
        $columns     = [];

        /**
         * We're using FETCH_NUM to fetch the columns
         */
        $fields = $this->fetchAll(
            $this->dialect->describeColumns($tableName, $schemaName),
            Enum::FETCH_NUM
        );

        foreach ($fields as $field) {
            /**
             * By default, the bind types is 2 (string)
             */
            $definition = [
                "bindType" => Column::BIND_PARAM_STR,
            ];

            /**
             * By checking every column type we convert it to a
             * Phalcon\Db\Column
             */
            $columnType = $field[2];

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
             *
             * BIGINT/INT
             */
            $lowerType = strtolower($columnType);
            switch (true) {
                case str_contains($lowerType, "bigint"):
                    /**
                     * Bigint are int
                     */
                    $definition["type"]      = Column::TYPE_BIGINTEGER;
                    $definition["isNumeric"] = true;
                    $definition["bindType"]  = Column::BIND_PARAM_STR;

                    break;
                case str_contains($lowerType, "int"):
                    /**
                     * Smallint/Integers/Int are int
                     */
                    $definition["type"]      = Column::TYPE_INTEGER;
                    $definition["isNumeric"] = true;
                    $definition["bindType"]  = Column::BIND_PARAM_INT;

                    if ($field[5]) {
                        $definition["autoIncrement"] = true;
                    }

                    break;
                case str_contains($lowerType, "tinyint(1)"):
                    /**
                     * Tinyint(1) is boolean
                     */
                    $definition["type"]     = Column::TYPE_BOOLEAN;
                    $definition["bindType"] = Column::BIND_PARAM_BOOL;
                    $columnType             = "boolean"; // Change column type to skip size check

                    break;
                /**
                 * ENUM
                 */
                case str_contains($lowerType, "enum"):
                    /**
                     * Enum are treated as char
                     */
                    $definition["type"] = Column::TYPE_CHAR;

                    break;
                /**
                 * DATE/DATETIME
                 */
                case str_contains($lowerType, "datetime"):
                    /**
                     * Special type for datetime
                     */
                    $definition["type"] = Column::TYPE_DATETIME;

                    break;
                /**
                 * ENUM
                 */
                case str_contains($lowerType, "date"):
                    /**
                     * Date/Datetime are varchars
                     */
                    $definition["type"] = Column::TYPE_DATE;

                    break;
                /**
                 * FLOAT/DECIMAL/DOUBLE
                 */
                case str_contains($lowerType, "decimal"):
                    /**
                     * Decimals are floats
                     */
                    $definition["type"]      = Column::TYPE_DECIMAL;
                    $definition["isNumeric"] = true;
                    $definition["bindType"]  = Column::BIND_PARAM_DECIMAL;

                    break;
                case str_contains($lowerType, "float"):
                    /**
                     * Float/Smallfloats/Decimals are float
                     */
                    $definition["type"]      = Column::TYPE_FLOAT;
                    $definition["isNumeric"] = true;
                    $definition["bindType"]  = Column::TYPE_DECIMAL;

                    break;
                /**
                 * TIMESTAMP
                 */
                case str_contains($lowerType, "timestamp"):
                    /**
                     * Timestamp as date
                     */
                    $definition["type"] = Column::TYPE_TIMESTAMP;

                    break;
                /**
                 * TEXT/VARCHAR/CHAR
                 */
                case str_contains($lowerType, "varchar"):
                    /**
                     * Varchar are varchars
                     */
                    $definition["type"] = Column::TYPE_VARCHAR;

                    break;
                case str_contains($lowerType, "char"):
                    /**
                     * Chars are chars
                     */
                    $definition["type"] = Column::TYPE_CHAR;

                    break;
                /**
                 * Text are varchars
                 */
                case str_contains($lowerType, "text"):
                    $definition["type"] = Column::TYPE_TEXT;

                    break;
                default:
                    /**
                     * By default is string
                     */
                    $definition["type"] = Column::TYPE_VARCHAR;
            }

            /**
             * If the column type has a parentheses we try to get the column
             * size from it
             */
            $matches = [];
            if (
                str_contains($columnType, "(") &&
                preg_match($sizePattern, $columnType, $matches)
            ) {
                if (isset($matches[1])) {
                    $definition["size"] = (int)$matches[1];
                }

                if (isset($matches[2])) {
                    $definition["scale"] = (int)$matches[2];
                }
            }

            /**
             * Check if the column is unsigned, only MySQL support this
             */
            if (str_contains($columnType, "unsigned")) {
                $definition["unsigned"] = true;
            }

            /**
             * Positions
             */
            if (null === $oldColumn) {
                $definition["first"] = true;
            } else {
                $definition["after"] = $oldColumn;
            }

            /**
             * Check if the field is primary key
             */
            if ($field[5]) {
                $definition["primary"] = true;
            }

            /**
             * Check if the column allows null values
             */
            if ($field[3] === 0) {
                $definition["notNull"] = false;
            }

            /**
             * Check if the column is default values
             * When field is empty default value is null
             */
            if (
                !empty($field[4]) &&
                0 !== strcasecmp($field[4], "null")
            ) {
                $definition["default"] = preg_replace(
                    "/^'|'$/",
                    "",
                    $field[4]
                );
            }

            /**
             * Every route is stored as a Phalcon\Db\Column
             */
            $columnName = $field[1];
            $columns[]  = new Column($columnName, $definition);
            $oldColumn  = $columnName;
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
     *
     * @param string $tableName
     * @param string $schemaName
     *
     * @return array|IndexInterface[]
     */
    public function describeIndexes(
        string $tableName,
        string | null $schemaName = null
    ): array {
        $indexes = [];
        $records = $this->fetchAll(
            $this->dialect->describeIndexes($tableName, $schemaName)
        );
        foreach ($records as $index) {
            $keyName = $index["name"];

            if (!isset($indexes[$keyName])) {
                $indexes[$keyName] = [];
            }

            $columns = $indexes[$keyName]["columns"] ?? [];

            $describeIndexes = $this->fetchAll(
                $this->dialect->describeIndex($keyName)
            );

            foreach ($describeIndexes as $describeIndex) {
                $columns[] = $describeIndex["name"];
            }

            $indexes[$keyName]["columns"] = $columns;

            $indexSql = $this->fetchColumn(
                $this->dialect->listIndexesSql($tableName, $schemaName, $keyName)
            );

            $indexes[$keyName]["type"] = "";
            if ($index["unique"]) {
                if (preg_match("# UNIQUE #i", $indexSql)) {
                    $indexes[$keyName]["type"] = "UNIQUE";
                } else {
                    $indexes[$keyName]["type"] = "PRIMARY";
                }
            }
        }

        $indexObjects = [];
        foreach ($indexes as $name => $index) {
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
     * @param string $tableName
     * @param string $schemaName
     *
     * @return array|ReferenceInterface[]
     * @throws Exception
     */
    public function describeReferences(
        string $tableName,
        string | null $schemaName = null
    ): array {
        $references = [];
        $records    = $this->fetchAll(
            $this->dialect->describeReferences($tableName, $schemaName),
            Enum::FETCH_NUM
        );

        foreach ($records as $number => $reference) {
            $constraintName = "foreign_key_" . $number;

            $referencedSchema  = $references[$constraintName]["referencedSchema"] ?? null;
            $referencedTable   = $references[$constraintName]["referencedTable"] ?? $reference[2];
            $columns           = $references[$constraintName]["columns"] ?? [];
            $referencedColumns = $references[$constraintName]["referencedColumns"] ?? [];

            $columns[]           = $reference[3];
            $referencedColumns[] = $reference[4];

            $references[$constraintName] = [
                "referencedSchema"  => $referencedSchema,
                "referencedTable"   => $referencedTable,
                "columns"           => $columns,
                "referencedColumns" => $referencedColumns,
            ];
        }

        $referenceObjects = [];
        foreach ($references as $name => $arrayReference) {
            $referenceObjects[$name] = new Reference(
                $name,
                [
                    "referencedSchema"  => $arrayReference["referencedSchema"],
                    "referencedTable"   => $arrayReference["referencedTable"],
                    "columns"           => $arrayReference["columns"],
                    "referencedColumns" => $arrayReference["referencedColumns"],
                ]
            );
        }

        return $referenceObjects;
    }

    /**
     * Returns the default value to make the RBDM use the default value declared
     * in the table definition
     *
     *```php
     * // Inserting a new robot with a valid default value for the column 'year'
     * $success = $connection->insert(
     *     "robots",
     *     [
     *         "Astro Boy",
     *         $connection->getDefaultValue(),
     *     ],
     *     [
     *         "name",
     *         "year",
     *     ]
     * );
     *```
     *
     * @return RawValue
     */
    public function getDefaultValue(): RawValue
    {
        return new RawValue("NULL");
    }

    /**
     * SQLite does not support the DEFAULT keyword
     *
     * @return bool
     * @deprecated Will re removed in the next version
     *
     */
    public function supportsDefaultValue(): bool
    {
        return false;
    }

    /**
     * Check whether the database system requires an explicit value for identity
     * columns
     *
     * @return bool
     */
    public function useExplicitIdValue(): bool
    {
        return true;
    }

    /**
     * Returns PDO adapter DSN defaults as a key-value map.
     *
     * @return array
     */
    protected function getDsnDefaults(): array
    {
        return [];
    }
}
