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

use PDO;
use Phalcon\Db\Adapter\Pdo\AbstractPdo as PdoAdapter;
use Phalcon\Db\Column;
use Phalcon\Db\ColumnInterface;
use Phalcon\Db\Enum;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\IndexInterface;
use Phalcon\Db\Reference;
use Phalcon\Db\ReferenceInterface;
use Phalcon\Events\Exception as EventsException;

use function preg_match;
use function str_starts_with;
use function strtolower;
use function substr;

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
class Mysql extends PdoAdapter
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
     * Constructor for Phalcon\Db\Adapter\Pdo
     *
     * @param array $descriptor = [
     *                          'host' => 'localhost',
     *                          'port' => '3306',
     *                          'dbname' => 'blog',
     *                          'username' => 'sigma'
     *                          'password' => 'secret'
     *                          'dialectClass' => null,
     *                          'options' => [],
     *                          'dsn' => null,
     *                          'charset' => 'utf8mb4'
     *                          ]
     */
    public function __construct(array $descriptor)
    {
        /**
         * Returning numbers as numbers and not strings. If the user already
         * set this option in the descriptor["options"], we do not have to set
         * anything
         */
        if (!isset($descriptor["options"][PDO::ATTR_EMULATE_PREPARES])) {
            $descriptor["options"][PDO::ATTR_EMULATE_PREPARES] = false;
        }
        if (!isset($descriptor["options"][PDO::ATTR_STRINGIFY_FETCHES])) {
            $descriptor["options"][PDO::ATTR_STRINGIFY_FETCHES] = false;
        }

        parent::__construct($descriptor);
    }

    /**
     * Adds a foreign key to a table
     *
     * @param string             $tableName
     * @param string             $schemaName
     * @param ReferenceInterface $reference
     *
     * @return bool
     * @throws Exception
     * @throws EventsException
     */
    public function addForeignKey(
        string $tableName,
        string $schemaName,
        ReferenceInterface $reference
    ): bool {
        $foreignKeyCheck = $this->prepare($this->dialect->getForeignKeyChecks());

        if (true !== $foreignKeyCheck->execute()) {
            throw new Exception(
                "DATABASE PARAMETER 'FOREIGN_KEY_CHECKS' HAS TO BE 1"
            );
        }

        return $this->execute(
            $this->dialect->addForeignKey(
                $tableName,
                $schemaName,
                $reference
            )
        );
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

        $columns = [];
        $fields  = $this->fetchAll(
            $this->dialect->describeColumns($tableName, $schemaName),
            Enum::FETCH_NUM
        );

        /**
         * Get the SQL to describe a table
         * We're using FETCH_NUM to fetch the columns
         * Get the describe()
         * Field Indexes: 0:name, 1:type, 2:not null, 3:key, 4:default, 5:extra
         */
        foreach ($fields as $field) {
            /**
             * By default, the bind types is 2
             */
            $definition = [
                "bindType" => Column::BIND_PARAM_STR,
            ];

            /**
             * By checking every column type we convert it to a
             * Phalcon\Db\Column
             */
            $columnType = strtolower($field[1]);

            /**
             * The order of these IF statements matters. Since we are using
             * str_contains to figure out whether a particular string exists
             * in the columnType we will end up with false positives if the
             * order changes.
             *
             * For instance if we have a `varchar` column, and we check for
             * `char` first, then that will match. Therefore, we have firs the IF
             * statements that are "unique" and further down the ones that can
             * appear a substrings of the columnType above them.
             */
            switch (true) {
                /**
                 * BIGINT
                 */
                case str_starts_with($columnType, "bigint"):
                    $definition["type"]      = Column::TYPE_BIGINTEGER;
                    $definition["isNumeric"] = true;
                    $definition["bindType"]  = Column::BIND_PARAM_STR;

                    break;

                /**
                 * MEDIUMINT
                 */
                case str_starts_with($columnType, "mediumint"):
                    $definition["type"]      = Column::TYPE_MEDIUMINTEGER;
                    $definition["isNumeric"] = true;
                    $definition["bindType"]  = Column::BIND_PARAM_INT;

                    break;

                /**
                 * SMALLINT
                 */
                case str_starts_with($columnType, "smallint"):
                    $definition["type"]      = Column::TYPE_SMALLINTEGER;
                    $definition["isNumeric"] = true;
                    $definition["bindType"]  = Column::BIND_PARAM_INT;

                    break;

                /**
                 * TINYINT
                 */
                case str_starts_with($columnType, "tinyint"):
                    /**
                     * Smallint/Bigint/Integers/Int are int
                     */
                    $definition["type"]      = Column::TYPE_TINYINTEGER;
                    $definition["isNumeric"] = true;
                    $definition["bindType"]  = Column::BIND_PARAM_INT;

                    break;

                /**
                 * INT
                 */
                case str_starts_with($columnType, "int"):
                    $definition["type"]      = Column::TYPE_INTEGER;
                    $definition["isNumeric"] = true;
                    $definition["bindType"]  = Column::BIND_PARAM_INT;

                    break;

                /**
                 * BIT
                 */
                case str_starts_with($columnType, "bit"):
                    $definition["type"]     = Column::TYPE_BIT;
                    $definition["bindType"] = Column::BIND_PARAM_INT;

                    break;

                /**
                 * ENUM
                 */
                case str_starts_with($columnType, "enum"):
                    $definition["type"] = Column::TYPE_ENUM;

                    break;

                /**
                 * DATE
                 */
                case str_starts_with($columnType, "datetime"):
                    $definition["type"] = Column::TYPE_DATETIME;

                    break;

                /**
                 * DATETIME
                 */
                case str_starts_with($columnType, "date"):
                    $definition["type"] = Column::TYPE_DATE;

                    break;

                /**
                 * DECIMAL - This will need to be a string so as not to lose
                 * the decimals
                 */
                case str_starts_with($columnType, "decimal"):
                    $definition["type"]      = Column::TYPE_DECIMAL;
                    $definition["isNumeric"] = true;

                    break;

                /**
                 * DOUBLE
                 */
                case str_starts_with($columnType, "double"):
                    $definition["type"]      = Column::TYPE_DOUBLE;
                    $definition["isNumeric"] = true;
                    $definition["bindType"]  = Column::BIND_PARAM_DECIMAL;

                    break;

                /**
                 * FLOAT
                 */
                case str_starts_with($columnType, "float"):
                    $definition["type"]      = Column::TYPE_FLOAT;
                    $definition["isNumeric"] = true;
                    $definition["bindType"]  = Column::BIND_PARAM_DECIMAL;

                    break;

                /**
                 * MEDIUMBLOB
                 */
                case str_starts_with($columnType, "mediumblob"):
                    $definition["type"] = Column::TYPE_MEDIUMBLOB;

                    break;

                /**
                 * LONGBLOB
                 */
                case str_starts_with($columnType, "longblob"):
                    $definition["type"] = Column::TYPE_LONGBLOB;

                    break;

                /**
                 * TINYBLOB
                 */
                case str_starts_with($columnType, "tinyblob"):
                    $definition["type"] = Column::TYPE_TINYBLOB;

                    break;

                /**
                 * BLOB
                 */
                case str_starts_with($columnType, "blob"):
                    $definition["type"] = Column::TYPE_BLOB;

                    break;

                /**
                 * TIMESTAMP
                 */
                case str_starts_with($columnType, "timestamp"):
                    $definition["type"] = Column::TYPE_TIMESTAMP;

                    break;

                /**
                 * TIME
                 */
                case str_starts_with($columnType, "time"):
                    $definition["type"] = Column::TYPE_TIME;

                    break;

                /**
                 * JSON
                 */
                case str_starts_with($columnType, "json"):
                    $definition["type"] = Column::TYPE_JSON;

                    break;

                /**
                 * LONGTEXT
                 */
                case str_starts_with($columnType, "longtext"):
                    $definition["type"] = Column::TYPE_LONGTEXT;

                    break;

                /**
                 * MEDIUMTEXT
                 */
                case str_starts_with($columnType, "mediumtext"):
                    $definition["type"] = Column::TYPE_MEDIUMTEXT;

                    break;

                /**
                 * TINYTEXT
                 */
                case str_starts_with($columnType, "tinytext"):
                    $definition["type"] = Column::TYPE_TINYTEXT;

                    break;

                /**
                 * TEXT
                 */
                case str_starts_with($columnType, "text"):
                    $definition["type"] = Column::TYPE_TEXT;

                    break;

                /**
                 * CHAR
                 */
                case str_starts_with($columnType, "char"):
                    $definition["type"] = Column::TYPE_CHAR;

                    break;

                /**
                 * VARBINARY
                 */
                case str_starts_with($columnType, "varbinary"):
                    $definition["type"] = Column::TYPE_VARBINARY;

                    break;

                /**
                 * BINARY
                 */
                case str_starts_with($columnType, "binary"):
                    $definition["type"] = Column::TYPE_BINARY;

                    break;

                /**
                 * Default
                 * VARCHAR
                 */
                default:
                    $definition["type"] = Column::TYPE_VARCHAR;

                    break;
            }

            /**
             * If the column type has a parentheses we try to get the column
             * size from it
             */
            if (str_contains($columnType, "(")) {
                $matches = [];

                if ($definition["type"] == Column::TYPE_ENUM) {
                    $definition["size"] = substr($columnType, 5, -1);
                } elseif (preg_match($sizePattern, $columnType, $matches)) {
                    if (isset($matches[1])) {
                        $definition["size"] = (int)$matches[1];
                    }

                    if (isset($matches[2])) {
                        $definition["scale"] = (int)$matches[2];
                    }
                }
            }

            /**
             * Check if the column is unsigned, only MySQL supports this
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
            if ($field[6] === "auto_increment") {
                $definition["autoIncrement"] = true;
            }

            /**
             * Check if the column has default value
             */
            if (null !== $field[5]) {
                $definition["default"] = $field[5];
                if (str_contains(strtolower($field[6]), "on update")) {
                    $definition["default"] .= " " . $field[6];
                }
            } elseif (str_contains(strtolower($field[6]), "on update")) {
                $definition["default"] = "NULL " . $field[6];
            }

            /**
             * Check if the column has comment
             */
            if (null !== $field[8]) {
                $definition["comment"] = $field[8];
            }

            /**
             * Every route is stored as a Phalcon\Db\Column
             */
            $columnName = $field[0];
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
            $keyName   = $index["Key_name"];
            $indexType = $index["Index_type"];

            if (!isset($indexes[$keyName])) {
                $indexes[$keyName] = [];
            }

            $columns = $indexes[$keyName]["columns"] ?? [];

            $columns[]                    = $index["Column_name"];
            $indexes[$keyName]["columns"] = $columns;

            if ($keyName === "PRIMARY") {
                $indexes[$keyName]["type"] = "PRIMARY";
            } elseif ($indexType == "FULLTEXT") {
                $indexes[$keyName]["type"] = "FULLTEXT";
            } elseif ($index["Non_unique"] === 0) {
                $indexes[$keyName]["type"] = "UNIQUE";
            } else {
                $indexes[$keyName]["type"] = "";
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
     *```php
     * print_r(
     *     $connection->describeReferences("robots_parts")
     * );
     *```
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

        foreach ($records as $reference) {
            $constraintName    = $reference[2];
            $columns           = $references[$constraintName]["columns"] ?? [];
            $referenceDelete   = $references[$constraintName]["onDelete"] ?? $reference[7];
            $referenceUpdate   = $references[$constraintName]["onUpdate"] ?? $reference[6];
            $referencedSchema  = $references[$constraintName]["referencedSchema"] ?? $reference[3];
            $referencedColumns = $references[$constraintName]["referencedColumns"] ?? [];
            $referencedTable   = $references[$constraintName]["referencedTable"] ?? $reference[4];

            $columns[]           = $reference[1];
            $referencedColumns[] = $reference[5];

            $references[$constraintName] = [
                "referencedSchema"  => $referencedSchema,
                "referencedTable"   => $referencedTable,
                "columns"           => $columns,
                "referencedColumns" => $referencedColumns,
                "onUpdate"          => $referenceUpdate,
                "onDelete"          => $referenceDelete,
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
                    "onUpdate"          => $arrayReference["onUpdate"],
                    "onDelete"          => $arrayReference["onDelete"],
                ]
            );
        }

        return $referenceObjects;
    }

    /**
     * Returns PDO adapter DSN defaults as a key-value map.
     *
     * @return string[]
     */
    protected function getDsnDefaults(): array
    {
        // In modern MySQL the "utf8mb4" charset is more ideal than just "uf8".
        return [
            "charset" => "utf8mb4",
        ];
    }
}
