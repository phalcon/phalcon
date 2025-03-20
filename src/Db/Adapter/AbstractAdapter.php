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

namespace Phalcon\Db\Adapter;

use Phalcon\Db\ColumnInterface;
use Phalcon\Db\DialectInterface;
use Phalcon\Db\Enum;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\IndexInterface;
use Phalcon\Db\RawValue;
use Phalcon\Db\Reference;
use Phalcon\Db\ReferenceInterface;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\Traits\EventsAwareTrait;

use function array_keys;
use function array_merge;
use function array_values;
use function explode;
use function is_array;
use function is_object;
use function is_string;
use function strpos;

/**
 * Base class for Phalcon\Db\Adapter adapters
 */
abstract class AbstractAdapter implements AdapterInterface, EventsAwareInterface
{
    use EventsAwareTrait;

    /**
     * Connection ID
     *
     * @var int
     */
    protected static int $connectionConsecutive = 0;

    /**
     * Active connection ID
     *
     * @var int
     */
    protected int $connectionId;

    /**
     * Descriptor used to connect to a database
     *
     * @var array
     */
    protected array $descriptor = [];

    /**
     * Dialect instance
     *
     * @var DialectInterface
     */
    protected DialectInterface $dialect;

    /**
     * Name of the dialect used
     *
     * @var string
     */
    protected string $dialectType;

    /**
     * The real SQL statement - what was executed
     *
     * @var string
     */
    protected string $realSqlStatement;

    /**
     * Active SQL Bind Types
     *
     * @var array
     */
    protected array $sqlBindTypes = [];

    /**
     * Active SQL Statement
     *
     * @var string
     */
    protected string $sqlStatement;

    /**
     * Active SQL bound parameter variables
     *
     * @var array
     */
    protected array $sqlVariables = [];

    /**
     * Current transaction level
     *
     * @var int
     */
    protected int $transactionLevel = 0;

    /**
     * Whether the database supports transactions with save points
     *
     * @var bool
     */
    protected bool $transactionsWithSavepoints = false;

    /**
     * Type of database system the adapter is used for
     *
     * @var string
     */
    protected string $type;

    /**
     * Phalcon\Db\Adapter constructor
     *
     * @param array $descriptor = [
     *                          'host'         => 'localhost',
     *                          'port'         => '3306',
     *                          'dbname'       => 'blog',
     *                          'username'     => 'sigma'
     *                          'password'     => 'secret',
     *                          'dialectClass' => null,
     *                          'options'      => [],
     *                          'dsn'          => null,
     *                          'charset'      => 'utf8mb4'
     *                          ]
     */
    public function __construct(array $descriptor)
    {
        $this->connectionId = self::$connectionConsecutive;
        self::$connectionConsecutive++;

        /**
         * Dialect class can override the default dialect
         */
        $dialectClass = "Phalcon\Db\Dialect\\" . ucfirst($this->dialectType);
        if (isset($descriptor["dialectClass"])) {
            $dialectClass = $descriptor["dialectClass"];
        }

        /**
         * Create the instance only if the dialect is a string
         */
        if (is_string($dialectClass)) {
            $this->dialect = new $dialectClass();
        } elseif (is_object($dialectClass)) {
            $this->dialect = $dialectClass;
        }

        $this->descriptor = $descriptor;
    }

    /**
     * Adds a column to a table
     *
     * @param string          $tableName
     * @param string          $schemaName
     * @param ColumnInterface $column
     *
     * @return bool
     */
    public function addColumn(
        string $tableName,
        string $schemaName,
        ColumnInterface $column
    ): bool {
        return $this->execute(
            $this->dialect->addColumn(
                $tableName,
                $schemaName,
                $column
            )
        );
    }

    /**
     * Adds a foreign key to a table
     *
     * @param string             $tableName
     * @param string             $schemaName
     * @param ReferenceInterface $reference
     *
     * @return bool
     */
    public function addForeignKey(
        string $tableName,
        string $schemaName,
        ReferenceInterface $reference
    ): bool {
        return $this->execute(
            $this->dialect->addForeignKey(
                $tableName,
                $schemaName,
                $reference
            )
        );
    }

