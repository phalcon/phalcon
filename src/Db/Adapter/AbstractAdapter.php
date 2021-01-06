<?php

/**
 * This file is part of the Phalcon.
 *
 * (c) Phalcon Team <team@phalcon.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Phalcon\Db\Adapter;

use Phalcon\Db\DialectInterface;
use Phalcon\Db\ColumnInterface;
use Phalcon\Db\Enum;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\IndexInterface;
use Phalcon\Db\Reference;
use Phalcon\Db\ReferenceInterface;
use Phalcon\Db\RawValue;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\ManagerInterface;
use Phalcon\Reflect\Create;
/**
 * Base class for Phalcon\Db\Adapter adapters
 */
abstract class AbstractAdapter implements AdapterInterface, EventsAwareInterface
{
    /**
     * Connection ID Generator
     */
    protected static function genId() : \Generator {
                $i = 0;
                while(true) {
                    yield $i;
                    $i++;
                }
    }

    protected \Generator $generator;
    /**
     * Active connection ID
     *
     * @var long
     */
    protected int $connectionId;

    /**
     * Descriptor used to connect to a database
     */
    protected array $descriptor = [];

    /**
     * Dialect instance
     */
    protected DialectInterface $dialect;

    /**
     * Name of the dialect used
     *
     * @var string
     */
    protected string $dialectType; // { get };

    /**
     * Event Manager
     *
     * @var ManagerInterface
     */
    protected ?ManagerInterface $eventsManager = null;

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
    protected array $sqlBindTypes;

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
    protected array $sqlVariables; // { get };

    /**
     * Current transaction level
     */
    protected int $transactionLevel = 0;

    /**
     * Whether the database supports transactions with save points
     */
    protected bool $transactionsWithSavepoints = false;

    /**
     * Type of database system the adapter is used for
     *
     * @var string
     */
    protected string $type; // { get };

    /**
     * Phalcon\Db\Adapter constructor
     *
     * @param array descriptor = [
     *     'host' => 'localhost',
     *     'port' => '3306',
     *     'dbname' => 'blog',
     *     'username' => 'sigma'
     *     'password' => 'secret',
     *     'dialectClass' => null,
     *     'options' => [],
     *     'dsn' => null,
     *     'charset' => 'utf8mb4'
     * ]
     */
    public function __construct(?array $descriptor)
    {
        //var dialectClass, connectionId;
        $this->generator = self::genId();
        $this->generator->rewind();
        $this->connectionId = $this->generator->current();
        $this->generator->next();
        
        /**
         * Dialect class can override the default dialect
         */
        $dclass = ucfirst($this->dialectType);
        $dialectClass = $descriptor["dialectClass"] ?? "Phalcon\\Db\\Dialect\\" .  $dclass;
        if (is_string($dialectClass)) {
            $this->dialect = Create::instance($dialectClass);
        }
        else if (is_object($dialectClass)) {
            $this->dialect = $dialectClass;
        }

        $this->descriptor = $descriptor;
    }

    /**
     * Adds a column to a table
     */
    public function addColumn(string $tableName, string $schemaName,  ColumnInterface $column) : bool
    {
        return $this->{"execute"}(
            $this->dialect->addColumn(
                $tableName,
                $schemaName,
                $column
            )
        );
    }

    /**
     * Adds a foreign key to a table
     */
    public function addForeignKey(string $tableName, string $schemaName,  ReferenceInterface $reference) : bool
    {
        return $this->{"execute"}(
            $this->dialect->addForeignKey(
                $tableName,
                $schemaName,
                $reference
            )
        );
    }

    /**
     * Adds an index to a table
     */
    public function addIndex(string $tableName, string $schemaName,  IndexInterface $index): bool
    {
        return $this->{"execute"}(
            $this->dialect->addIndex(
                $tableName,
                $schemaName,
                $index
            )
        );
    }

    /**
     * Adds a primary key to a table
     */
    public function addPrimaryKey(string $tableName, string $schemaName,  IndexInterface $index) : bool
    {
        return $this->{"execute"}(
            $this->dialect->addPrimaryKey(
                $tableName,
                $schemaName,
                $index
            )
        );
    }

