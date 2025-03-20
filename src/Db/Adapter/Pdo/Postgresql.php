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
use Phalcon\Db\RawValue;
use Phalcon\Db\Reference;
use Phalcon\Db\ReferenceInterface;
use Phalcon\Events\Exception as EventsException;
use Throwable;

use function explode;
use function preg_replace;
use function strcasecmp;
use function trigger_error;

/**
 * Specific functions for the PostgreSQL database system
 *
 * ```php
 * use Phalcon\Db\Adapter\Pdo\Postgresql;
 *
 * $config = [
 *     "host"     => "localhost",
 *     "dbname"   => "blog",
 *     "port"     => 5432,
 *     "username" => "postgres",
 *     "password" => "secret",
 * ];
 *
 * $connection = new Postgresql($config);
 * ```
 */
class Postgresql extends PdoAdapter
{
    /**
     * @var string
     */
    protected string $dialectType = "postgresql";

    /**
     * @var string
     */
    protected string $type = "pgsql";

    /**
     * Constructor for Phalcon\Db\Adapter\Pdo\Postgresql
     *
     * @param array $descriptor
     */
    public function __construct(array $descriptor)
    {
        if (isset($descriptor["charset"])) {
            trigger_error(
                "Postgres does not allow the charset to be changed in the DSN."
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
     * @throws EventsException
     */
    public function connect(array $descriptor = []): void
    {
        $descriptor = !empty($descriptor) ? $descriptor : $this->descriptor;
        $schemaName = $descriptor["schema"] ?? null;
        unset($descriptor['schema']);
        if (isset($descriptor["password"])) {
            $password               = $descriptor["password"];
            $descriptor["password"] = (!empty($password)) ? $password : null;
        }

        parent::connect($descriptor);

        if (!empty($schemaName)) {
            $sql = "SET search_path TO '" . $schemaName . "'";

            $this->execute($sql);
        }
    }

    /**
     * Creates a table
     *
     * @param string $tableName
     * @param string $schemaName
     * @param array  $definition
     *
     * @return bool
     * @throws EventsException
     * @throws Exception
     */
    public function createTable(
        string $tableName,
        string $schemaName,
        array $definition
    ): bool {
        if (
            !isset($definition["columns"]) ||
            (isset($definition["columns"]) && empty($definition["columns"]))
        ) {
            throw new Exception(
                "The table must contain at least one column"
            );
        }

        $sql = $this->dialect->createTable(
            $tableName,
            $schemaName,
            $definition
        );

        $queries = explode(";", $sql);
        if (count($queries) > 1) {
            try {
                $this->begin();

                foreach ($queries as $query) {
                    if (empty($query)) {
                        continue;
                    }

                    $this->query($query . ";");
                }

                return $this->commit();
            } catch (Throwable $exception) {
                $this->rollback();

                throw $exception;
            }
        } else {
            return $this->execute($queries[0] . ";");
        }
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
        $oldColumn = null;
        $columns   = [];

        /**
         * We're using FETCH_NUM to fetch the columns
         * 0:name, 1:type, 2:size, 3:numericsize, 4: numericscale, 5: null,
         * 6: key, 7: extra, 8: position, 9 default
         */
        $fields = $this->fetchAll(
            $this->dialect->describeColumns($tableName, $schemaName),
            Enum::FETCH_NUM
        );

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
            $columnType   = $field[1];
            $charSize     = $field[2];
            $numericSize  = $field[3];
            $numericScale = $field[4];

            /**
             * The order of these IF statements matters. Since we are using
             * str_contains to figure out whether a particular string exists in the
             * columnType we will end up with false positives if the order
             * changes.
             *
             * For instance if we have a `varchar` column, and we check for
             * `char` first, then that will match. Therefore, we have firs the IF
             * statements that are "unique" and further down the ones that can
             * appear a substrings of the columnType above them.
             */

            switch (true) {
                /**
                 * BOOL
                 */
                case str_contains($columnType, "boolean"):
                    /**
                     * tinyint(1) is boolean
                     */
                    $definition["type"]     = Column::TYPE_BOOLEAN;
                    $definition["bindType"] = Column::BIND_PARAM_BOOL;

                    break;

                /**
                 * BIGINT
                 */
                case str_contains($columnType, "bigint"):
                    $definition["type"]      = Column::TYPE_BIGINTEGER;
                    $definition["isNumeric"] = true;
                    $definition["bindType"]  = Column::BIND_PARAM_STR;

                    break;

                /**
                 * MEDIUMINT
                 */
                case str_contains($columnType, "mediumint"):
                    $definition["type"]      = Column::TYPE_MEDIUMINTEGER;
                    $definition["isNumeric"] = true;
                    $definition["bindType"]  = Column::BIND_PARAM_INT;

                    break;

                /**
                 * SMALLINT
                 */
                case str_contains($columnType, "smallint"):
                    $definition["type"]      = Column::TYPE_SMALLINTEGER;
                    $definition["isNumeric"] = true;
                    $definition["bindType"]  = Column::BIND_PARAM_INT;

                    break;

                /**
                 * TINYINT
                 */
                case str_contains($columnType, "tinyint"):
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
                case str_contains($columnType, "int"):
                    $definition["type"]      = Column::TYPE_INTEGER;
                    $definition["isNumeric"] = true;
                    $definition["bindType"]  = Column::BIND_PARAM_INT;

                    break;

                /**
                 * BIT
                 */
                case str_contains($columnType, "bit"):
                    $definition["type"] = Column::TYPE_BIT;
                    $definition["size"] = $numericSize;

                    break;

                /**
                 * ENUM
                 */
                case str_contains($columnType, "enum"):
                    $definition["type"] = Column::TYPE_ENUM;

                    break;

                /**
                 * DATE
                 */
                case str_contains($columnType, "datetime"):
                    $definition["type"] = Column::TYPE_DATETIME;
                    $definition["size"] = 0;

                    break;

                /**
                 * DATETIME
                 */
                case str_contains($columnType, "date"):
                    $definition["type"] = Column::TYPE_DATE;
                    $definition["size"] = 0;

                    break;

                /**
                 * NUMERIC -> DECIMAL - This will need to be a string so as not
                 * to lose the decimals
                 */
                case str_contains($columnType, "decimal"):
                case str_contains($columnType, "numeric"):
                    $definition["type"]      = Column::TYPE_DECIMAL;
                    $definition["size"]      = $numericSize;
                    $definition["isNumeric"] = true;
                    $definition["bindType"]  = Column::BIND_PARAM_DECIMAL;

                    break;

                /**
                 * DOUBLE
                 */
                case str_contains($columnType, "double precision"):
                    $definition["type"]      = Column::TYPE_DOUBLE;
                    $definition["isNumeric"] = true;
                    $definition["size"]      = $numericSize;
                    $definition["bindType"]  = Column::BIND_PARAM_DECIMAL;

                    break;

                /**
                 * FLOAT
                 */
                case str_contains($columnType, "float"):
                case str_contains($columnType, "real"):
                    $definition["type"]      = Column::TYPE_FLOAT;
                    $definition["isNumeric"] = true;
                    $definition["size"]      = $numericSize;
                    $definition["bindType"]  = Column::BIND_PARAM_DECIMAL;

                    break;

                /**
                 * LONGBLOB
                 */
                case str_contains($columnType, "longblob"):
                    $definition["type"] = Column::TYPE_LONGBLOB;

                    break;

                /**
                 * TINYBLOB
                 */
                case str_contains($columnType, "tinyblob"):
                    $definition["type"] = Column::TYPE_TINYBLOB;

                    break;

                /**
                 * BLOB
                 */
                case str_contains($columnType, "blob"):
                    $definition["type"] = Column::TYPE_BLOB;

                    break;

                /**
                 * TIMESTAMP
                 */
                case str_contains($columnType, "timestamp"):
                    $definition["type"] = Column::TYPE_TIMESTAMP;

                    break;

                /**
                 * TIME
                 */
                case str_contains($columnType, "time"):
                    $definition["type"] = Column::TYPE_TIME;

                    break;

                /**
                 * JSONB
                 */
                case str_contains($columnType, "jsonb"):
                    $definition["type"] = Column::TYPE_JSONB;

                    break;

                /**
                 * JSON
                 */
                case str_contains($columnType, "json"):
                    $definition["type"] = Column::TYPE_JSON;

                    break;

                /**
                 * LONGTEXT
                 */
                case str_contains($columnType, "longtext"):
                    $definition["type"] = Column::TYPE_LONGTEXT;

                    break;

                /**
                 * MEDIUMTEXT
                 */
                case str_contains($columnType, "mediumtext"):
                    $definition["type"] = Column::TYPE_MEDIUMTEXT;

                    break;

                /**
                 * TINYTEXT
                 */
                case str_contains($columnType, "tinytext"):
                    $definition["type"] = Column::TYPE_TINYTEXT;

                    break;

                /**
                 * TEXT, MEDIUMBLOB
                 */
                case str_contains($columnType, "mediumblob"):
                case str_contains($columnType, "text"):
                    $definition["type"] = Column::TYPE_TEXT;

                    break;

                /**
                 * VARCHAR
                 */
                case str_contains($columnType, "varying"):
                case str_contains($columnType, "varchar"):
                    $definition["type"] = Column::TYPE_VARCHAR;
                    $definition["size"] = $charSize;

                    break;

                /**
                 * CHAR
                 */
                case str_contains($columnType, "char"):
                    $definition["type"] = Column::TYPE_CHAR;
                    $definition["size"] = $charSize;

                    break;

                /**
                 * UUID
                 */
                case str_contains($columnType, "uuid"):
                    $definition["type"] = Column::TYPE_CHAR;
                    $definition["size"] = 36;

                    break;

                /**
                 * Default
                 */
                default:
                    $definition["type"] = Column::TYPE_VARCHAR;

                    break;
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
            if ($field[6] == "PRI") {
                $definition["primary"] = true;
            }

            /**
             * Check if the column allows null values
             */
            if ($field[5] == "YES") {
                $definition["notNull"] = false;
            }

            /**
             * Check if the column is auto increment
             */
            if ($field[7] == "auto_increment") {
                $definition["autoIncrement"] = true;
            }

            /**
             * Check if the column has default values
             */
            if (null !== $field[9]) {
                $definition["default"] = preg_replace(
                    "/^('|'?::[[:alnum:][:space:]]+)$/",
                    "",
                    $field[9]
                );

                if (0 === strcasecmp($definition["default"], "null")) {
                    $definition["default"] = null;
                }
            }

            /**
             * Check if the column has comment
             */
            if (null !== $field[10]) {
                $definition["comment"] = $field[10];
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
            $referencedColumns = $references[$constraintName]["referencedColumns"] ?? [];
            $referencedSchema  = $references[$constraintName]["referencedSchema"] ?? $reference[3];
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
     * Returns the default identity value to be inserted in an identity column
     *
     *```php
     * // Inserting a new robot with a valid default value for the column 'id'
     * $success = $connection->insert(
     *     "robots",
     *     [
     *         $connection->getDefaultIdValue(),
     *         "Astro Boy",
     *         1952,
     *     ],
     *     [
     *         "id",
     *         "name",
     *         "year",
     *     ]
     * );
     *```
     *
     * @return RawValue
     */
    public function getDefaultIdValue(): RawValue
    {
        return new RawValue("DEFAULT");
    }

    /**
     * Modifies a table column based on a $definition
     *
     * @param string               $tableName
     * @param string               $schemaName
     * @param ColumnInterface      $column
     * @param ColumnInterface|null $currentColumn
     *
     * @return bool
     * @throws EventsException
     * @throws Exception
     */
    public function modifyColumn(
        string $tableName,
        string $schemaName,
        ColumnInterface $column,
        ColumnInterface | null $currentColumn = null
    ): bool {
        $sql = $this->dialect->modifyColumn(
            $tableName,
            $schemaName,
            $column,
            $currentColumn
        );

        $queries = explode(";", $sql);
        if (count($queries) > 1) {
            try {
                $this->begin();
                foreach ($queries as $query) {
                    if (empty($query)) {
                        continue;
                    }

                    $this->query($query . ";");
                }

                return $this->commit();
            } catch (Throwable $exception) {
                $this->rollback();

                throw $exception;
            }
        } else {
            return (!empty($sql)) || $this->execute($queries[0] . ";");
        }
    }

    /**
     * Check whether the database system requires a sequence to produce
     * auto-numeric values
     *
     * @return bool
     */
    public function supportSequences(): bool
    {
        return true;
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