    /**
     * Adds an index to a table
     *
     * @param string         $tableName
     * @param string         $schemaName
     * @param IndexInterface $index
     *
     * @return bool
     */
    public function addIndex(
        string $tableName,
        string $schemaName,
        IndexInterface $index
    ): bool {
        return $this->execute(
            $this->dialect->addIndex(
                $tableName,
                $schemaName,
                $index
            )
        );
    }

    /**
     * Adds a primary key to a table
     *
     * @param string         $tableName
     * @param string         $schemaName
     * @param IndexInterface $index
     *
     * @return bool
     */
    public function addPrimaryKey(
        string $tableName,
        string $schemaName,
        IndexInterface $index
    ): bool {
        return $this->execute(
            $this->dialect->addPrimaryKey(
                $tableName,
                $schemaName,
                $index
            )
        );
    }

    /**
     * Creates a new savepoint
     *
     * @param string $name
     *
     * @return bool
     * @throws Exception
     */
    public function createSavepoint(string $name): bool
    {
        $this->checkSavepoints();

        return $this->execute($this->dialect->createSavepoint($name));
    }

    /**
     * Creates a table
     *
     * @param string $tableName
     * @param string $schemaName
     * @param array  $definition
     *
     * @return bool
     * @throws Exception
     */
    public function createTable(
        string $tableName,
        string $schemaName,
        array $definition
    ): bool {
        $columns = $definition["columns"] ?? [];
        if (empty($columns)) {
            throw new Exception("The table must contain at least one column");
        }

        return $this->execute(
            $this->dialect->createTable(
                $tableName,
                $schemaName,
                $definition
            )
        );
    }

    /**
     * Creates a view
     *
     * @param string      $viewName
     * @param array       $definition
     * @param string|null $schemaName
     *
     * @return bool
     * @throws Exception
     */
    public function createView(
        string $viewName,
        array $definition,
        string | null $schemaName = null
    ): bool {
        if (!isset($definition["sql"])) {
            throw new Exception("The table must contain at least one column");
        }

        return $this->execute(
            $this->dialect->createView(
                $viewName,
                $definition,
                $schemaName
            )
        );
    }

    /**
     * Deletes data from a table using custom RBDM SQL syntax
     *
     * ```php
     * // Deleting existing robot
     * $success = $connection->delete(
     *     "robots",
     *     "id = 101"
     * );
     *
     * // Next SQL sentence is generated
     * DELETE FROM `robots` WHERE `id` = 101
     * ```
     *
     * @param array|string $tableName
     * @param string|null  $whereCondition
     * @param array        $placeholders
     * @param array        $dataTypes
     *
     * @return bool
     */
    public function delete(
        array | string $tableName,
        string | null $whereCondition = null,
        array $placeholders = [],
        array $dataTypes = []
    ): bool {
        $sql = "DELETE FROM " . $this->escapeIdentifier($tableName);

        if (!empty($whereCondition)) {
            $sql .= " WHERE " . $whereCondition;
        }

        /**
         * Perform the update via PDO::execute
         */
        return $this->execute($sql, $placeholders, $dataTypes);
    }

    /**
     * Lists table indexes
     *
     *```php
     * print_r(
     *     $connection->describeIndexes("robots_parts")
     * );
     *```
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
            $this->dialect->describeIndexes($tableName, $schemaName),
            Enum::FETCH_NUM
        );

        foreach ($records as $index) {
            $keyName           = $index[2];
            $columns           = $indexes[$keyName] ?? [];
            $columns[]         = $index[4];
            $indexes[$keyName] = $columns;
        }

        $indexObjects = [];
        foreach ($indexes as $name => $indexColumns) {
            /**
             * Every index is abstracted using a Phalcon\Db\Index instance
             */
            $indexObjects[$name] = new Index($name, $indexColumns);
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
            $referencedSchema  = $references[$constraintName]["referencedSchema"] ?? $reference[3];
            $referencedTable   = $references[$constraintName]["referencedTable"] ?? $reference[4];
            $columns           = $references[$constraintName]["columns"] ?? [];
            $referencedColumns = $references[$constraintName]["referencedColumns"] ?? [];