    /**
     * Creates a new savepoint
     */
    public function createSavepoint(string $name) : bool
    {

        $dialect = $this->dialect;

        if (!$dialect->supportsSavePoints()) {
            throw new Exception(
                "Savepoints are not supported by this database adapter."
            );
        }

        return $this->{"execute"}(
            $dialect->createSavepoint($name)
        );
    }

    /**
     * Creates a table
     */
    public function createTable(string $tableName, ?string $schemaName, array $definition) : bool
    {
        $columns = $definition["columns"] ?? null;

        if (empty($columns) ) {
            throw new Exception("The table must contain at least one column");
        }

        return $this->{"execute"}(
            $this->dialect->createTable(
                $tableName,
                $schemaName,
                $definition
            )
        );
    }

    /**
     * Creates a view
     */
    public function createView(string $viewName, array $definition, string $schemaName = null) : bool
    {
        if (!isset($definition["sql"])) {
            throw new Exception("The table must contain at least one column");
        }

        return $this->{"execute"}(
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
     */
    public function delete(string $table, string $whereCondition = null, ?array $placeholders = null, ?array $dataTypes = null): bool
    {
        //var sql, escapedTable;

        $escapedTable = $this->escapeIdentifier($table);

        $sql = "DELETE FROM " . $escapedTable;

        if (!empty($whereCondition)) {
            $sql .= " WHERE " . $whereCondition;
        }

        /**
         * Perform the update via PDO::execute
         */
        return $this->{"execute"}($sql, $placeholders, $dataTypes);
    }

    /**
     * Lists table indexes
     *
     *```php
     * print_r(
     *     $connection->describeIndexes("robots_parts")
     * );
     *```
     */
    public function describeIndexes(string $table, ?string $schema = null) : array
    {
        //var indexes, index, keyName, indexObjects, name, indexColumns, columns;

        $indexes = [];

        foreach ($this->fetchAll($this->dialect->describeIndexes($table, $schema), Enum::FETCH_NUM) as $index) {
            $keyName = $index[2];
            $columns = $indexes[$keyName] ?? [];
            $columns[] = $index[4];
            $indexes[$keyName] = $columns;
        }

        $indexObjects = [];

        foreach($indexes as $name => $indexColumns) {
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
     */
    public function describeReferences(string $table, ?string $schema = null) : array
    {
        //var references, reference, arrayReference, constraintName,
          //  referenceObjects, name, referencedSchema, referencedTable, columns,
            // referencedColumns;

        $references = [];

        foreach($this->fetchAll($this->dialect->describeReferences($table, $schema), Enum::FETCH_NUM) as $reference) {
            $constraintName = $reference[2];

            $constraint = $references[$constraintName] ?? null;
            if (empty($constraint)) {
                $referencedSchema = $reference[3];
                $referencedTable = $reference[4];
                $columns = [];
                $referencedColumns = [];
            } else {
                $referencedSchema = $constraint["referencedSchema"];
                $referencedTable = $constraint["referencedTable"];
                $columns = $constraint["columns"];
                $referencedColumns = $constraint["referencedColumns"];
            }

            $columns[] = $reference[1];
            $referencedColumns[] = $reference[5];

            $references[$constraintName] = [
                "referencedSchema"  =>  $referencedSchema,
                "referencedTable"   =>  $referencedTable,
                "columns"          =>  $columns,
                "referencedColumns" =>  $referencedColumns
            ];
        }

        $referenceObjects = [];

        foreach ($references as $name => $arrayReference) {
            $referenceObjects[$name] = new Reference(
                $name,
                [
                    "referencedSchema"  =>  $arrayReference["referencedSchema"],
                    "referencedTable"   =>  $arrayReference["referencedTable"],
                    "columns"           =>  $arrayReference["columns"],
                    "referencedColumns" =>  $arrayReference["referencedColumns"]
                ]
            );
        }

        return $referenceObjects;
    }

    /**
     * Drops a column from a table
     */
    public function dropColumn(string  $tableName, ?string $schemaName, string $columnName): bool
    {
        return $this->{"execute"}(
            $this->dialect->dropColumn(
                $tableName,
                $schemaName,
                $columnName
            )
        );
    }

    /**
     * Drops a foreign key from a table
     */
    public function dropForeignKey(string  $tableName, ?string $schemaName, string $referenceName) : bool
    {
        return $this->{"execute"}(
            $this->dialect->dropForeignKey(
                 $tableName,
                $schemaName,
                $referenceName
            )
        );
    }

    /**
     * Drop an index from a table
     */
    public function dropIndex(string $tableName, ?string $schemaName, string $indexName): bool
    {
        return $this->{"execute"}(
            $this->dialect->dropIndex(
                 $tableName,
                $schemaName,
                $indexName
            )
        );
    }

    /**
     * Drops a table's primary key
     */
    public function dropPrimaryKey(string $tableName, ?string $schemaName): bool
    {
        return $this->{"execute"}(
           $this->dialect->dropPrimaryKey(
                $tableName,
                $schemaName,
            )
        );
    }

    /**
     * Drops a table from a schema/database
     */
    public function dropTable(string $tableName, ?string $schemaName = null, bool $ifExists = true): bool
    {
        return $this->{"execute"}(
           $this->dialect->dropTable(
                $tableName,
                $schemaName,
                $ifExists
            )
        );
    }

    /**
     * Drops a view
     */
    public function dropView(string $viewName, ?string $schemaName = null, bool $ifExists = true): bool
    {
        return $this->{"execute"}(
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
     */
    public function escapeIdentifier(string|array $identifier): string
    {
        if (is_array($identifier)) {
            return $this->dialect->escape($identifier[0]) . "." .$this->dialect->escape($identifier[1]);
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
     */
    public function fetchAll(string $sqlQuery, int $fetchMode = Enum::FETCH_ASSOC, ?array $bindParams = null, ?array $bindTypes = null) : array
    {
        $result = $this->{"query"}($sqlQuery, $bindParams, $bindTypes);

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
    public function fetchColumn(string $sqlQuery, ?array $placeholders = [], int $column = 0): string | bool
    {
        $row = $this->fetchOne($sqlQuery, Enum::FETCH_BOTH, $placeholders);
        $columnValue = $row[$column] ?? false;
        return $columnValue;
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
     */
    public function fetchOne(string $sqlQuery, ?int $fetchMode = Enum::FETCH_ASSOC, ?array $bindParams = null, ?array $bindTypes = null) : bool | array
    {
        $result = $this->{"query"}($sqlQuery, $bindParams, $bindTypes);

        if (!is_object($result)) {
            return [];
        }

        if ($fetchMode !== null) {
            $result->setFetchMode($fetchMode);
        }

        return $result->fetch();
    }

    /**
     * Returns a SQL modified with a FOR UPDATE clause
     */
    public function forUpdate(string $sqlQuery): string
    {
        return $this->dialect->forUpdate($sqlQuery);
    }

    /**
     * Returns the SQL column definition from a column
     */
    public function getColumnDefinition( ColumnInterface $column): string
    {
        return $this->dialect->getColumnDefinition($column);
    }

    /**
     * Gets a list of columns
     */
    public function getColumnList($columnList): string
    {
        return $this->dialect->getColumnList($columnList);
    }

    /**
     * Gets the active connection unique identifier
     */
    public function getConnectionId(): string
    {
        return $this->connectionId;
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
     * @todo Return NULL if this is not supported by the adapter
     */
    public function getDefaultValue(): RawValue
    {
        return new RawValue("DEFAULT");
    }

    /**
     * Return descriptor used to connect to the active database
     */
    public function getDescriptor() : array
    {
        return $this->descriptor;
    }

    /**
     * Returns internal dialect instance
     */
    public function getDialect() : DialectInterface
    {
        return $this->dialect;
    }

    /**
     * Returns the internal event manager
     */
    public function getEventsManager() : ManagerInterface
    {
        return $this->eventsManager;
    }

    /**
     * Returns the savepoint name to use for nested transactions
     */
    public function getNestedTransactionSavepointName(): string
    {
        return "PHALCON_SAVEPOINT_" . $this->transactionLevel;
    }

    /**
     * Active SQL statement in the object without replace bound parameters
     */
    public function getRealSQLStatement(): string
    {
        return $this->realSqlStatement;
    }

    /**
     * Active SQL statement in the object
     */
    public function getSQLBindTypes() : array
    {
        return $this->sqlBindTypes;
    }

    /**
     * Active SQL statement in the object
     */
    public function getSQLStatement(): string
    {
        return $this->sqlStatement;
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
     */
    public function insert(string $table, array $values, ?array $fields = null, ?array $dataTypes = null): bool
    {
        /*var bindDataTypes, bindType, escapedTable, escapedFields, field,
            insertSql, insertValues, joinedValues, placeholders, position,
            tableName, value;*/

        /**
         * A valid array with more than one element is required
         */
        if (empty($values)) {
            throw new Exception(
                "Unable to insert into " . $table . " without data"
            );
        }

        $placeholders  = [];
        $insertValues  = [];
        $bindDataTypes = [];

        /**
         * Objects are casted using __toString, null values are converted to
         * string "null", everything else is passed as "?"
         */
        foreach($values as $position => $value) {
            if (is_object($value) && $value instanceof RawValue) {
                $placeholders[] = (string) $value;
            } else {
                if (is_object($value)) {
                    $value = (string) $value;
                }

                if (is_null($value)) {
                    $placeholders[] = "null";
                } else {
                    $placeholders[] = "?";
                    $insertValues[] = $value;

                    if (is_array($dataTypes)) {
                        $bindType = $dataTypes[$position] ?? null;
                        if (is_null($bindType)) {
                            throw new Exception(
                                "Incomplete number of bind types"
                            );
                        }
                        $bindDataTypes[] = $bindType;
                    }
                }
            }
        }

        if (strpos($table, ".") > 0) {
            $tableName = explode(".", $table);
        } else {
            $tableName = $table;
        }

        $escapedTable = $this->escapeIdentifier($tableName);

        /**
         * Build the final SQL INSERT statement
         */
        $joinedValues = join(", ", $placeholders);

        if (is_array($fields)) {
            $escapedFields = [];

            foreach($fields as $field) {
                $escapedFields[] = $this->escapeIdentifier($field);
            }

            $insertSql = "INSERT INTO " . $escapedTable . " (" . join(", ", $escapedFields) . ") VALUES (" . $joinedValues . ")";
        } else {
            $insertSql = "INSERT INTO " . $escapedTable . " VALUES (" . $joinedValues . ")";
        }

        /**
         * Perform the execution via PDO::execute
         */
        if (empty($bindDataTypes)) {
            return $this->{"execute"}($insertSql, $insertValues);
        }

        return $this->{"execute"}($insertSql, $insertValues, $bindDataTypes);
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
     */
    public function insertAsDict(string $table, ?array $data, ?array $dataTypes = null): bool
    {
        if (empty($data)) {
            return false;
        }
        $fields = [];
        $values = [];
        foreach($data as $field => $value) {
            $fields[] = $field;
            $values[] = $value;
        }

        return $this->insert($table, $values, $fields, $dataTypes);
    }

    /**
     * Returns if nested transactions should use savepoints
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
     */
    public function limit(string $sqlQuery, int $number): string
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
     */
    public function listTables(?string $schemaName = null) : array
    {
        //var tables, table, allTables;

        $allTables = [];

        $tables = $this->fetchAll(
           $this->dialect->listTables($schemaName),
            Enum::FETCH_NUM
        );

        foreach($tables as $table) {
            $allTables[] = $table[0];
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
     */
    public function listViews(?string $schemaName = null) : array
    {
        //var tables, table, allTables;

        $allTables = [];

       $tables = $this->fetchAll(
           $this->dialect->listViews($schemaName),
            Enum::FETCH_NUM
        );

        foreach($table as $table){
            $allTables[] = $table[0];
        }

        return $allTables;
    }

    /**
     * Modifies a table column based on a definition
     */
    public function modifyColumn(string $tableName, ?string $schemaName, ColumnInterface $column, ?ColumnInterface $currentColumn = null): bool
    {
        return $this->{"execute"}(
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
     */
    public function releaseSavepoint(string $name): bool
    {
        $dialect = $this->dialect;

        if (!$dialect->supportsSavePoints()) {
            throw new Exception(
                "Savepoints are not supported by this database adapter"
            );
        }

        if (!$dialect->supportsReleaseSavePoints()) {
            return false;
        }

        return $this->{"execute"}(
            $dialect->releaseSavepoint($name)
        );
    }

    /**
     * Rollbacks given savepoint
     */
    public function rollbackSavepoint(string $name): bool
    {

        $dialect = $this->dialect;

        if (!$dialect->supportsSavePoints()) {
            throw new Exception(
                "Savepoints are not supported by this database adapter"
            );
        }


        return $this->{"execute"}(
            $dialect->rollbackSavepoint($name)
        );
    }

    /**
     * Sets the event manager
     */
    public function setEventsManager( ManagerInterface $eventsManager) : void
    {
        $this->eventsManager = $eventsManager;
    }

    /**
     * Sets the dialect used to produce the SQL
     */
    public function setDialect( DialectInterface $dialect) : void
    {
        $this->dialect = $dialect;
    }

    /**
     * Set if nested transactions should use savepoints
     */
    public function setNestedTransactionsWithSavepoints(bool $nestedTransactionsWithSavepoints) : AdapterInterface
    {
        if ($this->transactionLevel > 0) {
            throw new Exception(
                "Nested transaction with savepoints behavior cannot be changed while a transaction is open"
            );
        }

        if (!$this->dialect->supportsSavePoints()) {
            throw new Exception(
                "Savepoints are not supported by this database adapter"
            );
        }

        $this->transactionsWithSavepoints = $nestedTransactionsWithSavepoints;

        return $this;
    }

    /**
     * Returns a SQL modified with a LOCK IN SHARE MODE clause
     */
    public function sharedLock(string $sqlQuery): string
    {
        return $this->dialect->sharedLock($sqlQuery);
    }

    /**
     * Check whether the database system requires a sequence to produce
     * auto-numeric values
     */
    public function supportSequences(): bool
    {
        return false;
    }

    /**
     * Generates SQL checking for the existence of a schema.table
     *
     *```php
     * var_dump(
     *     $connection->tableExists("blog", "posts")
     * );
     *```
     */
    public function tableExists(string $tableName, ?string $schemaName = null): bool
    {
        return $this->fetchOne($this->dialect->tableExists($tableName, $schemaName), Enum::FETCH_NUM)[0] > 0;
    }

    /**
     * Gets creation options from a table
     *
     *```php
     * print_r(
     *     $connection->tableOptions("robots")
     * );
     *```
     */
    public function tableOptions(string $tableName, ?string $schemaName = null) : array
    {
        $sql = $this->dialect->tableOptions($tableName, $schemaName);

        if (empty($sql)) {
            return [];
        }

        return $this->fetchAll($sql, Enum::FETCH_ASSOC)[0];
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
     *         "bindTypes"  => [PDO::PARAM_INT], // use only if you use $dataTypes param
     *     ],
     *     [
     *         PDO::PARAM_STR
     *     ]
     * );
     *
     * ```
     *
     * Warning! If $whereCondition is string it not escaped.
     */
    public function update(string $table, ?array $fields, array $values, ?string $whereCondition = null, ?array $dataTypes = null): bool
    {
        /*var bindDataTypes, bindType, conditions, escapedField, escapedTable,
            field, placeholders, position, setClause, tableName, updateSql,
            updateValues, value, whereBind, whereTypes;*/

        $placeholders  = [];
        $updateValues  = [];
        $bindDataTypes = [];

        /**
         * Objects are casted using __toString, null values are converted to
         * string 'null', everything else is passed as '?'
         */
        foreach($values as $position => $value){
            $field = $fields[$position] ?? null;
            if (empty($field)) {
                throw new Exception(
                    "The number of values in the update is not the same as fields"
                );
            }

            $escapedField = $this->escapeIdentifier($field);

            if (is_object($value)  && $value instanceof RawValue) {
                 $placeholders[] = $escapedField . " = " . (string) $value;
            } else {
                if (is_object($value)) {
                    $value = (string) $value;
                }

                if ($value === null) {
                    $placeholders[] = $escapedField . " = null";
                } else {
                    $updateValues[] = $value;

                    if (is_array($dataTypes)) {
                        $bindType = $dataTypes[$position] ?? null;
                        if ($bindType===null) {
                            throw new Exception(
                                "Incomplete number of bind types"
                            );
                        }

                        $bindDataTypes[] = $bindType;
                    }

                    $placeholders[] = $escapedField . " = ?";
                }
            }
        }

        /**
         * Check if we got table and schema and escape it accordingly
         */
        if (strpos($table, ".") > 0) {
            $tableName = explode(".", $table);
        } else {
            $tableName = $table;
        }

        $escapedTable = $this->escapeIdentifier($tableName);
        $setClause    = join(", ", $placeholders);

        if ($whereCondition !== null) {
            $updateSql = "UPDATE " . $escapedTable . " SET " . $setClause . " WHERE ";

            /**
             * String conditions are simply appended to the SQL
             */
            if (is_string($whereCondition)) {
                $updateSql .= $whereCondition;
            } else {

                /**
                 * Array conditions may have bound params and bound types
                 */
                if (!is_array($whereCondition)) {
                    throw new Exception("Invalid WHERE clause conditions");
                }

                /**
                 * If an index 'conditions' is present it contains string where
                 * conditions that are appended to the UPDATE SQL
                 */
                $conditions = $whereCondition["conditions"] ?? null;
                if (is_string($conditions)) {
                    $updateSql .= $conditions;
                }

                /**
                 * Bound parameters are arbitrary values that are passed
                 * separately
                 */
                $whereBind = $whereCondition["bind"] ?? null;
                if ($whereBind !== null) {
                    merge_append($updateValues, $whereBind);
                }

                /**
                 * Bind types is how the bound parameters must be casted before
                 * be sent to the database system
                 */
                $whereTypes = $whereCondition["bindTypes"] ?? null;
                if ($whereTypes !== null)  {
                    merge_append($bindDataTypes, $whereTypes);
                }
            }
        } else {
            $updateSql = "UPDATE " . $escapedTable . " SET " . $setClause;
        }

        /**
         * Perform the update via PDO::execute
         */
        if (empty($bindDataTypes)) {
            return $this->{"execute"}($updateSql, $updateValues);
        }

        return $this->{"execute"}($updateSql, $updateValues, $bindDataTypes);
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
     */
    public function updateAsDict(string $table, array $data, string $whereCondition = null, ?array $dataTypes = null): bool
    {
        if (empty($data)) {
            return false;
        }
        $values = [];
        $fields = [];
        foreach($data as $field=>$value) { 
             $fields[] = $field;
             $values[] = $value;
        }

        return $this->update($table, $fields, $values, $whereCondition, $dataTypes);
    }

    /**
     * Check whether the database system requires an explicit value for identity
     * columns
     */
    public function useExplicitIdValue(): bool
    {
        return false;
    }

    /**
     * Check whether the database system support the DEFAULT
     * keyword (SQLite does not support it)
     *
     * @deprecated Will re removed in the next version
     */
    public function supportsDefaultValue(): bool {
        return true;
    }

    /**
     * Generates SQL checking for the existence of a schema.view
     *
     *```php
     * var_dump(
     *     $connection->viewExists("active_users", "posts")
     * );
     *```
     */
    public function viewExists(string $viewName, ?string $schemaName = null): bool
    {
        return $this->fetchOne($this->dialect->viewExists($viewName, $schemaName), Enum::FETCH_NUM)[0] > 0;
    }
}