            $columns[]           = $reference[1];
            $referencedColumns[] = $reference[5];

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
     * Drops a column from a table
     *
     * @param string $tableName
     * @param string $schemaName
     * @param string $columnName
     *
     * @return bool
     */
    public function dropColumn(
        string $tableName,
        string $schemaName,
        string $columnName
    ): bool {
        return $this->execute(
            $this->dialect->dropColumn(
                $tableName,
                $schemaName,
                $columnName
            )
        );
    }

    /**
     * Drops a foreign key from a table
     *
     * @param string $tableName
     * @param string $schemaName
     * @param string $referenceName
     *
     * @return bool
     */
    public function dropForeignKey(
        string $tableName,
        string $schemaName,
        string $referenceName
    ): bool {
        return $this->execute(
            $this->dialect->dropForeignKey(
                $tableName,
                $schemaName,
                $referenceName
            )
        );
    }

    /**
     * Drop an index from a table
     *
     * @param string $tableName
     * @param string $schemaName
     * @param string $indexName
     *
     * @return bool
     */
    public function dropIndex(
        string $tableName,
        string $schemaName,
        string $indexName
    ): bool {
        return $this->execute(
            $this->dialect->dropIndex(
                $tableName,
                $schemaName,
                $indexName
            )
        );
    }

    /**
     * Drops a table's primary key
     *
     * @param string $tableName
     * @param string $schemaName
     *
     * @return bool
     */
    public function dropPrimaryKey(string $tableName, string $schemaName): bool
    {
        return $this->execute(
            $this->dialect->dropPrimaryKey(
                $tableName,
                $schemaName
            )
        );
    }

    /**
     * Drops a table from a schema/database
     *
     * @param string      $tableName
     * @param string|null $schemaName
     * @param bool        $ifExists
     *
     * @return bool
     */
    public function dropTable(
        string $tableName,
        string | null $schemaName = null,
        bool $ifExists = true
    ): bool {
        return $this->execute(
            $this->dialect->dropTable(
                $tableName,
                $schemaName,
                $ifExists
            )
        );
    }

    /**
     * Drops a view
     *
     * @param string $viewName
     * @param string $schemaName
     * @param bool   $ifExists
     *
     * @return bool
     */
    public function dropView(
        string $viewName,
        string | null $schemaName = null,
        bool $ifExists = true
    ): bool {
        return $this->execute(
            $this->dialect->dropView(
                $viewName,
                $schemaName,
                $ifExists
            )
        );
    }

    /**
     * Escapes a column/table/schema name
     *
     *```php
     * $escapedTable = $connection->escapeIdentifier(
     *     "robots"
     * );
     *
     * $escapedTable = $connection->escapeIdentifier(
     *     [
     *         "store",
     *         "robots",
     *     ]
     * );
     *```
     *
     * @param array|string $identifier
     *
     * @return string
     */
    public function escapeIdentifier(array | string $identifier): string
    {
        if (is_array($identifier)) {
            return $this->dialect->escape($identifier[0])
                . "." . $this->dialect->escape($identifier[1]);
        }

        return $this->dialect->escape($identifier);
    }

    /**
     * Dumps the complete result of a query into an array
     *
     *```php
     * // Getting all robots with associative indexes only
     * $robots = $connection->fetchAll(
     *     "SELECT * FROM robots",
     *     \Phalcon\Db\Enum::FETCH_ASSOC
     * );
     *
     * foreach ($robots as $robot) {
     *     print_r($robot);
     * }
     *
     *  // Getting all robots that contains word "robot" withing the name
     * $robots = $connection->fetchAll(
     *     "SELECT * FROM robots WHERE name LIKE :name",
     *     \Phalcon\Db\Enum::FETCH_ASSOC,
     *     [
     *         "name" => "%robot%",
     *     ]
     * );
     * foreach($robots as $robot) {
     *     print_r($robot);
     * }
     *```
     *
     * @param string $sqlQuery
     * @param int    $fetchMode
     * @param array  $bindParams
     * @param array  $bindTypes
     *
     * @return array
     */
    public function fetchAll(
        string $sqlQuery,
        int $fetchMode = Enum::FETCH_ASSOC,
        array $bindParams = [],
        array $bindTypes = []
    ): array {
        $result = $this->query($sqlQuery, $bindParams, $bindTypes);

        if (!is_object($result)) {
            return [];
        }

        if ($fetchMode === Enum::FETCH_COLUMN) {
            return $result->fetchAll(Enum::FETCH_COLUMN);
        }

        $result->setFetchMode($fetchMode);

        return $result->fetchAll();
    }

    /**
     * Returns the n'th field of first row in a SQL query result
     *
     *```php
     * // Getting count of robots
     * $robotsCount = $connection->fetchColumn("SELECT count(*) FROM robots");
     * print_r($robotsCount);
     *
     * // Getting name of last edited robot
     * $robot = $connection->fetchColumn(
     *     "SELECT id, name FROM robots ORDER BY modified DESC",
     *     1
     * );
     * print_r($robot);
     *```
     */
    public function fetchColumn(
        string $sqlQuery,
        array $placeholders = [],
        int | string $column = 0
    ): string | bool {
        $row = $this->fetchOne($sqlQuery, Enum::FETCH_BOTH, $placeholders);

        return $row[$column] ?? false;
    }

    /**
     * Returns the first row in a SQL query result
     *
     *```php
     * // Getting first robot
     * $robot = $connection->fetchOne("SELECT * FROM robots");
     * print_r($robot);
     *
     * // Getting first robot with associative indexes only
     * $robot = $connection->fetchOne(
     *     "SELECT * FROM robots",
     *     \Phalcon\Db\Enum::FETCH_ASSOC
     * );
     * print_r($robot);
     *```
     *
     * @param string $sqlQuery
     * @param int    $fetchMode
     * @param array  $bindParams
     * @param array  $bindTypes
     *
     * @return array
     */
    public function fetchOne(
        string $sqlQuery,
        int $fetchMode = Enum::FETCH_ASSOC,
        array $bindParams = [],
        array $bindTypes = []
    ): array {
        $result = $this->query($sqlQuery, $bindParams, $bindTypes);

        if (!is_object($result)) {
            return [];
        }

        $result->setFetchMode($fetchMode);

        return $result->fetch();
    }

    /**
     * Returns a SQL modified with a FOR UPDATE clause
     *
     * @param string $sqlQuery
     *
     * @return string
     */
    public function forUpdate(string $sqlQuery): string
    {
        return $this->dialect->forUpdate($sqlQuery);
    }

    /**
     * Returns the SQL column definition from a column
     *
     * @param ColumnInterface $column
     *
     * @return string
     */
    public function getColumnDefinition(ColumnInterface $column): string
    {
        return $this->dialect->getColumnDefinition($column);
    }

    /**
     * Gets a list of columns
     *
     * @param array $columnList
     *
     * @return string
     */
    public function getColumnList(array $columnList): string
    {
        return $this->dialect->getColumnList($columnList);
    }

    /**
     * Gets the active connection unique identifier
     *
     * @return string
     */
    public function getConnectionId(): string
    {
        return (string)$this->connectionId;
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
        return new RawValue("null");
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
     *         $connection->getDefaultValue()
     *     ],
     *     [
     *         "name",
     *         "year",
     *     ]
     * );
     *```
     *
     * @return RawValue
     * @todo Return NULL if this is not supported by the adapter
     *
     */
    public function getDefaultValue(): RawValue
    {
        return new RawValue("DEFAULT");
    }

    /**
     * Return descriptor used to connect to the active database
     *
     * @return array
     */
    public function getDescriptor(): array
    {
        return $this->descriptor;
    }

    /**
     * Returns internal dialect instance
     *
     * @return DialectInterface
     */
    public function getDialect(): DialectInterface
    {
        return $this->dialect;
    }

    /**
     * Name of the dialect used
     *
     * @return string
     */
    public function getDialectType(): string
    {
        return $this->dialectType;
    }

    /**
     * Returns the savepoint name to use for nested transactions
     *
     * @return string
     */
    public function getNestedTransactionSavepointName(): string
    {
        return "PHALCON_SAVEPOINT_" . $this->transactionLevel;
    }

    /**
     * Active SQL statement in the object without replace bound parameters
     *
     * @return string
     */
    public function getRealSQLStatement(): string
    {
        return $this->realSqlStatement;
    }

    /**
     * Active SQL statement in the object
     *
     * @return array
     */
    public function getSQLBindTypes(): array
    {
        return $this->sqlBindTypes;
    }

    /**
     * Active SQL statement in the object
     *
     * @return string
     */
    public function getSQLStatement(): string
    {
        return $this->sqlStatement;
    }

    /**
     * Active SQL variables in the object
     *
     * @return array
     */
    public function getSQLVariables(): array
    {
        return $this->sqlVariables;
    }

    /**
     * Type of database system the adapter is used for
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Inserts data into a table using custom RDBMS SQL syntax
     *
     * ```php
     * // Inserting a new robot
     * $success = $connection->insert(
     *     "robots",
     *     ["Astro Boy", 1952],
     *     ["name", "year"]
     * );
     *
     * // Next SQL sentence is sent to the database system
     * INSERT INTO `robots` (`name`, `year`) VALUES ("Astro boy", 1952);
     * ```
     *
     * @param string $tableName
     * @param array  $values
     * @param array  $fields
     * @param array  $dataTypes
     *
     * @return bool
     * @throws Exception
     */
    public function insert(
        string $tableName,
        array $values,
        array $fields = [],
        array $dataTypes = []
    ): bool {
        /**
         * A valid array with more than one element is required
         */
        if (empty($values)) {
            throw new Exception(
                "Unable to insert into " . $tableName . " without data"
            );
        }

        $placeholders  = [];
        $insertValues  = [];
        $bindDataTypes = [];

        /**
         * Objects are cast using __toString, null values are converted to
         * string "null", everything else is passed as "?"
         */
        foreach ($values as $position => $value) {
            if ($value instanceof RawValue) {
                $placeholders[] = (string)$value;
            } else {
                if (is_object($value)) {
                    $value = (string)$value;
                }

                if (null === $value) {
                    $placeholders[] = "null";
                } else {
                    $placeholders[] = "?";
                    $insertValues[] = $value;

                    if (!empty($dataTypes)) {
                        if (!isset($dataTypes[$position])) {
                            throw new Exception(
                                "Incomplete number of bind types"
                            );
                        }

                        $bindDataTypes[] = $dataTypes[$position];
                    }
                }
            }
        }

        if (strpos($tableName, ".") > 0) {
            $tableName = explode(".", $tableName);
        }

        $escapedTable = $this->escapeIdentifier($tableName);

        /**
         * Build the final SQL INSERT statement
         */
        $joinedValues = implode(", ", $placeholders);

        $insertSql = "INSERT INTO " . $escapedTable . " VALUES (" . $joinedValues . ")";

        if (!empty($fields)) {
            $escapedFields = [];
            foreach ($fields as $field) {
                $escapedFields[] = $this->escapeIdentifier($field);
            }

            $insertSql = "INSERT INTO "
                . $escapedTable
                . " (" . implode(", ", $escapedFields)
                . ") VALUES (" . $joinedValues . ")";
        }

        /**
         * Perform the execution via PDO::execute
         */
        if (!empty($bindDataTypes)) {
            return $this->execute($insertSql, $insertValues, $bindDataTypes);
        }

        return $this->execute($insertSql, $insertValues);
    }

    /**
     * Inserts data into a table using custom RBDM SQL syntax
     *
     * ```php
     * // Inserting a new robot
     * $success = $connection->insertAsDict(
     *     "robots",
     *     [
     *         "name" => "Astro Boy",
     *         "year" => 1952,
     *     ]
     * );
     *
     * // Next SQL sentence is sent to the database system
     * INSERT INTO `robots` (`name`, `year`) VALUES ("Astro boy", 1952);
     * ```
     *
     * @param string $tableName
     * @param array  $data
     * @param array  $dataTypes
     *
     * @return bool
     * @throws Exception
     */
    public function insertAsDict(
        string $tableName,
        array $data,
        array $dataTypes = []
    ): bool {
        if (empty($data)) {
            return false;
        }


        return $this->insert(
            $tableName,
            array_keys($data),
            array_values($data),
            $dataTypes
        );
    }

    /**
     * Returns if nested transactions should use savepoints
     *
     * @return bool
     */
    public function isNestedTransactionsWithSavepoints(): bool
    {
        return $this->transactionsWithSavepoints;
    }

    /**
     * Appends a LIMIT clause to $sqlQuery argument
     *
     * ```php
     * echo $connection->limit("SELECT * FROM robots", 5);
     * ```
     *
     * @param string    $sqlQuery
     * @param array|int $number
     *
     * @return string
     */
    public function limit(string $sqlQuery, array | int $number): string
    {
        return $this->dialect->limit($sqlQuery, $number);
    }

    /**
     * List all tables on a database
     *
     *```php
     * print_r(
     *     $connection->listTables("blog")
     * );
     *```
     *
     * @param string $schemaName
     *
     * @return array
     * @todo optimize this
     */
    public function listTables(string | null $schemaName = null): array
    {
        $allTables  = [];
        $tableNames = $this->fetchAll(
            $this->dialect->listTables($schemaName),
            Enum::FETCH_NUM
        );

        foreach ($tableNames as $tableName) {
            $allTables[] = $tableName[0];
        }

        return $allTables;
    }

    /**
     * List all views on a database
     *
     *```php
     * print_r(
     *     $connection->listViews("blog")
     * );
     *```
     *
     * @param string $schemaName
     *
     * @return array
     */
    public function listViews(string | null $schemaName = null): array
    {
        $allTables  = [];
        $tableNames = $this->fetchAll(
            $this->dialect->listViews($schemaName),
            Enum::FETCH_NUM
        );

        foreach ($tableNames as $tableName) {
            $allTables[] = $tableName[0];
        }

        return $allTables;
    }

    /**
     * Modifies a table column based on a definition
     *
     * @param string               $tableName
     * @param string               $schemaName
     * @param ColumnInterface      $column
     * @param ColumnInterface|null $currentColumn
     *
     * @return bool
     */
    public function modifyColumn(
        string $tableName,
        string $schemaName,
        ColumnInterface $column,
        ColumnInterface | null $currentColumn = null
    ): bool {
        return $this->execute(
            $this->dialect->modifyColumn(
                $tableName,
                $schemaName,
                $column,
                $currentColumn
            )
        );
    }

    /**
     * Releases given savepoint
     *
     * @param string $name
     *
     * @return bool
     * @throws Exception
     */
    public function releaseSavepoint(string $name): bool
    {
        $this->checkSavepoints();

        if (true !== $this->dialect->supportsReleaseSavePoints()) {
            return false;
        }

        return $this->execute($this->dialect->releaseSavepoint($name));
    }

    /**
     * Rollbacks given savepoint
     *
     * @param string $name
     *
     * @return bool
     * @throws Exception
     */
    public function rollbackSavepoint(string $name): bool
    {
        $this->checkSavepoints();

        return $this->execute($this->dialect->rollbackSavepoint($name));
    }

    /**
     * Sets the dialect used to produce the SQL
     *
     * @param DialectInterface $dialect
     *
     * @return void
     */
    public function setDialect(DialectInterface $dialect): void
    {
        $this->dialect = $dialect;
    }

    /**
     * Set if nested transactions should use savepoints
     *
     * @param bool $flag
     *
     * @return AdapterInterface
     * @throws Exception
     */
    public function setNestedTransactionsWithSavepoints(
        bool $flag
    ): AdapterInterface {
        if ($this->transactionLevel > 0) {
            throw new Exception(
                "Nested transaction with savepoints behavior "
                . "cannot be changed while a transaction is open"
            );
        }

        $this->checkSavepoints();

        $this->transactionsWithSavepoints = $flag;

        return $this;
    }

    /**
     * Returns a SQL modified with a LOCK IN SHARE MODE clause
     *
     * @param string $sqlQuery
     *
     * @return string
     */
    public function sharedLock(string $sqlQuery): string
    {
        return $this->dialect->sharedLock($sqlQuery);
    }

    /**
     * Check whether the database system requires a sequence to produce
     * auto-numeric values
     *
     * @return bool
     */
    public function supportSequences(): bool
    {
        return false;
    }

    /**
     * Check whether the database system support the DEFAULT
     * keyword (SQLite does not support it)
     *
     * @deprecated Will re removed in the next version
     */
    public function supportsDefaultValue(): bool
    {
        return true;
    }

    /**
     * Generates SQL checking for the existence of a schema.table
     *
     *```php
     * var_dump(
     *     $connection->tableExists("blog", "posts")
     * );
     *```
     *
     * @param string $tableName
     * @param string $schemaName
     *
     * @return bool
     */
    public function tableExists(
        string $tableName,
        string | null $schemaName = null
    ): bool {
        $exists = $this->dialect->tableExists($tableName, $schemaName);

        return $this->fetchOne($exists, Enum::FETCH_NUM)[0] > 0;
    }

    /**
     * Gets creation options from a table
     *
     *```php
     * print_r(
     *     $connection->tableOptions("robots")
     * );
     *```
     *
     * @param string $tableName
     * @param string $schemaName
     *
     * @return array
     */
    public function tableOptions(
        string $tableName,
        string | null $schemaName = null
    ): array {
        $options = $this->dialect->tableOptions($tableName, $schemaName);

        if (empty($options)) {
            return [];
        }

        return $this->fetchAll($options)[0];
    }

    /**
     * Updates data on a table using custom RBDM SQL syntax
     *
     * ```php
     * // Updating existing robot
     * $success = $connection->update(
     *     "robots",
     *     ["name"],
     *     ["New Astro Boy"],
     *     "id = 101"
     * );
     *
     * // Next SQL sentence is sent to the database system
     * UPDATE `robots` SET `name` = "Astro boy" WHERE id = 101
     *
     * // Updating existing robot with array condition and $dataTypes
     * $success = $connection->update(
     *     "robots",
     *     ["name"],
     *     ["New Astro Boy"],
     *     [
     *         "conditions" => "id = ?",
     *         "bind"       => [$some_unsafe_id],
     *         "bindTypes"  => [PDO::PARAM_INT], // use only if you use
     *         $dataTypes param
     *     ],
     *     [
     *         PDO::PARAM_STR
     *     ]
     * );
     *
     * ```
     *
     * Warning! If $whereCondition is string, it is not escaped.
     *
     * @param string       $tableName
     * @param array        $fields
     * @param array        $values
     * @param array|string $whereCondition
     * @param array        $dataTypes
     *
     * @return bool
     * @throws Exception
     */
    public function update(
        string $tableName,
        array $fields,
        array $values,
        array | string $whereCondition = [],
        array $dataTypes = []
    ): bool {
        $placeholders  = [];
        $updateValues  = [];
        $bindDataTypes = [];

        /**
         * Objects are cast using __toString, null values are converted to
         * string 'null', everything else is passed as '?'
         */
        foreach ($values as $position => $value) {
            if (!isset($fields[$position])) {
                throw new Exception(
                    "The number of values in the update is not the same as fields"
                );
            }

            $field        = $fields[$position];
            $escapedField = $this->escapeIdentifier($field);

            if ($value instanceof RawValue) {
                $placeholders[] = $escapedField . " = " . $value;
            } else {
                if (is_object($value)) {
                    $value = (string)$value;
                }

                if (null === $value) {
                    $placeholders[] = $escapedField . " = null";
                } else {
                    $updateValues[] = $value;

                    if (!empty($dataTypes)) {
                        if (!isset($dataTypes[$position])) {
                            throw new Exception(
                                "Incomplete number of bind types"
                            );
                        }

                        $bindDataTypes[] = $dataTypes[$position];
                    }

                    $placeholders[] = $escapedField . " = ?";
                }
            }
        }

        /**
         * Check if we got table and schema and escape it accordingly
         */
        if (strpos($tableName, ".") > 0) {
            $tableName = explode(".", $tableName);
        }

        $escapedTable = $this->escapeIdentifier($tableName);
        $setClause    = implode(", ", $placeholders);

        $updateSql = "UPDATE " . $escapedTable . " SET " . $setClause;
        if (!empty($whereCondition)) {
            $updateSql .= " WHERE ";

            /**
             * String conditions are simply appended to the SQL
             */
            if (is_string($whereCondition)) {
                $updateSql .= $whereCondition;
            } else {
                /**
                 * Array conditions may have bound params and bound types
                 *
                 * If an index 'conditions' is present it contains string where
                 * conditions that are appended to the UPDATE SQL
                 */
                if (isset($whereCondition["conditions"])) {
                    $updateSql .= $whereCondition["conditions"];
                }

                /**
                 * Bound parameters are arbitrary values that are passed
                 * separately
                 */
                if (isset($whereCondition["bind"])) {
                    $condition    = $whereCondition["bind"];
                    $condition    = is_array($condition) ? $condition : [$condition];
                    $updateValues = array_merge($updateValues, $condition);
                }

                /**
                 * Bind types is how the bound parameters must be cast before
                 * be sent to the database system
                 */
                if (isset($whereCondition["bindTypes"])) {
                    $condition     = $whereCondition["bindTypes"];
                    $condition     = is_array($condition) ? $condition : [$condition];
                    $bindDataTypes = array_merge($bindDataTypes, $condition);
                }
            }
        }

        /**
         * Perform the update via PDO::execute
         */
        /**
         * Perform the execution via PDO::execute
         */
        if (!empty($bindDataTypes)) {
            return $this->execute($updateSql, $updateValues, $bindDataTypes);
        }

        return $this->execute($updateSql, $updateValues);
    }

    /**
     * Updates data on a table using custom RBDM SQL syntax
     * Another, more convenient syntax
     *
     * ```php
     * // Updating existing robot
     * $success = $connection->updateAsDict(
     *     "robots",
     *     [
     *         "name" => "New Astro Boy",
     *     ],
     *     "id = 101"
     * );
     *
     * // Next SQL sentence is sent to the database system
     * UPDATE `robots` SET `name` = "Astro boy" WHERE id = 101
     * ```
     *
     * @param string       $tableName
     * @param array        $data
     * @param array|string $whereCondition
     * @param array        $dataTypes
     *
     * @return bool
     * @throws Exception
     */
    public function updateAsDict(
        string $tableName,
        array $data,
        array | string $whereCondition = [],
        array $dataTypes = []
    ): bool {
        if (empty($data)) {
            return false;
        }

        return $this->update(
            $tableName,
            array_keys($data),
            array_values($data),
            $whereCondition,
            $dataTypes
        );
    }

    /**
     * Check whether the database system requires an explicit value for identity
     * columns
     *
     * @return bool
     */
    public function useExplicitIdValue(): bool
    {
        return false;
    }

    /**
     * Generates SQL checking for the existence of a schema.view
     *
     *```php
     * var_dump(
     *     $connection->viewExists("active_users", "posts")
     * );
     *```
     *
     * @param string $viewName
     * @param string $schemaName
     *
     * @return bool
     */
    public function viewExists(
        string $viewName,
        string | null $schemaName = null
    ): bool {
        $exists = $this->dialect->viewExists($viewName, $schemaName);

        return $this->fetchOne($exists, Enum::FETCH_NUM)[0] > 0;
    }

    /**
     * Check if savepoints are supported
     *
     * @return void
     * @throws Exception
     */
    protected function checkSavepoints(): void
    {
        if (true !== $this->dialect->supportsSavePoints()) {
            throw new Exception(
                "Savepoints are not supported by this database adapter"
            );
        }
    }
}
