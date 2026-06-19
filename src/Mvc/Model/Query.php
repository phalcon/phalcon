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

namespace Phalcon\Mvc\Model;

use PDOException;
use Phalcon\Cache\CacheInterface;
use Phalcon\Cache\Exception\InvalidArgumentException;
use Phalcon\Db\Adapter\AdapterInterface;
use Phalcon\Db\Column;
use Phalcon\Db\RawValue;
use Phalcon\Db\ResultInterface;
use Phalcon\Di\DiInterface;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Di\Traits\InjectionAwareTrait;
use Phalcon\Mvc\Model\Query\Exceptions\AmbiguousColumn;
use Phalcon\Mvc\Model\Query\Exceptions\AmbiguousJoinRelation;
use Phalcon\Mvc\Model\Query\Exceptions\BindParameterNotInPlaceholders;
use Phalcon\Mvc\Model\Query\Exceptions\BindTypeRequiresArray;
use Phalcon\Mvc\Model\Query\Exceptions\BindValueRequired;
use Phalcon\Mvc\Model\Query\Exceptions\ColumnNotInDomain;
use Phalcon\Mvc\Model\Query\Exceptions\ColumnNotInSelectedModels;
use Phalcon\Mvc\Model\Query\Exceptions\CorruptedAst;
use Phalcon\Mvc\Model\Query\Exceptions\CorruptedDeleteAst;
use Phalcon\Mvc\Model\Query\Exceptions\CorruptedInsertAst;
use Phalcon\Mvc\Model\Query\Exceptions\CorruptedSelectAst;
use Phalcon\Mvc\Model\Query\Exceptions\CorruptedUpdateAst;
use Phalcon\Mvc\Model\Query\Exceptions\DeleteMultipleNotSupported;
use Phalcon\Mvc\Model\Query\Exceptions\DuplicateAlias;
use Phalcon\Mvc\Model\Query\Exceptions\EmptyArrayPlaceholderValue;
use Phalcon\Mvc\Model\Query\Exceptions\InsertColumnCountMismatch;
use Phalcon\Mvc\Model\Query\Exceptions\InvalidCachedResultset;
use Phalcon\Mvc\Model\Query\Exceptions\InvalidColumnDefinition;
use Phalcon\Mvc\Model\Query\Exceptions\InvalidInjectedManager;
use Phalcon\Mvc\Model\Query\Exceptions\InvalidInjectedMetadata;
use Phalcon\Mvc\Model\Query\Exceptions\InvalidQueryCacheService;
use Phalcon\Mvc\Model\Query\Exceptions\InvalidResultsetClass;
use Phalcon\Mvc\Model\Query\Exceptions\JoinAliasAlreadyUsed;
use Phalcon\Mvc\Model\Query\Exceptions\JoinFieldCountMismatch;
use Phalcon\Mvc\Model\Query\Exceptions\MissingCacheKey;
use Phalcon\Mvc\Model\Query\Exceptions\MissingMetaData;
use Phalcon\Mvc\Model\Query\Exceptions\MissingModelAttribute;
use Phalcon\Mvc\Model\Query\Exceptions\MissingModelsManager;
use Phalcon\Mvc\Model\Query\Exceptions\MixedDatabaseSystems;
use Phalcon\Mvc\Model\Query\Exceptions\ModelsListNotLoaded;
use Phalcon\Mvc\Model\Query\Exceptions\ModelSourceNotFound;
use Phalcon\Mvc\Model\Query\Exceptions\MultipleSqlStatementsNotSupported;
use Phalcon\Mvc\Model\Query\Exceptions\NoModelForAlias;
use Phalcon\Mvc\Model\Query\Exceptions\PhqlColumnNotInMap;
use Phalcon\Mvc\Model\Query\Exceptions\ReadConnectionMissing;
use Phalcon\Mvc\Model\Query\Exceptions\RelationshipNotFound;
use Phalcon\Mvc\Model\Query\Exceptions\ResultsetClassNotFound;
use Phalcon\Mvc\Model\Query\Exceptions\ResultsetNonCacheable;
use Phalcon\Mvc\Model\Query\Exceptions\UnknownBindType;
use Phalcon\Mvc\Model\Query\Exceptions\UnknownColumnType;
use Phalcon\Mvc\Model\Query\Exceptions\UnknownJoinType;
use Phalcon\Mvc\Model\Query\Exceptions\UnknownModelOrAlias;
use Phalcon\Mvc\Model\Query\Exceptions\UnknownPhqlExpression;
use Phalcon\Mvc\Model\Query\Exceptions\UnknownPhqlExpressionType;
use Phalcon\Mvc\Model\Query\Exceptions\UnknownPhqlStatement;
use Phalcon\Mvc\Model\Query\Exceptions\UpdateMultipleNotSupported;
use Phalcon\Mvc\Model\Query\Exceptions\WriteConnectionMissing;
use Phalcon\Mvc\Model\Query\Status;
use Phalcon\Mvc\Model\Query\StatusInterface;
use Phalcon\Mvc\Model\Resultset\Complex;
use Phalcon\Mvc\Model\Resultset\Simple;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Phql\Parser;
use Phalcon\Phql\Scanner\Opcode;
use Phalcon\Support\Settings;

use function array_merge;
use function class_exists;
use function explode;
use function get_class;
use function is_array;
use function is_int;
use function is_object;
use function is_subclass_of;
use function lcfirst;
use function method_exists;
use function str_replace;

/**
 * This class takes a PHQL intermediate representation and executes it.
 *
 *```php
 * $phql = "SELECT c.price*0.16 AS taxes, c.* FROM Cars AS c JOIN Brands AS b
 *          WHERE b.name = :name: ORDER BY c.name";
 *
 * $result = $manager->executeQuery(
 *     $phql,
 *     [
 *         "name" => "Lamborghini",
 *     ]
 * );
 *
 * foreach ($result as $row) {
 *     echo "Name: ",  $row->cars->name, "\n";
 *     echo "Price: ", $row->cars->price, "\n";
 *     echo "Taxes: ", $row->taxes, "\n";
 * }
 *
 * // with transaction
 * use Phalcon\Mvc\Model\Query;
 * use Phalcon\Mvc\Model\Transaction;
 *
 * // $di needs to have the service "db" registered for this to work
 * $di = Phalcon\Di\FactoryDefault::getDefault();
 *
 * $phql = 'SELECT * FROM robot';
 *
 * $myTransaction = new Transaction($di);
 * $myTransaction->begin();
 *
 * $newRobot = new Robot();
 * $newRobot->setTransaction($myTransaction);
 * $newRobot->type = "mechanical";
 * $newRobot->name = "Astro Boy";
 * $newRobot->year = 1952;
 * $newRobot->save();
 *
 * $queryWithTransaction = new Query($phql, $di);
 * $queryWithTransaction->setTransaction($myTransaction);
 *
 * $resultWithEntries = $queryWithTransaction->execute();
 *
 * $queryWithOutTransaction = new Query($phql, $di);
 * $resultWithOutEntries = $queryWithTransaction->execute();
 *```
 */
class Query implements QueryInterface, InjectionAwareInterface
{
    use InjectionAwareTrait;

    public const TYPE_DELETE = 303;
    public const TYPE_INSERT = 306;
    public const TYPE_SELECT = 309;
    public const TYPE_UPDATE = 300;

    /**
     * @var array
     * TODO: Add default value, instead of null, also remove type check
     */
    protected array $ast;

    /**
     * @var array
     */
    protected array $bindParams = [];

    /**
     * @var array
     */
    protected array $bindTypes = [];

    /**
     * @var CacheInterface|null
     */
    protected CacheInterface | null $cache = null;

    /**
     * @var array|null
     */
    protected array | null $cacheOptions = null;

    /**
     * @var bool
     */
    protected bool $enableImplicitJoins;

    /**
     * @var array|null
     */
    protected array | null $intermediate = null;

    /**
     * @var array|null
     */
    protected static array | null $internalPhqlCache = null;

    /**
     * @var ManagerInterface|null
     */
    protected ManagerInterface | null $manager = null;

    /**
     * @var MetaDataInterface|null
     */
    protected MetaDataInterface | null $metaData = null;

    /**
     * @var array
     */
    protected array $models = [];

    /**
     * @var array
     */
    protected array $modelsInstances = [];

    /**
     * @var int
     */
    protected int $nestingLevel = -1;

    /**
     * @var Parser
     */
    protected Parser $parser;

    /**
     * @var bool
     */
    protected bool $sharedLock = false;

    /**
     * @var array
     */
    protected array $sqlAliases = [];

    /**
     * @var array
     */
    protected array $sqlAliasesModels = [];

    /**
     * @var array
     */
    protected array $sqlAliasesModelsInstances = [];

    /**
     * @var array
     */
    protected array $sqlColumnAliases = [];

    /**
     * @var array
     */
    protected array $sqlModelsAliases = [];

    /**
     * TransactionInterface so that the query can wrap a transaction
     * around batch updates and intermediate selects within the transaction.
     * however if a model got a transaction set inside it will use the local
     * transaction instead of this one
     *
     * @var TransactionInterface|null
     */
    protected TransactionInterface | null $transaction = null;

    /**
     * @var int|null
     */
    protected int | null $type = null;

    /**
     * @var bool
     */
    protected bool $uniqueRow = false;

    /**
     * Phalcon\Mvc\Model\Query constructor
     *
     * @param string|null                  $phql
     * @param DiInterface|null  $container
     * @param array                        $options
     *
     * @throws Exception
     */
    public function __construct(
        protected string | null $phql = null,
        DiInterface | null $container = null,
        array $options = []
    ) {
        if (null !== $container) {
            $this->setDI($container);
        }

        if (isset($options["enable_implicit_joins"])) {
            $this->enableImplicitJoins = ($options["enable_implicit_joins"] == true);
        } else {
            $this->enableImplicitJoins = Settings::get(
                "orm.enable_implicit_joins"
            );
        }

        $this->bindParams = [];
        $this->bindTypes  = [];
        $this->parser     = new Parser();
    }

    /**
     * Sets the cache parameters of the query
     *
     * @param array $cacheOptions
     *
     * @return QueryInterface
     */
    public function cache(array $cacheOptions): QueryInterface
    {
        $this->cacheOptions = $cacheOptions;

        return $this;
    }

    /**
     * Destroys the internal PHQL cache
     *
     * @return void
     */
    public static function clean(): void
    {
        self::$internalPhqlCache = [];
    }

    /**
     * Executes a parsed PHQL statement
     *
     * @param array $bindParams
     * @param array $bindTypes
     *
     * @return mixed
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function execute(array $bindParams = [], array $bindTypes = []): mixed
    {
        $key          = null;
        $cache        = null;
        $lifetime     = null;
        $uniqueRow    = $this->uniqueRow;
        $cacheOptions = $this->cacheOptions;

        if (null !== $cacheOptions) {
            /**
             * The user must set a cache key
             */
            if (!isset($cacheOptions["key"])) {
                throw new MissingCacheKey();
            }

            $key          = $cacheOptions["key"];
            $cacheService = $cacheOptions["service"] ?? "modelsCache";

            /** @var CacheInterface $cache */
            if ($this->container instanceof DiInterface) {
                $cache = $this->container->getShared($cacheService);
            } else {
                $cache = $this->container->get($cacheService);
            }

            if (!$cache instanceof CacheInterface) {
                throw new InvalidQueryCacheService();
            }

            /**
             * If the lifetime is not specified in the options, use the
             * adapter's lifetime as a fallback
             */
            $adapter       = $cache->getAdapter();
            $cacheLifetime = $adapter->getLifetime();
            $lifetime      = $cacheOptions["lifetime"] ?? $cacheLifetime;

            $result = false;
            if ($cache->has($key)) {
                $result = $cache->get($key);
            }

            if (!empty($result)) {
                if (!is_object($result)) {
                    throw new InvalidCachedResultset();
                }

                $result->setIsFresh(false);

                /**
                 * Check if only the first row must be returned
                 */
                if ($uniqueRow) {
                    $preparedResult = $result->getFirst();
                } else {
                    $preparedResult = $result;
                }

                return $preparedResult;
            }

            $this->cache = $cache;
        }

        /**
         * The statement is parsed from its PHQL string or a previously
         * processed IR
         */
        $intermediate = $this->parse();

        /**
         * Check for default bind parameters and merge them with the passed ones
         */
        $defaultBindParams = $this->bindParams;
        $mergedParams      = $defaultBindParams + $bindParams;

        /**
         * Check for default bind types and merge them with the passed ones
         */
        $defaultBindTypes = $this->bindTypes;

        if (is_array($defaultBindTypes)) {
            $mergedTypes = $defaultBindTypes + $bindTypes;
        } else {
            $mergedTypes = $bindTypes;
        }

        $type = $this->type;

        $result = match ($type) {
            Opcode::SELECT->value => $this->executeSelect(
                $intermediate,
                $mergedParams,
                $mergedTypes
            ),
            Opcode::INSERT->value => $this->executeInsert(
                $intermediate,
                $mergedParams,
                $mergedTypes
            ),
            Opcode::UPDATE->value => $this->executeUpdate(
                $intermediate,
                $mergedParams,
                $mergedTypes
            ),
            Opcode::DELETE->value => $this->executeDelete(
                $intermediate,
                $mergedParams,
                $mergedTypes
            ),
            default             => throw new UnknownPhqlStatement((string) $type),
        };

        /**
         * We store the resultset in the cache if any
         */
        if (null !== $cacheOptions) {
            /**
             * Only PHQL SELECTs can be cached
             */
            if ($type != Opcode::SELECT->value) {
                throw new ResultsetNonCacheable();
            }

            $cache->set($key, $result, $lifetime);
        }

        /**
         * Check if only the first row must be returned
         */
        if ($uniqueRow) {
            $preparedResult = $result->getFirst();
        } else {
            $preparedResult = $result;
        }

        return $preparedResult;
    }

    /**
     * Returns default bind params
     *
     * @return array
     */
    public function getBindParams(): array
    {
        return $this->bindParams;
    }

    /**
     * Returns default bind types
     *
     * @return array
     */
    public function getBindTypes(): array
    {
        return $this->bindTypes;
    }

    /**
     * Returns the current cache backend instance
     *
     * @return AdapterInterface
     */
    public function getCache(): AdapterInterface
    {
        return $this->cache;
    }

    /**
     * Returns the current cache options
     *
     * @return array
     */
    public function getCacheOptions(): array
    {
        return $this->cacheOptions ?? [];
    }

    /**
     * Returns the intermediate representation of the PHQL statement
     *
     * @return array
     */
    public function getIntermediate(): array
    {
        return $this->intermediate;
    }

    /**
     * Executes the query returning the first result
     *
     * @param array $bindParams
     * @param array $bindTypes
     *
     * @return ModelInterface
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function getSingleResult(
        array $bindParams = [],
        array $bindTypes = []
    ): ModelInterface {
        /**
         * The query is already programmed to return just one row
         */
        if ($this->uniqueRow) {
            return $this->execute($bindParams, $bindTypes);
        }

        return $this->execute($bindParams, $bindTypes)->getFirst();
    }

    /**
     * Returns an associative array with the SQL to be generated by the internal PHQL,
     * and arrays with bound parameters and their types (only works in SELECT statements).
     *
     *```php
     * [
     *     'sql' => 'SELECT * FROM parts WHERE robot = :robot',
     *     'bind' => ['robot' => 123],
     *     'bindTypes => ['robot' => 1] // 1 corresponds to int
     * ]
     *```
     *
     * @return array
     * @throws Exception
     */
    public function getSql(): array
    {
        /**
         * The statement is parsed from its PHQL string or a previously
         * processed IR
         */
        $intermediate = $this->parse();

        if ($this->type == Opcode::SELECT->value) {
            return $this->executeSelect(
                $intermediate,
                $this->bindParams,
                $this->bindTypes,
                true
            );
        }

        throw new MultipleSqlStatementsNotSupported();
    }

    /**
     * @return TransactionInterface|null
     */
    public function getTransaction(): TransactionInterface | null
    {
        return $this->transaction;
    }

    /**
     * Gets the type of PHQL statement executed
     *
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * Check if the query is programmed to get only the first row in the
     * resultset
     *
     * @return bool
     */
    public function getUniqueRow(): bool
    {
        return $this->uniqueRow;
    }

    /**
     * Parses the intermediate code produced by Phalcon\Mvc\Model\Query\Lang
     * generating another intermediate representation that could be executed by
     * Phalcon\Mvc\Model\Query
     *
     * @return array|array[]
     * @throws Exception
     */
    public function parse(): array
    {
        $intermediate = $this->intermediate;

        if (is_array($intermediate)) {
            return $intermediate;
        }

        /**
         * This function parses the PHQL statement
         */
        $phql = $this->phql;
        $ast  = $this->parser
            ->setEnableLiterals((bool) Settings::get('orm.enable_literals'))
            ->parse($phql);

        $irPhql   = null;
        $uniqueId = null;

        if (is_array($ast)) {
            /**
             * Check if the prepared PHQL is already cached
             * Parsed ASTs have a unique id
             */
            if (isset($ast["id"])) {
                $uniqueId = $ast['id'];
                if (isset(self::$internalPhqlCache[$uniqueId])) {
                    $irPhql = self::$internalPhqlCache[$uniqueId];
                    if (is_array($irPhql)) {
                        // Assign the type to the query
                        $this->type = $ast["type"];

                        /**
                         * Refresh schema/source for every model referenced
                         * in the cached intermediate representation. The
                         * cache is keyed by the PHQL string only, so models
                         * that change their schema/source at runtime would
                         * otherwise keep producing SQL with the stale value
                         * baked in at first parse. See issue #17020.
                         */
                        return $this->refreshSchemasInIntermediate($irPhql);
                    }
                }
            }

            /**
             * A valid AST must have a type
             */
            if (isset($ast["type"])) {
                $type       = $ast["type"];
                $this->ast  = $ast;
                $this->type = $type;

                $irPhql = match ($type) {
                    Opcode::SELECT->value => $this->prepareSelect(),
                    Opcode::INSERT->value => $this->prepareInsert(),
                    Opcode::UPDATE->value => $this->prepareUpdate(),
                    Opcode::DELETE->value => $this->prepareDelete(),
                    default             => throw new UnknownPhqlStatement((string) $type),
                };
            }
        }

        if (!is_array($irPhql)) {
            throw new CorruptedAst();
        }

        /**
         * Store the prepared AST in the cache
         */
        if (is_int($uniqueId)) {
            self::$internalPhqlCache[$uniqueId] = $irPhql;
        }

        $this->intermediate = $irPhql;

        return $irPhql;
    }

    /**
     * Set default bind parameters
     *
     * @param array $bindParams
     * @param bool  $merge
     *
     * @return QueryInterface
     */
    public function setBindParams(
        array $bindParams,
        bool $merge = false
    ): QueryInterface {
        if ($merge) {
            $currentBindParams = $this->bindParams;
            $this->bindParams  = $currentBindParams + $bindParams;
        } else {
            $this->bindParams = $bindParams;
        }

        return $this;
    }

    /**
     * Set default bind parameters
     *
     * @param array $bindTypes
     * @param bool  $merge
     *
     * @return QueryInterface
     */
    public function setBindTypes(
        array $bindTypes,
        bool $merge = false
    ): QueryInterface {
        if ($merge) {
            $this->bindTypes = $this->bindTypes + $bindTypes;
        } else {
            $this->bindTypes = $bindTypes;
        }

        return $this;
    }

    /**
     * Sets the dependency injection container
     *
     * @param DiInterface $container
     *
     * @return void
     * @throws Exception
     */
    public function setDI(DiInterface $container): void
    {
        if ($container instanceof DiInterface) {
            $manager = $container->getShared("modelsManager");
        } else {
            $manager = $container->get("modelsManager");
        }

        if (!is_object($manager)) {
            throw new InvalidInjectedManager();
        }

        if ($container instanceof DiInterface) {
            $metaData = $container->getShared("modelsMetadata");
        } else {
            $metaData = $container->get("modelsMetadata");
        }

        if (!is_object($metaData)) {
            throw new InvalidInjectedMetadata();
        }

        $this->manager  = $manager;
        $this->metaData = $metaData;

        $this->container = $container;
    }

    /**
     * Allows to set the IR to be executed
     *
     * @param array $intermediate
     *
     * @return QueryInterface
     */
    public function setIntermediate(array $intermediate): QueryInterface
    {
        $this->intermediate = $intermediate;

        return $this;
    }

    /**
     * Set SHARED LOCK clause
     *
     * @param bool $sharedLock
     *
     * @return QueryInterface
     */
    public function setSharedLock(bool $sharedLock = false): QueryInterface
    {
        $this->sharedLock = $sharedLock;

        return $this;
    }

    /**
     * allows to wrap a transaction around all queries
     *
     * @param TransactionInterface $transaction
     *
     * @return QueryInterface
     */
    public function setTransaction(TransactionInterface $transaction): QueryInterface
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * Sets the type of PHQL statement to be executed
     *
     * @param int $type
     *
     * @return QueryInterface
     */
    public function setType(int $type): QueryInterface
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Tells to the query if only the first row in the resultset must be
     * returned
     *
     * @param bool $uniqueRow
     *
     * @return QueryInterface
     */
    public function setUniqueRow(bool $uniqueRow): QueryInterface
    {
        $this->uniqueRow = $uniqueRow;

        return $this;
    }

    /**
     * Executes the DELETE intermediate representation producing a
     * Phalcon\Mvc\Model\Query\Status
     *
     * @param array $intermediate
     * @param array $bindParams
     * @param array $bindTypes
     *
     * @return StatusInterface
     * @throws Exception
     */
    final protected function executeDelete(
        array $intermediate,
        array $bindParams,
        array $bindTypes
    ): StatusInterface {
        $models = $intermediate["models"];

        if (isset($models[1])) {
            throw new DeleteMultipleNotSupported();
        }

        $modelName = $models[0];

        /**
         * Load the model from the modelsManager or from the modelsInstances property
         */
        if (!isset($this->modelsInstances[$modelName])) {
            $model = $this->manager->load($modelName);
        } else {
            $model = $this->modelsInstances[$modelName];
        }

        /**
         * Get the records to be deleted
         */
        $records = $this->getRelatedRecords(
            $model,
            $intermediate,
            $bindParams,
            $bindTypes
        );

        /**
         * If there are no records to delete we return success
         */
        if (!count($records)) {
            return new Status(true);
        }

        $connection = $this->getWriteConnection(
            $model,
            $intermediate,
            $bindParams,
            $bindTypes
        );

        /**
         * Create a transaction in the write connection
         */
        $connection->begin();
        $records->rewind();

        while ($records->valid()) {
            try {
                $record = $records->current();

                /**
                 * We delete every record found
                 */
                if (!$record->delete()) {
                    /**
                     * Rollback the transaction
                     */
                    $connection->rollback();

                    return new Status(false, $record);
                }

                $records->next();
            } catch (PDOException $ex) {
                $connection->rollback();

                throw $ex;
            }
        }

        /**
         * Commit the transaction
         */
        $connection->commit();

        /**
         * Create a status to report the deletion status
         */
        return new Status(true);
    }

    /**
     * Executes the INSERT intermediate representation producing a
     * Phalcon\Mvc\Model\Query\Status
     *
     * @param array $intermediate
     * @param array $bindParams
     * @param array $bindTypes
     *
     * @return StatusInterface
     * @throws Exception
     */
    final protected function executeInsert(
        array $intermediate,
        array $bindParams,
        array $bindTypes
    ): StatusInterface {
        $modelName = $intermediate["model"];

        if (!isset($this->modelsInstances[$modelName])) {
            $model = $this->manager->load($modelName);
        } else {
            $model = $this->modelsInstances[$modelName];
        }

        $connection = $this->getWriteConnection(
            $model,
            $intermediate,
            $bindParams,
            $bindTypes
        );

        $attributes      = $this->metaData->getAttributes($model);
        $automaticFields = false;

        /**
         * The "fields" index may already have the fields to be used in the
         * query
         */
        $columnMap = null;
        if (!isset($intermediate["fields"])) {
            $automaticFields = true;
            $fields          = $attributes;

            if (Settings::get("orm.column_renaming")) {
                $columnMap = $this->metaData->getColumnMap($model);
            }
        } else {
            $fields = $intermediate["fields"];
        }

        $values = $intermediate["values"];

        /**
         * The number of calculated values must be equal to the number of fields
         * in the model
         */
        if (count($fields) != count($values)) {
            throw new InsertColumnCountMismatch();
        }

        /**
         * Get the dialect to resolve the SQL expressions
         */
        $dialect = $connection->getDialect();

        $insertValues = [];
        foreach ($values as $number => $value) {
            $exprValue = $value["value"];

            switch ($value["type"]) {
                case Opcode::STRING->value:
                case Opcode::INTEGER->value:
                case Opcode::DOUBLE->value:
                    $insertValue = $dialect->getSqlExpression($exprValue);
                    break;

                case Opcode::NULL->value:
                    $insertValue = null;
                    break;

                case Opcode::NPLACEHOLDER->value:
                case Opcode::SPLACEHOLDER->value:
                case Opcode::BPLACEHOLDER->value:
                    $wildcard = str_replace(
                        ":",
                        "",
                        $dialect->getSqlExpression($exprValue)
                    );

                    if (!isset($bindParams[$wildcard])) {
                        throw new BindParameterNotInPlaceholders($wildcard);
                    }

                    $insertValue = $bindParams[$wildcard];
                    break;

                default:
                    $insertValue = new RawValue(
                        $dialect->getSqlExpression($exprValue)
                    );

                    break;
            }

            $fieldName = $fields[$number];

            /**
             * If the user didn't define a column list we assume all the model's
             * attributes as columns
             */
            if ($automaticFields && is_array($columnMap)) {
                if (!isset($columnMap[$fieldName])) {
                    throw new PhqlColumnNotInMap($fieldName);
                }

                $attributeName = $columnMap[$fieldName];
            } else {
                $attributeName = $fieldName;
            }

            $insertValues[$attributeName] = $insertValue;
        }

        /**
         * Get model from the Models Manager
         */
        $insertModel = $this->manager->load($modelName);

        $insertModel->assign($insertValues);

        /**
         * Call 'create' to ensure that an insert is performed
         * Return the insert status
         */
        return new Status($insertModel->create(), $insertModel);
    }

    /**
     * Executes the SELECT intermediate representation producing a
     * Phalcon\Mvc\Model\Resultset
     *
     * @param array $intermediate
     * @param array $bindParams
     * @param array $bindTypes
     * @param bool  $simulate
     *
     * @return ResultsetInterface|array
     * @throws Exception
     */
    final protected function executeSelect(
        array $intermediate,
        array $bindParams,
        array $bindTypes,
        bool $simulate = false
    ): ResultsetInterface | array {
        /**
         * Get a database connection
         */
        $model           = null;
        $isSimpleStd     = false;
        $connection      = null;
        $connectionTypes = [];
        $models          = $intermediate["models"];

        foreach ($models as $modelName) {
            // Load model if it is not loaded
            if (!isset($this->modelsInstances[$modelName])) {
                $model                             = $this->manager->load($modelName);
                $this->modelsInstances[$modelName] = $model;
            } else {
                $model = $this->modelsInstances[$modelName];
            }

            if (isset($intermediate["forUpdate"]) && $intermediate["forUpdate"]) {
                $connection = $this->getWriteConnection(
                    $model,
                    $intermediate,
                    $bindParams,
                    $bindTypes
                );
            } else {
                $connection = $this->getReadConnection(
                    $model,
                    $intermediate,
                    $bindParams,
                    $bindTypes
                );
            }

            if (is_object($connection)) {
                // More than one type of connection is not allowed
                $connectionTypes[$connection->getType()] = true;

                if (count($connectionTypes) == 2) {
                    throw new MixedDatabaseSystems();
                }
            }
        }

        $columns = $intermediate["columns"];

        $haveObjects = false;
        $haveScalars = false;
        $isComplex   = false;

        // Check if the resultset have objects and how many of them have
        $numberObjects = 0;
        $columns1      = $columns;

        foreach ($columns as $column) {
            if (!is_array($column)) {
                throw new InvalidColumnDefinition();
            }

            if ($column["type"] === "scalar") {
                if (!isset($column["balias"])) {
                    $isComplex = true;
                }

                $haveScalars = true;
            } else {
                $haveObjects = true;
                $numberObjects++;
            }
        }

        // Check if the resultset to return is complex or simple
        if (!$isComplex) {
            if ($haveObjects) {
                if ($haveScalars) {
                    $isComplex = true;
                } else {
                    if ($numberObjects === 1) {
                        $isSimpleStd = false;
                    } else {
                        $isComplex = true;
                    }
                }
            } else {
                $isSimpleStd = true;
            }
        }

        // Processing selected columns
        $instance        = null;
        $selectColumns   = [];
        $simpleColumnMap = [];

        foreach ($columns as $aliasCopy => $column) {
            $sqlColumn = $column["column"];

            // Complete objects are treated in a different way
            if ($column["type"] === "object") {
                $modelName = $column["model"];

                /**
                 * Base instance
                 */
                if (!isset($this->modelsInstances[$modelName])) {
                    $instance                          = $this->manager->load($modelName);
                    $this->modelsInstances[$modelName] = $instance;
                } else {
                    $instance = $this->modelsInstances[$modelName];
                }

                $attributes = $this->metaData->getAttributes($instance);

                if ($isComplex) {
                    /**
                     * If the resultset is complex we open every model into
                     * their columns
                     */
                    if (Settings::get("orm.column_renaming")) {
                        $columnMap = $this->metaData->getColumnMap($instance);
                    } else {
                        $columnMap = null;
                    }

                    // Add every attribute in the model to the generated select
                    foreach ($attributes as $attribute) {
                        $selectColumns[] = [
                            $attribute,
                            $sqlColumn,
                            "_" . $sqlColumn . "_" . $attribute,
                        ];
                    }

                    /**
                     * We cache required meta-data to make its future access
                     * faster
                     */
                    $columns1[$aliasCopy]["instance"]   = $instance;
                    $columns1[$aliasCopy]["attributes"] = $attributes;
                    $columns1[$aliasCopy]["columnMap"]  = $columnMap;

                    // Check if the model keeps snapshots
                    $isKeepingSnapshots = (bool)$this->manager->isKeepingSnapshots($instance);
                    if ($isKeepingSnapshots) {
                        $columns1[$aliasCopy]["keepSnapshots"] = $isKeepingSnapshots;
                    }
                } else {
                    /**
                     * Query only the columns that are registered as attributes
                     * in the metaData
                     */
                    foreach ($attributes as $attribute) {
                        $selectColumns[] = [$attribute, $sqlColumn];
                    }
                }
            } else {
                /**
                 * Create an alias if the column does not have one
                 */
                if (is_int($aliasCopy)) {
                    $columnAlias = [$sqlColumn, null];
                } else {
                    $columnAlias = [$sqlColumn, null, $aliasCopy];
                }

                $selectColumns[] = $columnAlias;
            }

            /**
             * Simulate a column map
             */
            if (!$isComplex && $isSimpleStd) {
                if (isset($column["sqlAlias"])) {
                    $sqlAlias                   = $column["sqlAlias"];
                    $simpleColumnMap[$sqlAlias] = $aliasCopy;
                } else {
                    $simpleColumnMap[$aliasCopy] = $aliasCopy;
                }
            }
        }

        $processed               = [];
        $bindCounts              = [];
        $intermediate["columns"] = $selectColumns;

        /**
         * Replace the placeholders
         */
        foreach ($bindParams as $wildcard => $value) {
            if (is_int($wildcard)) {
                $wildcardValue = ":" . $wildcard;
            } else {
                $wildcardValue = $wildcard;
            }

            $processed[$wildcardValue] = $value;

            if (is_array($value)) {
                $bindCounts[$wildcardValue] = count($value);
            }
        }

        $processedTypes = [];

        /**
         * Replace the bind Types
         */
        foreach ($bindTypes as $typeWildcard => $value) {
            if (is_int($typeWildcard)) {
                $processedTypes[":" . $typeWildcard] = $value;
            } else {
                $processedTypes[$typeWildcard] = $value;
            }
        }

        if (count($bindCounts)) {
            $intermediate["bindCounts"] = $bindCounts;
        }

        /**
         * The corresponding SQL dialect generates the SQL statement based
         * accordingly with the database system
         */
        $dialect   = $connection->getDialect();
        $sqlSelect = $dialect->select($intermediate);

        if ($this->sharedLock) {
            $sqlSelect = $dialect->sharedLock($sqlSelect);
        }

        /**
         * Embed RawValue bind params directly in the SQL instead of passing
         * them to PDO, which would quote them as strings.
         */
        foreach ($processed as $wildcard => $value) {
            if ($value instanceof RawValue) {
                if (substr((string) $wildcard, 0, 1) === ":") {
                    $sqlSelect = str_replace((string) $wildcard, (string) $value, $sqlSelect);
                } else {
                    $sqlSelect = str_replace(":" . $wildcard, (string) $value, $sqlSelect);
                }

                unset($processed[$wildcard]);
                unset($processedTypes[$wildcard]);
            }
        }

        /**
         * Return the SQL to be executed instead of execute it
         */
        if ($simulate) {
            return [
                "sql"       => $sqlSelect,
                "bind"      => $processed,
                "bindTypes" => $processedTypes,
            ];
        }

        /**
         * Execute the query
         */
        $result = $connection->query($sqlSelect, $processed, $processedTypes);

        /**
         * Check if the query has data
         *
         * Previous if [leaving here on purpose]:
         * if result instanceof ResultInterface && result->numRows() {
         */
        $resultData = null;
        if ($result instanceof ResultInterface) {
            $resultData = $result;
        }

        /**
         * Choose a resultset type
         */
        $cache = $this->cache;

        if (!$isComplex) {
            /**
             * Select the base object
             */
            if ($isSimpleStd) {
                /**
                 * If the result is a simple standard object use an
                 * Phalcon\Mvc\Model\Row as base
                 */
                $resultObject = new Row();

                /**
                 * Standard objects can't keep snapshots
                 */
                $isKeepingSnapshots = false;
            } else {
                $resultObject = $model;
                if (is_object($instance)) {
                    $resultObject = $instance;
                }

                /**
                 * Get the column map
                 */
                if (!Settings::get("orm.cast_on_hydrate")) {
                    $simpleColumnMap = $this->metaData->getColumnMap($resultObject);
                } else {
                    $columnMap      = $this->metaData->getColumnMap($resultObject);
                    $typesColumnMap = $this->metaData->getDataTypes($resultObject);

                    if ($columnMap === null) {
                        $simpleColumnMap = [];

                        foreach ($this->metaData->getAttributes($resultObject) as $attribute) {
                            $simpleColumnMap[$attribute] = [
                                $attribute,
                                $typesColumnMap[$attribute],
                            ];
                        }
                    } else {
                        $simpleColumnMap = [];

                        foreach ($columnMap as $column => $attribute) {
                            $simpleColumnMap[$column] = [
                                $attribute,
                                $typesColumnMap[$column],
                            ];
                        }
                    }
                }

                /**
                 * Check if the model keeps snapshots
                 */
                $isKeepingSnapshots = (bool)$this->manager->isKeepingSnapshots($resultObject);
            }

            if (
                $resultObject instanceof ModelInterface &&
                method_exists($resultObject, "getResultsetClass")
            ) {
                $resultsetClassName = $resultObject->getResultsetClass();

                if ($resultsetClassName) {
                    if (!class_exists($resultsetClassName)) {
                        throw new ResultsetClassNotFound($resultsetClassName);
                    }

                    if (!is_subclass_of($resultsetClassName, "Phalcon\\Mvc\\Model\\ResultsetInterface")) {
                        throw new InvalidResultsetClass($resultsetClassName);
                    }

                    return new $resultsetClassName(
                        $simpleColumnMap,
                        $resultObject,
                        $resultData,
                        $cache,
                        $isKeepingSnapshots
                    );
                }
            }

            /**
             * Simple resultsets contains only complete objects
             */
            return new Simple(
                $simpleColumnMap,
                $resultObject,
                $resultData,
                $cache,
                $isKeepingSnapshots
            );
        }

        /**
         * Complex resultsets may contain complete objects and scalars
         */
        return new Complex($columns1, $resultData, $cache);
    }

    /**
     * Executes the UPDATE intermediate representation producing a
     * Phalcon\Mvc\Model\Query\Status
     *
     * @param array $intermediate
     * @param array $bindParams
     * @param array $bindTypes
     *
     * @return StatusInterface
     */
    final protected function executeUpdate(
        array $intermediate,
        array $bindParams,
        array $bindTypes
    ): StatusInterface {
        $models = $intermediate["models"];

        if (isset($models[1])) {
            throw new UpdateMultipleNotSupported();
        }

        $modelName = $models[0];

        /**
         * Load the model from the modelsManager or from the modelsInstances
         * property
         */
        $model = $this->modelsInstances[$modelName] ?? $this->manager->load($modelName);

        $connection = $this->getWriteConnection(
            $model,
            $intermediate,
            $bindParams,
            $bindTypes
        );

        $dialect = $connection->getDialect();
        $fields  = $intermediate["fields"];
        $values  = $intermediate["values"];

        /**
         * updateValues is applied to every record
         */
        $updateValues = [];

        /**
         * If a placeholder is unused in the update values, we assume that it's
         * used in the SELECT
         */
        $selectBindParams = $bindParams;
        $selectBindTypes  = $bindTypes;

        foreach ($fields as $number => $field) {
            $value     = $values[$number];
            $exprValue = $value["value"];
            $fieldName = $field["balias"] ?? $field["name"];

            switch ($value["type"]) {
                case Opcode::STRING->value:
                case Opcode::INTEGER->value:
                case Opcode::DOUBLE->value:
                    $updateValue = $dialect->getSqlExpression($exprValue);
                    break;

                case Opcode::NULL->value:
                    $updateValue = null;
                    break;

                case Opcode::NPLACEHOLDER->value:
                case Opcode::SPLACEHOLDER->value:
                case Opcode::BPLACEHOLDER->value:
                    $wildcard = str_replace(
                        ":",
                        "",
                        $dialect->getSqlExpression($exprValue)
                    );

                    if (!isset($bindParams[$wildcard])) {
                        throw new BindParameterNotInPlaceholders($wildcard);
                    }

                    $updateValue = $bindParams[$wildcard];

                    unset($selectBindParams[$wildcard]);
                    unset($selectBindTypes[$wildcard]);

                    break;
                /**
                 * @todo duplicate branch
                 */
//                case Opcode::BPLACEHOLDER->value:
//                    throw new Exception("Not supported");

                default:
                    $sqlExpr = $dialect->getSqlExpression($exprValue);

                    /**
                     * If the expression contains named placeholders (e.g.
                     * "col + :param"), resolve them from bindParams so the
                     * RawValue is free of named params. This prevents a PDO
                     * "mixed named and positional parameters" error when the
                     * WHERE clause uses positional "?" markers.
                     */
                    $namedParams = [];

                    if (preg_match_all("/:([a-zA-Z0-9_]+)/", $sqlExpr, $namedParams)) {
                        /**
                         * Sort by length descending so a key like "id" does
                         * not partially match a longer placeholder like
                         * ":idx" when running preg_replace.
                         */
                        $paramKeys = array_unique($namedParams[1]);

                        usort(
                            $paramKeys,
                            function ($a, $b) {
                                return strlen($b) - strlen($a);
                            }
                        );

                        foreach ($paramKeys as $paramKey) {
                            if (isset($bindParams[$paramKey])) {
                                $paramValue = $bindParams[$paramKey];

                                if (is_int($paramValue) || is_float($paramValue)) {
                                    $sqlExpr = preg_replace(
                                        "/:" . preg_quote($paramKey, "/") . "\b/",
                                        (string) $paramValue,
                                        $sqlExpr
                                    );
                                } else {
                                    $sqlExpr = preg_replace(
                                        "/:" . preg_quote($paramKey, "/") . "\b/",
                                        $connection->escapeString((string) $paramValue),
                                        $sqlExpr
                                    );
                                }

                                unset($selectBindParams[$paramKey]);
                                unset($selectBindTypes[$paramKey]);
                            }
                        }
                    }

                    $updateValue = new RawValue($sqlExpr);

                    break;
            }

            $updateValues[$fieldName] = $updateValue;
        }

        /**
         * We need to query the records related to the update
         */
        $records = $this->getRelatedRecords(
            $model,
            $intermediate,
            $selectBindParams,
            $selectBindTypes
        );

        /**
         * If there are no records to apply the update we return success
         */
        if (!count($records)) {
            return new Status(true);
        }

        $connection = $this->getWriteConnection(
            $model,
            $intermediate,
            $bindParams,
            $bindTypes
        );

        /**
         * Create a transaction in the write connection
         */
        $connection->begin();
        $records->rewind();

        while ($records->valid()) {
            try {
                $record = $records->current();

                $record->assign($updateValues);

                /**
                 * We apply the executed values to every record found
                 */
                if (!$record->update()) {
                    /**
                     * Rollback the transaction on failure
                     */
                    $connection->rollback();

                    return new Status(false, $record);
                }

                $records->next();
            } catch (PDOException $ex) {
                $connection->rollback();

                throw $ex;
            }
        }

        /**
         * Commit transaction on success
         */
        $connection->commit();

        return new Status(true);
    }

    /**
     * Resolves an expression in a single call argument
     *
     * @param array $argument
     *
     * @return array|string[]
     * @throws Exception
     */
    final protected function getCallArgument(array $argument): array
    {
        if ($argument["type"] == Opcode::STARALL->value) {
            return [
                "type" => "all",
            ];
        }

        return $this->getExpression($argument);
    }

    /**
     * Resolves an expression in a single call argument
     *
     * @param array $expr
     *
     * @return array
     * @throws Exception
     */
    final protected function getCaseExpression(array $expr): array
    {
        $whenClauses = [];

        foreach ($expr["right"] as $whenExpr) {
            if (isset($whenExpr["right"])) {
                $whenClauses[] = [
                    "type" => "when",
                    "expr" => $this->getExpression($whenExpr["left"]),
                    "then" => $this->getExpression($whenExpr["right"]),
                ];
            } else {
                $whenClauses[] = [
                    "type" => "else",
                    "expr" => $this->getExpression($whenExpr["left"]),
                ];
            }
        }

        return [
            "type"         => "case",
            "expr"         => $this->getExpression($expr["left"]),
            "when-clauses" => $whenClauses,
        ];
    }

    /**
     * Resolves an expression from its intermediate code into an array
     *
     * @param array $expr
     * @param bool  $quoting
     *
     * @return array<array-key, list<array<string>>|string>
     * @throws Exception
     */
    final protected function getExpression(array $expr, bool $quoting = true): array
    {
        $left  = null;
        $right = null;

        if (isset($expr["type"])) {
            $exprType       = $expr["type"];
            $tempNotQuoting = true;

            if ($exprType != Opcode::CASE->value) {
                /**
                 * Resolving the left part of the expression if any
                 */
                if (isset($expr["left"])) {
                    $exprLeft = $expr["left"];
                    $left     = $this->getExpression($exprLeft, $tempNotQuoting);
                }

                /**
                 * Resolving the right part of the expression if any
                 */
                if (isset($expr["right"])) {
                    $exprRight = $expr["right"];
                    $right     = $this->getExpression($exprRight, $tempNotQuoting);
                }
            }

            /**
             * Every node in the AST has a unique integer type
             */
            switch ($exprType) {
                case Opcode::LESS->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "<",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::EQUALS->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "=",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::GREATER->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => ">",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::NOTEQUALS->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "<>",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::LESSEQUAL->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "<=",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::GREATEREQUAL->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => ">=",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::AND->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "AND",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::OR->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "OR",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::QUALIFIED->value:
                    $exprReturn = $this->getQualified($expr);
                    break;

                case Opcode::ADD->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "+",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::SUB->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "-",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::MUL->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "*",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::DIV->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "/",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::MOD->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "%",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::BITWISE_AND->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "&",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::BITWISE_OR->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "|",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::ENCLOSED->value:
                case Opcode::SUBQUERY->value:
                    $exprReturn = [
                        "type" => "parentheses",
                        "left" => $left,
                    ];

                    break;

                case Opcode::MINUS->value:
                    $exprReturn = [
                        "type"  => "unary-op",
                        "op"    => "-",
                        "right" => $right,
                    ];

                    break;

                case Opcode::INTEGER->value:
                case Opcode::DOUBLE->value:
                case Opcode::HINTEGER->value:
                    $exprReturn = [
                        "type"  => "literal",
                        "value" => $expr["value"],
                    ];

                    break;

                case Opcode::TRUE->value:
                    $exprReturn = [
                        "type"  => "literal",
                        "value" => "TRUE",
                    ];

                    break;

                case Opcode::FALSE->value:
                    $exprReturn = [
                        "type"  => "literal",
                        "value" => "FALSE",
                    ];

                    break;

                case Opcode::STRING->value:
                    $value = $expr["value"];

                    if ($quoting) {
                        /**
                         * Check if static literals have single quotes and
                         * escape them
                         */
                        if (str_contains($value, "'")) {
                            $escapedValue = $this->ormSingleQuotes($value);
                        } else {
                            $escapedValue = $value;
                        }

                        $exprValue = "'" . $escapedValue . "'";
                    } else {
                        $exprValue = $value;
                    }

                    $exprReturn = [
                        "type"  => "literal",
                        "value" => $exprValue,
                    ];

                    break;

                case Opcode::NPLACEHOLDER->value:
                    $exprReturn = [
                        "type"  => "placeholder",
                        "value" => str_replace("?", ":", $expr["value"]),
                    ];

                    break;

                case Opcode::SPLACEHOLDER->value:
                    $exprReturn = [
                        "type"  => "placeholder",
                        "value" => ":" . $expr["value"],
                    ];

                    break;

                case Opcode::BPLACEHOLDER->value:
                    $value = $expr["value"];
                    if (str_contains($value, ":")) {
                        $valueParts = explode(":", $value);
                        $name       = $valueParts[0];
                        $bindType   = $valueParts[1];

                        switch ($bindType) {
                            case "str":
                                $this->bindTypes[$name] = Column::BIND_PARAM_STR;

                                $exprReturn = [
                                    "type"  => "placeholder",
                                    "value" => ":" . $name,
                                ];

                                break;

                            case "int":
                                $this->bindTypes[$name] = Column::BIND_PARAM_INT;

                                $exprReturn = [
                                    "type"  => "placeholder",
                                    "value" => ":" . $name,
                                ];

                                break;

                            case "double":
                                $this->bindTypes[$name] = Column::BIND_PARAM_DECIMAL;

                                $exprReturn = [
                                    "type"  => "placeholder",
                                    "value" => ":" . $name,
                                ];

                                break;

                            case "bool":
                                $this->bindTypes[$name] = Column::BIND_PARAM_BOOL;

                                $exprReturn = [
                                    "type"  => "placeholder",
                                    "value" => ":" . $name,
                                ];

                                break;

                            case "blob":
                                $this->bindTypes[$name] = Column::BIND_PARAM_BLOB;

                                $exprReturn = [
                                    "type"  => "placeholder",
                                    "value" => ":" . $name,
                                ];

                                break;

                            case "null":
                                $this->bindTypes[$name] = Column::BIND_PARAM_NULL;

                                $exprReturn = [
                                    "type"  => "placeholder",
                                    "value" => ":" . $name,
                                ];

                                break;

                            case "array":
                            case "array-str":
                            case "array-int":
                                if (!isset($this->bindParams[$name])) {
                                    throw new BindValueRequired($name);
                                }

                                $bind = $this->bindParams[$name];

                                if (!is_array($bind)) {
                                    throw new BindTypeRequiresArray($name);
                                }

                                if (empty($bind)) {
                                    throw new EmptyArrayPlaceholderValue($name);
                                }

                                $exprReturn = [
                                    "type"     => "placeholder",
                                    "value"    => ":" . $name,
                                    "rawValue" => $name,
                                    "times"    => count($bind),
                                ];

                                break;

                            default:
                                throw new UnknownBindType($bindType);
                        }
                    } else {
                        $exprReturn = [
                            "type"  => "placeholder",
                            "value" => ":" . $value,
                        ];
                    }

                    break;

                case Opcode::NULL->value:
                    $exprReturn = [
                        "type"  => "literal",
                        "value" => "NULL",
                    ];

                    break;

                case Opcode::LIKE->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "LIKE",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::NLIKE->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "NOT LIKE",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::ILIKE->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "ILIKE",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::NILIKE->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "NOT ILIKE",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::NOT->value:
                    $exprReturn = [
                        "type"  => "unary-op",
                        "op"    => "NOT ",
                        "right" => $right,
                    ];

                    break;

                case Opcode::ISNULL->value:
                    $exprReturn = [
                        "type" => "unary-op",
                        "op"   => " IS NULL",
                        "left" => $left,
                    ];

                    break;

                case Opcode::ISNOTNULL->value:
                    $exprReturn = [
                        "type" => "unary-op",
                        "op"   => " IS NOT NULL",
                        "left" => $left,
                    ];

                    break;

                case Opcode::IN->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "IN",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::NOTIN->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "NOT IN",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::EXISTS->value:
                    $exprReturn = [
                        "type"  => "unary-op",
                        "op"    => "EXISTS",
                        "right" => $right,
                    ];

                    break;

                case Opcode::DISTINCT->value:
                    $exprReturn = [
                        "type"  => "unary-op",
                        "op"    => "DISTINCT ",
                        "right" => $right,
                    ];

                    break;

                case Opcode::BETWEEN_NOT->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "NOT BETWEEN",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::BETWEEN->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "BETWEEN",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::AGAINST->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "AGAINST",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::CAST->value:
                    $exprReturn = [
                        "type"  => "cast",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::CONVERT->value:
                    $exprReturn = [
                        "type"  => "convert",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::RAW_QUALIFIED->value:
                    $exprReturn = [
                        "type"  => "literal",
                        "value" => $expr["name"],
                    ];

                    break;

                case Opcode::FCALL->value:
                    $exprReturn = $this->getFunctionCall($expr);

                    break;

                case Opcode::CASE->value:
                    $exprReturn = $this->getCaseExpression($expr);

                    break;

                case Opcode::SELECT->value:
                    $exprReturn = [
                        "type"  => "select",
                        "value" => $this->prepareSelect($expr, true),
                    ];

                    break;

                case Opcode::OP_MATCHES->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "@@",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::OP_CONTAINS->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "@>",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::OP_CONTAINED->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "<@",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::OP_OVERLAPS->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "&&",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::OP_CONCAT->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "||",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::OP_JSON_GET->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "->",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::OP_JSON_GET_TEXT->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "->>",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::OP_JSON_PATH->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "#>",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                case Opcode::OP_JSON_PATH_TEXT->value:
                    $exprReturn = [
                        "type"  => "binary-op",
                        "op"    => "#>>",
                        "left"  => $left,
                        "right" => $right,
                    ];

                    break;

                default:
                    throw new UnknownPhqlExpressionType((string) $exprType);
            }

            return $exprReturn;
        }

        /**
         * It's a qualified column
         */
        if (isset($expr["domain"])) {
            return $this->getQualified($expr);
        }

        /**
         * If the expression does not have a type it's a list of nodes
         */
        if (isset($expr[0])) {
            $listItems = [];

            foreach ($expr as $exprListItem) {
                $listItems[] = $this->getExpression($exprListItem);
            }

            return [
                "type" => "list",
                $listItems,
            ];
        }

        throw new UnknownPhqlExpression();
    }

    /**
     * Resolves an expression in a single call argument
     *
     * @param array $expr
     *
     * @return array
     * @throws Exception
     */
    final protected function getFunctionCall(array $expr): array
    {
        if (isset($expr["arguments"])) {
            $arguments = $expr["arguments"];
            $distinct  = isset($expr["distinct"]) ? 1 : 0;

            if (isset($arguments[0])) {
                // There are more than one argument
                $functionArgs = [];

                foreach ($arguments as $argument) {
                    $functionArgs[] = $this->getCallArgument($argument);
                }
            } else {
                // There is only one argument
                $functionArgs = [
                    $this->getCallArgument($arguments),
                ];
            }

            if ($distinct) {
                return [
                    "type"      => "functionCall",
                    "name"      => $expr["name"],
                    "arguments" => $functionArgs,
                    "distinct"  => $distinct,
                ];
            } else {
                return [
                    "type"      => "functionCall",
                    "name"      => $expr["name"],
                    "arguments" => $functionArgs,
                ];
            }
        }

        return [
            "type" => "functionCall",
            "name" => $expr["name"],
        ];
    }

    /**
     * Returns a processed group clause for a SELECT statement
     *
     * @param array $group
     *
     * @return list<array<list<array<string>>|string>>
     * @throws Exception
     */
    final protected function getGroupClause(array $group): array
    {
        if (isset($group[0])) {
            /**
             * The select is grouped by several columns
             */
            $groupParts = [];

            foreach ($group as $groupItem) {
                $groupParts[] = $this->getExpression($groupItem);
            }
        } else {
            $groupParts = [
                $this->getExpression($group),
            ];
        }

        return $groupParts;
    }

    /**
     * Resolves a JOIN clause checking if the associated models exist
     *
     * @param ManagerInterface $manager
     * @param array            $join
     *
     * @return array{
     *     schema: null|string,
     *     source: string,
     *     modelName: string,
     *     model: ModelInterface
     * }
     * @throws Exception
     */
    final protected function getJoin(ManagerInterface $manager, array $join): array
    {
        if (isset($join["qualified"])) {
            $qualified = $join["qualified"];
            if ($qualified["type"] == Opcode::QUALIFIED->value) {
                $modelName = $qualified["name"];

                $model  = $manager->load($modelName);
                $source = $model->getSource();
                $schema = $model->getSchema();

                return [
                    "schema"    => $schema,
                    "source"    => $source,
                    "modelName" => $modelName,
                    "model"     => $model,
                ];
            }
        }

        throw new CorruptedSelectAst();
    }

    /**
     * Resolves a JOIN type
     *
     * @param array $join
     *
     * @return string
     * @throws Exception
     */
    final protected function getJoinType(array $join): string
    {
        if (!isset($join["type"])) {
            throw new CorruptedSelectAst();
        }

        $type = $join["type"];
        switch ($type) {
            case Opcode::INNERJOIN->value:
                return "INNER";

            case Opcode::LEFTJOIN->value:
                return "LEFT";

            case Opcode::RIGHTJOIN->value:
                return "RIGHT";

            case Opcode::CROSSJOIN->value:
                return "CROSS";

            case Opcode::FULLJOIN->value:
                return "FULL OUTER";
        }

        throw new UnknownJoinType((string) $type, (string) $this->phql);
    }

    /**
     * Processes the JOINs in the query returning an internal representation for
     * the database dialect
     *
     * @param array $select
     *
     * @return array
     * @throws Exception
     */
    final protected function getJoins(array $select): array
    {
        $models                    = $this->models;
        $sqlAliases                = $this->sqlAliases;
        $sqlAliasesModels          = $this->sqlAliasesModels;
        $sqlModelsAliases          = $this->sqlModelsAliases;
        $sqlAliasesModelsInstances = $this->sqlAliasesModelsInstances;
        $modelsInstances           = $this->modelsInstances;
        $fromModels                = $models;

        $sqlJoins         = [];
        $joinModels       = [];
        $joinSources      = [];
        $joinTypes        = [];
        $joinPreCondition = [];
        $joinPrepared     = [];

        $manager = $this->manager;

        $tables = $select["tables"];

        if (!isset($tables[0])) {
            $selectTables = [$tables];
        } else {
            $selectTables = $tables;
        }

        $joins = $select["joins"];

        if (!isset($joins[0])) {
            $selectJoins = [$joins];
        } else {
            $selectJoins = $joins;
        }

        foreach ($selectJoins as $joinItem) {
            /**
             * Check join alias
             */
            $joinData       = $this->getJoin($manager, $joinItem);
            $source         = $joinData["source"];
            $schema         = $joinData["schema"];
            $model          = $joinData["model"];
            $realModelName  = $joinData["modelName"];
            $completeSource = [$source, $schema];

            /**
             * Check join alias
             */
            $joinType = $this->getJoinType($joinItem);

            /**
             * Process join alias
             */
            if (isset($joinItem["alias"])) {
                $aliasExpr = $joinItem["alias"];
                $alias     = $aliasExpr["name"];

                /**
                 * Check if alias is unique
                 */
                if (isset($joinModels[$alias])) {
                    throw new JoinAliasAlreadyUsed($alias, (string) $this->phql);
                }

                /**
                 * Add the alias to the source
                 */
                $completeSource[] = $alias;

                /**
                 * Set the join type
                 */
                $joinTypes[$alias] = $joinType;

                /**
                 * Update alias: alias
                 */
                $sqlAliases[$alias] = $alias;

                /**
                 * Update model: alias
                 */
                $joinModels[$alias] = $realModelName;

                /**
                 * Update model: alias
                 */
                $sqlModelsAliases[$realModelName] = $alias;

                /**
                 * Update model: model
                 */
                $sqlAliasesModels[$alias] = $realModelName;

                /**
                 * Update alias: model
                 */
                $sqlAliasesModelsInstances[$alias] = $model;

                /**
                 * Update model: alias
                 */
                $models[$realModelName] = $alias;

                /**
                 * Complete source related to a model
                 */
                $joinSources[$alias] = $completeSource;

                /**
                 * Complete source related to a model
                 */
                $joinPrepared[$alias] = $joinItem;
            } else {
                /**
                 * Check if alias is unique
                 */
                if (isset($joinModels[$realModelName])) {
                    throw new JoinAliasAlreadyUsed($realModelName, (string) $this->phql);
                }

                /**
                 * Set the join type
                 */
                $joinTypes[$realModelName] = $joinType;

                /**
                 * Update model: source
                 */
                $sqlAliases[$realModelName] = $source;

                /**
                 * Update model: source
                 */
                $joinModels[$realModelName] = $source;

                /**
                 * Update model: model
                 */
                $sqlModelsAliases[$realModelName] = $realModelName;

                /**
                 * Update model: model
                 */
                $sqlAliasesModels[$realModelName] = $realModelName;

                /**
                 * Update model: model instance
                 */
                $sqlAliasesModelsInstances[$realModelName] = $model;

                /**
                 * Update model: source
                 */
                $models[$realModelName] = $source;

                /**
                 * Complete source related to a model
                 */
                $joinSources[$realModelName] = $completeSource;

                /**
                 * Complete source related to a model
                 */
                $joinPrepared[$realModelName] = $joinItem;
            }

            $modelsInstances[$realModelName] = $model;
        }

        /**
         * Update temporary properties
         */
        $this->models                    = $models;
        $this->sqlAliases                = $sqlAliases;
        $this->sqlAliasesModels          = $sqlAliasesModels;
        $this->sqlModelsAliases          = $sqlModelsAliases;
        $this->sqlAliasesModelsInstances = $sqlAliasesModelsInstances;
        $this->modelsInstances           = $modelsInstances;

        foreach ($joinPrepared as $joinAliasName => $joinItem) {
            /**
             * Check for predefined conditions
             */
            if (isset($joinItem["conditions"])) {
                $joinExpr                         = $joinItem["conditions"];
                $joinPreCondition[$joinAliasName] = $this->getExpression($joinExpr);
            }
        }

        /**
         * Skip all implicit joins if the option is not enabled
         */
        if (!$this->enableImplicitJoins) {
            foreach ($joinPrepared as $joinAliasName => $item) {
                $joinType     = $joinTypes[$joinAliasName];
                $joinSource   = $joinSources[$joinAliasName];
                $preCondition = $joinPreCondition[$joinAliasName];
                $sqlJoins[]   = [
                    "type"       => $joinType,
                    "source"     => $joinSource,
                    "conditions" => [$preCondition],
                ];
            }

            return $sqlJoins;
        }

        /**
         * Build the list of tables used in the SELECT clause
         */
        $fromModels = [];

        foreach ($selectTables as $tableItem) {
            $fromModels[$tableItem["qualifiedName"]["name"]] = true;
        }

        /**
         * Create join relationships dynamically
         */
        foreach ($fromModels as $fromModelName => $item) {
            foreach ($joinModels as $joinAlias => $joinModel) {
                /**
                 * Real source name for joined model
                 */
                $joinSource = $joinSources[$joinAlias];

                /**
                 * Join type is: LEFT, RIGHT, INNER, etc
                 */
                $joinType = $joinTypes[$joinAlias];

                /**
                 * Check if the model already have pre-defined conditions
                 */
                if (!isset($joinPreCondition[$joinAlias])) {
                    /**
                     * Get the model name from its source
                     */
                    $modelNameAlias = $sqlAliasesModels[$joinAlias];

                    /**
                     * Check if the joined model is an alias
                     */
                    $relation = $manager->getRelationByAlias(
                        $fromModelName,
                        $modelNameAlias
                    );

                    if ($relation === false) {
                        /**
                         * Check for relations between models
                         */
                        $relations = $manager->getRelationsBetween(
                            $fromModelName,
                            $modelNameAlias
                        );

                        if (is_array($relations)) {
                            /**
                             * More than one relation must throw an exception
                             */
                            if (count($relations) != 1) {
                                throw new AmbiguousJoinRelation($fromModelName, $joinModel, (string) $this->phql);
                            }

                            /**
                             * Get the first relationship
                             */
                            $relation = $relations[0];
                        }
                    }

                    /*
                     * Valid relations are objects
                     */
                    if (is_object($relation)) {
                        /**
                         * Get the related model alias of the left part
                         */
                        $modelAlias = $sqlModelsAliases[$fromModelName];

                        /**
                         * Generate the conditions based on the type of join
                         */
                        if (!$relation->isThrough()) {
                            $sqlJoin = $this->getSingleJoin(
                                $joinType,
                                $joinSource,
                                $modelAlias,
                                $joinAlias,
                                $relation
                            );
                        } else {
                            $sqlJoin = $this->getMultiJoin(
                                $joinType,
                                $joinSource,
                                $modelAlias,
                                $joinAlias,
                                $relation
                            );
                        }

                        /**
                         * Append or merge joins
                         */
                        if (isset($sqlJoin[0])) {
                            foreach ($sqlJoin as $sqlJoinItem) {
                                $sqlJoins[] = $sqlJoinItem;
                            }
                        } else {
                            $sqlJoins[] = $sqlJoin;
                        }
                    } else {
                        /**
                         * Join without conditions because no relation has been
                         * found between the models
                         */
                        $sqlJoins[] = [
                            "type"       => $joinType,
                            "source"     => $joinSource,
                            "conditions" => [],
                        ];
                    }
                } else {
                    $preCondition = $joinPreCondition[$joinAlias];

                    /**
                     * Get the conditions established by the developer
                     * Join with conditions established by the developer
                     */
                    $sqlJoins[] = [
                        "type"       => $joinType,
                        "source"     => $joinSource,
                        "conditions" => [$preCondition],
                    ];
                }
            }
        }

        return $sqlJoins;
    }

    /**
     * Returns a processed limit clause for a SELECT statement
     *
     * @param array $limitClause
     *
     * @return array
     * @throws Exception
     */
    final protected function getLimitClause(array $limitClause): array
    {
        $limit = [];

        if (isset($limitClause["number"])) {
            $limit["number"] = $this->getExpression($limitClause["number"]);
        }

        if (isset($limitClause["offset"])) {
            $limit["offset"] = $this->getExpression($limitClause["offset"]);
        }

        return $limit;
    }

    /**
     * Resolves joins involving many-to-many relations
     *
     * @param string            $joinType
     * @param string            $joinSource
     * @param string            $modelAlias
     * @param string            $joinAlias
     * @param RelationInterface $relation
     *
     * @return array
     * @throws Exception
     */
    final protected function getMultiJoin(
        string $joinType,
        mixed $joinSource,
        string $modelAlias,
        string $joinAlias,
        RelationInterface $relation
    ): array {
        $sqlJoins = [];

        /**
         * Local fields in the 'from' relation
         */
        $fields = $relation->getFields();

        /**
         * Referenced fields in the joined relation
         */
        $referencedFields = $relation->getReferencedFields();

        /**
         * Intermediate model
         */
        $intermediateModelName = $relation->getIntermediateModel();

        $manager = $this->manager;

        /**
         * Get the intermediate model instance
         */
        $intermediateModel = $manager->load($intermediateModelName);

        /**
         * Source of the related model
         */
        $intermediateSource = $intermediateModel->getSource();

        /**
         * Schema of the related model
         */
        $intermediateSchema = $intermediateModel->getSchema();

        //intermediateFullSource = [intermediateSchema, intermediateSource];

        /**
         * Update the internal sqlAliases to set up the intermediate model
         */
        $this->sqlAliases[$intermediateModelName] = $intermediateSource;

        /**
         * Update the internal sqlAliasesModelsInstances to rename columns if
         * necessary
         */
        $this->sqlAliasesModelsInstances[$intermediateModelName] = $intermediateModel;

        /**
         * Fields that join the 'from' model with the 'intermediate' model
         */
        $intermediateFields = $relation->getIntermediateFields();

        /**
         * Fields that join the 'intermediate' model with the intermediate model
         */
        $intermediateReferencedFields = $relation->getIntermediateReferencedFields();

        /**
         * Intermediate model
         */
        $referencedModelName = $relation->getReferencedModel();

        if (is_array($fields)) {
            foreach ($fields as $field => $position) {
                if (!isset($referencedFields[$position])) {
                    throw new JoinFieldCountMismatch($modelAlias, $joinAlias, (string) $this->phql);
                }

                /**
                 * Get the referenced field in the same position
                 */
                $intermediateField = $intermediateFields[$position];

                /**
                 * Create a binary operation for the join conditions
                 */
                $sqlEqualsJoinCondition = [
                    "type"  => "binary-op",
                    "op"    => "=",
                    "left"  => $this->getQualified(
                        [
                            "type"   => Opcode::QUALIFIED->value,
                            "domain" => $modelAlias,
                            "name"   => $field,
                        ]
                    ),
                    "right" => $this->getQualified(
                        [
                            "type"   => "qualified",
                            "domain" => $joinAlias,
                            "name"   => $referencedFields,
                        ]
                    ),
                ];
                //$sqlJoinPartialConditions[] = sqlEqualsJoinCondition;
            }
        } else {
            /**
             * Create the left part of the expression
             * Create the right part of the expression
             * Create a binary operation for the join conditions
             * A single join
             */
            $sqlJoins = [
                [
                    "type"       => $joinType,
                    "source"     => [$intermediateSource, $intermediateSchema],
                    "conditions" => [
                        [
                            "type"  => "binary-op",
                            "op"    => "=",
                            "left"  => $this->getQualified(
                                [
                                    "type"   => Opcode::QUALIFIED->value,
                                    "domain" => $modelAlias,
                                    "name"   => $fields,
                                ]
                            ),
                            "right" => $this->getQualified(
                                [
                                    "type"   => "qualified",
                                    "domain" => $intermediateModelName,
                                    "name"   => $intermediateFields,
                                ]
                            ),
                        ],
                    ],
                ],

                /**
                 * Create the left part of the expression
                 * Create the right part of the expression
                 * Create a binary operation for the join conditions
                 * A single join
                 */
                [
                    "type"       => $joinType,
                    "source"     => $joinSource,
                    "conditions" => [
                        [
                            "type"  => "binary-op",
                            "op"    => "=",
                            "left"  => $this->getQualified(
                                [
                                    "type"   => Opcode::QUALIFIED->value,
                                    "domain" => $intermediateModelName,
                                    "name"   => $intermediateReferencedFields,
                                ]
                            ),
                            "right" => $this->getQualified(
                                [
                                    "type"   => "qualified",
                                    "domain" => $referencedModelName,
                                    "name"   => $referencedFields,
                                ]
                            ),
                        ],
                    ],
                ],
            ];
        }

        return $sqlJoins;
    }

    /**
     * Returns a processed order clause for a SELECT statement
     *
     * @param array|string $order
     *
     * @return array
     * @throws Exception
     */
    final protected function getOrderClause(array | string $order): array
    {
        if (!isset($order[0])) {
            $orderColumns = [$order];
        } else {
            $orderColumns = $order;
        }

        $orderParts = [];

        foreach ($orderColumns as $orderItem) {
            $orderPartExpr = $this->getExpression($orderItem["column"]);

            /**
             * Check if the order has a predefined ordering mode
             */
            if (isset($orderItem["sort"])) {
                if ($orderItem["sort"] == Opcode::ASC->value) {
                    $orderPartSort = [$orderPartExpr, "ASC"];
                } else {
                    $orderPartSort = [$orderPartExpr, "DESC"];
                }
            } else {
                $orderPartSort = [$orderPartExpr];
            }

            $orderParts[] = $orderPartSort;
        }

        return $orderParts;
    }

    /**
     * Replaces the model's name to its source name in a qualified-name
     * expression
     *
     * @param array $expr
     *
     * @return string[]
     * @throws Exception
     */
    final protected function getQualified(array $expr): array
    {
        $columnName   = $expr["name"];
        $nestingLevel = $this->nestingLevel;

        /**
         * Check if the qualified name is a column alias
         */
        if (isset($this->sqlColumnAliases[$nestingLevel])) {
            $sqlColumnAliases = $this->sqlColumnAliases[$nestingLevel];
        } else {
            $sqlColumnAliases = [];
        }

        if (
            isset($sqlColumnAliases[$columnName]) &&
            (!isset($expr["domain"]) || empty($expr["domain"]))
        ) {
            return [
                "type" => "qualified",
                "name" => $columnName,
            ];
        }

        $metaData = $this->metaData;

        /**
         * Check if the qualified name has a domain
         */
        if (isset($expr["domain"])) {
            $columnDomain = $expr["domain"];
            $sqlAliases   = $this->sqlAliases;

            /**
             * The column has a domain, we need to check if it's an alias
             */
            if (!isset($sqlAliases[$columnDomain])) {
                throw new UnknownModelOrAlias($columnDomain, "11", (string) $this->phql);
            }

            $source = $sqlAliases[$columnDomain];

            /**
             * Change the selected column by its real name on its mapped table
             */
            $columnMap = null;
            if (Settings::get("orm.column_renaming")) {
                /**
                 * Retrieve the corresponding model by its alias
                 */
                $sqlAliasesModelsInstances = $this->sqlAliasesModelsInstances;

                /**
                 * We need the model instance to retrieve the reversed column
                 * map
                 */
                if (!isset($sqlAliasesModelsInstances[$columnDomain])) {
                    throw new NoModelForAlias($columnDomain, (string) $this->phql);
                }

                $model     = $sqlAliasesModelsInstances[$columnDomain];
                $columnMap = $metaData->getReverseColumnMap($model);
            }

            if (is_array($columnMap)) {
                if (!isset($columnMap[$columnName])) {
                    throw new ColumnNotInDomain($columnName, $columnDomain, (string) $this->phql);
                }

                $realColumnName = $columnMap[$columnName];
            } else {
                $realColumnName = $columnName;
            }
        } else {
            /**
             * If the column IR does not have a domain, we must check for
             * ambiguities
             */
            $number   = 0;
            $hasModel = false;

            foreach ($this->modelsInstances as $model) {
                /**
                 * Check if the attribute belongs to the current model
                 */
                if ($metaData->hasAttribute($model, $columnName)) {
                    $number++;

                    if ($number > 1) {
                        throw new AmbiguousColumn($columnName, (string) $this->phql);
                    }

                    $hasModel = $model;
                }
            }

            /**
             * After check in every model, the column does not belong to any of
             * the selected models
             */
            if ($hasModel === false) {
                throw new ColumnNotInSelectedModels($columnName, "1", (string) $this->phql);
            }

            /**
             * Check if the models property is correctly prepared
             */
            $models = $this->models;

            if (!is_array($models)) {
                throw new ModelsListNotLoaded();
            }

            /**
             * Obtain the model's source from the models list
             */
            $className = get_class($hasModel);

            if (!isset($models[$className])) {
                throw new ModelSourceNotFound($className, (string) $this->phql);
            }

            $source = $models[$className];

            /**
             * Rename the column
             */
            $columnMap = null;
            if (Settings::get("orm.column_renaming")) {
                $columnMap = $metaData->getReverseColumnMap($hasModel);
            }

            if (is_array($columnMap)) {
                /**
                 * The real column name is in the column map
                 */
                if (!isset($columnMap[$columnName])) {
                    throw new ColumnNotInSelectedModels($columnName, "3", (string) $this->phql);
                }
                $realColumnName = $columnMap[$columnName];
            } else {
                $realColumnName = $columnName;
            }
        }

        /**
         * Create an array with the qualified info
         */
        return [
            "type"   => "qualified",
            "domain" => $source,
            "name"   => $realColumnName,
            "balias" => $columnName,
        ];
    }

    /**
     * Gets the read connection from the model if there is no transaction set
     * inside the query object
     *
     * @param ModelInterface $model
     * @param array|null     $intermediate
     * @param array          $bindParams
     * @param array          $bindTypes
     *
     * @return AdapterInterface
     * @throws Exception
     */
    protected function getReadConnection(
        ModelInterface $model,
        array | null $intermediate = null,
        array $bindParams = [],
        array $bindTypes = []
    ): AdapterInterface {
        $connection  = null;
        $transaction = $this->transaction;

        if (is_object($transaction) && $transaction instanceof TransactionInterface) {
            return $transaction->getConnection();
        }

        if (method_exists($model, "selectReadConnection")) {
            // use selectReadConnection() if implemented in extended Model class
            $connection = $model->selectReadConnection(
                $intermediate,
                $bindParams,
                $bindTypes
            );

            if (!is_object($connection)) {
                throw new ReadConnectionMissing();
            }

            return $connection;
        }

        return $model->getReadConnection();
    }

    /**
     * Query the records on which the UPDATE/DELETE operation will be done
     *
     * @param ModelInterface $model
     * @param array          $intermediate
     * @param array          $bindParams
     * @param array          $bindTypes
     *
     * @return ResultsetInterface
     * @throws Exception
     * @throws InvalidArgumentException
     */
    final protected function getRelatedRecords(
        ModelInterface $model,
        array $intermediate,
        array $bindParams,
        array $bindTypes
    ): ResultsetInterface {
        /**
         * Instead of create a PHQL string statement we manually create the IR
         * representation
         */
        $selectIr = [
            "columns" => [
                [
                    "type"   => "object",
                    "model"  => get_class($model),
                    "column" => $model->getSource(),
                ],
            ],
            "models"  => $intermediate["models"],
            "tables"  => $intermediate["tables"],
        ];

        /**
         * Forward the JOINs (if any) so the related records are filtered by
         * the joined models too (UPDATE/DELETE ... JOIN support)
         */
        if (isset($intermediate["joins"])) {
            $selectIr["joins"] = $intermediate["joins"];
        }

        /**
         * Check if a WHERE clause was specified
         */
        if (isset($intermediate["where"])) {
            $selectIr["where"] = $intermediate["where"];
        }

        /**
         * Check if a LIMIT clause was specified
         */
        if (isset($intermediate["limit"])) {
            $selectIr["limit"] = $intermediate["limit"];
        }

        /**
         * We create another Phalcon\Mvc\Model\Query to get the related records
         */
        $query = new self();

        $query->setDI($this->container);
        $query->setType(Opcode::SELECT->value);
        $query->setIntermediate($selectIr);

        return $query->execute($bindParams, $bindTypes);
    }

    /**
     * Resolves a column from its intermediate representation into an array
     * used to determine if the resultset produced is simple or complex
     *
     * @param array $column
     *
     * @return array
     * @throws Exception
     */
    final protected function getSelectColumn(array $column): array
    {
        if (!isset($column["type"])) {
            throw new CorruptedSelectAst();
        }

        $columnType = $column["type"];
        $sqlColumns = [];

        /**
         * Check if column is eager loaded
         */
        $eager = $column["eager"] ?? null;

        /**
         * Check for select * (all)
         */
        if ($columnType == Opcode::STARALL->value) {
            foreach ($this->models as $modelName => $source) {
                $sqlColumn = [
                    "type"   => "object",
                    "model"  => $modelName,
                    "column" => $source,
                    // Namespaced model names are kept verbatim so callers
                    // can readAttribute(Model::class). Only single-word
                    // model names get lcfirst'd (legacy behavior).
                    "balias" => (strpos($modelName, '\\') !== false)
                        ? $modelName
                        : lcfirst($modelName),
                ];

                if ($eager !== null) {
                    $sqlColumn["eager"]     = $eager;
                    $sqlColumn["eagerType"] = $column["eagerType"];
                }

                $sqlColumns[] = $sqlColumn;
            }

            return $sqlColumns;
        }

        if (!isset($column["column"])) {
            throw new CorruptedSelectAst();
        }

        /**
         * Check if selected column is qualified.*, ex: robots.*
         */
        if ($columnType == Opcode::DOMAINALL->value) {
            $sqlAliases = $this->sqlAliases;

            /**
             * We only allow the alias.*
             */
            $columnDomain = $column["column"];

            if (!isset($sqlAliases[$columnDomain])) {
                throw new UnknownModelOrAlias($columnDomain, "2", (string) $this->phql);
            }

            $source = $sqlAliases[$columnDomain];

            /**
             * Get the SQL alias if any
             */
            $sqlColumnAlias = $source;
            $preparedAlias  = $column["balias"] ?? null;

            /**
             * Get the real source name
             */
            $sqlAliasesModels = $this->sqlAliasesModels;
            $modelName        = $sqlAliasesModels[$columnDomain];

            if (!is_string($preparedAlias)) {
                /**
                 * If the best alias is the model name, lowercase the first
                 * letter - but keep fully-qualified namespaced names verbatim
                 * so `readAttribute(Model::class)` matches downstream.
                 */
                if ($columnDomain == $modelName) {
                    $preparedAlias = (strpos($modelName, '\\') !== false)
                        ? $modelName
                        : lcfirst($modelName);
                } else {
                    $preparedAlias = $columnDomain;
                }
            }

            /**
             * Each item is a complex type returning a complete object
             */
            $sqlColumn = [
                "type"   => "object",
                "model"  => $modelName,
                "column" => $sqlColumnAlias,
                "balias" => $preparedAlias,
            ];

            if ($eager !== null) {
                $sqlColumn["eager"]     = $eager;
                $sqlColumn["eagerType"] = $column["eagerType"];
            }

            $sqlColumns[] = $sqlColumn;

            return $sqlColumns;
        }

        /**
         * Check for columns qualified and not qualified
         */
        if ($columnType == Opcode::EXPR->value) {
            /**
             * The sql_column is a scalar type returning a simple string
             */
            $sqlColumn     = ["type" => "scalar"];
            $columnData    = $column["column"];
            $sqlExprColumn = $this->getExpression($columnData);

            /**
             * Create balias and sqlAlias
             */
            if (isset($sqlExprColumn["balias"])) {
                $balias                = $sqlExprColumn["balias"];
                $sqlColumn["balias"]   = $balias;
                $sqlColumn["sqlAlias"] = $balias;
            }

            if ($eager !== null) {
                $sqlColumn["eager"]     = $eager;
                $sqlColumn["eagerType"] = $column["eagerType"];
            }

            $sqlColumn["column"] = $sqlExprColumn;
            $sqlColumns[]        = $sqlColumn;

            return $sqlColumns;
        }

        throw new UnknownColumnType((string) $columnType);
    }

    /**
     * Resolves joins involving has-one/belongs-to/has-many relations
     *
     * @param string            $joinType
     * @param string            $joinSource
     * @param string            $modelAlias
     * @param string            $joinAlias
     * @param RelationInterface $relation
     *
     * @return array
     * @throws Exception
     */
    final protected function getSingleJoin(
        string $joinType,
        string $joinSource,
        string $modelAlias,
        string $joinAlias,
        RelationInterface $relation
    ): array {
        $sqlJoinConditions = null;

        /**
         * Local fields in the 'from' relation
         */
        $fields = $relation->getFields();

        /**
         * Referenced fields in the joined relation
         */
        $referencedFields = $relation->getReferencedFields();

        if (!is_array($fields)) {
            /**
             * Create the left part of the expression
             * Create a binary operation for the join conditions
             * Create the right part of the expression
             */
            $sqlJoinConditions = [
                [
                    "type"  => "binary-op",
                    "op"    => "=",
                    "left"  => $this->getQualified(
                        [
                            "type"   => Opcode::QUALIFIED->value,
                            "domain" => $modelAlias,
                            "name"   => $fields,
                        ]
                    ),
                    "right" => $this->getQualified(
                        [
                            "type"   => "qualified",
                            "domain" => $joinAlias,
                            "name"   => $referencedFields,
                        ]
                    ),
                ],
            ];
        } else {
            /**
             * Resolve the compound operation
             */
            $sqlJoinPartialConditions = [];

            foreach ($fields as $position => $field) {
                /**
                 * Get the referenced field in the same position
                 */
                if (!isset($referencedFields[$position])) {
                    throw new JoinFieldCountMismatch($modelAlias, $joinAlias, (string) $this->phql);
                }

                $referencedField = $referencedFields[$position];

                /**
                 * Create the left part of the expression
                 * Create the right part of the expression
                 * Create a binary operation for the join conditions
                 */
                $sqlJoinPartialConditions[] = [
                    "type"  => "binary-op",
                    "op"    => "=",
                    "left"  => $this->getQualified(
                        [
                            "type"   => Opcode::QUALIFIED->value,
                            "domain" => $modelAlias,
                            "name"   => $field,
                        ]
                    ),
                    "right" => $this->getQualified(
                        [
                            "type"   => "qualified",
                            "domain" => $joinAlias,
                            "name"   => $referencedField,
                        ]
                    ),
                ];
            }
        }

        /**
         * A single join
         */
        return [
            "type"       => $joinType,
            "source"     => $joinSource,
            "conditions" => $sqlJoinConditions,
        ];
    }

    /**
     * Resolves a table in a SELECT statement checking if the model exists
     *
     * @param ManagerInterface $manager
     * @param array            $qualifiedName
     *
     * @return array|string
     * @throws Exception
     */
    final protected function getTable(
        ManagerInterface $manager,
        array $qualifiedName
    ): array | string {
        if (!isset($qualifiedName["name"])) {
            throw new CorruptedSelectAst();
        }

        $modelName = $qualifiedName["name"];
        $model     = $manager->load($modelName);
        $source    = $model->getSource();
        $schema    = $model->getSchema();

        if ($schema) {
            return [$schema, $source];
        }

        return $source;
    }

    /**
     * Gets the write connection from the model if there is no transaction
     * inside the query object
     *
     * @param ModelInterface $model
     * @param array|null     $intermediate
     * @param array          $bindParams
     * @param array          $bindTypes
     *
     * @return AdapterInterface
     * @throws Exception
     */
    protected function getWriteConnection(
        ModelInterface $model,
        array | null $intermediate = null,
        array $bindParams = [],
        array $bindTypes = []
    ): AdapterInterface {
        $connection  = null;
        $transaction = $this->transaction;

        if (is_object($transaction) && $transaction instanceof TransactionInterface) {
            return $transaction->getConnection();
        }

        if (method_exists($model, "selectWriteConnection")) {
            $connection = $model->selectWriteConnection(
                $intermediate,
                $bindParams,
                $bindTypes
            );

            if (!is_object($connection)) {
                throw new WriteConnectionMissing();
            }

            return $connection;
        }

        return $model->getWriteConnection();
    }

    /**
     * Analyzes a DELETE intermediate code and produces an array to be executed
     * later
     *
     * @return array
     * @throws Exception
     */
    final protected function prepareDelete(): array
    {
        $ast = $this->ast;

        if (!isset($ast["delete"])) {
            throw new CorruptedDeleteAst();
        }

        $delete = $ast["delete"];

        if (!isset($delete["tables"])) {
            throw new CorruptedDeleteAst();
        }

        $tables = $delete["tables"];

        /**
         * We use these arrays to store info related to models, alias and its
         * sources. Thanks to them we can rename columns later
         */
        $models          = [];
        $modelsInstances = [];

        $sqlTables                 = [];
        $sqlModels                 = [];
        $sqlAliases                = [];
        $sqlAliasesModelsInstances = [];

        if (!isset($tables[0])) {
            $deleteTables = [$tables];
        } else {
            $deleteTables = $tables;
        }

        $manager = $this->manager;

        foreach ($deleteTables as $table) {
            $qualifiedName = $table["qualifiedName"];
            $modelName     = $qualifiedName["name"];

            /**
             * Load a model instance from the models manager
             */
            $model  = $manager->load($modelName);
            $source = $model->getSource();
            $schema = $model->getSchema();

            if ($schema) {
                $completeSource = [$source, $schema];
            } else {
                $completeSource = [$source, null];
            }

            if (isset($table["alias"])) {
                $alias                             = $table["alias"];
                $sqlAliases[$alias]                = $alias;
                $completeSource[]                  = $alias;
                $sqlTables[]                       = $completeSource;
                $sqlAliasesModelsInstances[$alias] = $model;
                $models[$alias]                    = $modelName;
            } else {
                $sqlAliases[$modelName]                = $source;
                $sqlAliasesModelsInstances[$modelName] = $model;
                $sqlTables[]                           = $source;
                $models[$modelName]                    = $source;
            }

            $sqlModels[]                 = $modelName;
            $modelsInstances[$modelName] = $model;
        }

        /**
         * Update the models/alias/sources in the object
         */
        $this->models                    = $models;
        $this->modelsInstances           = $modelsInstances;
        $this->sqlAliases                = $sqlAliases;
        $this->sqlAliasesModelsInstances = $sqlAliasesModelsInstances;

        $sqlDelete           = [];
        $sqlDelete["tables"] = $sqlTables;
        $sqlDelete["models"] = $sqlModels;

        if (isset($ast["where"])) {
            $sqlDelete["where"] = $this->getExpression($ast["where"]);
        }

        if (isset($ast["limit"])) {
            $sqlDelete["limit"] = $this->getLimitClause($ast["limit"]);
        }

        return $sqlDelete;
    }

    /**
     * Analyzes an INSERT intermediate code and produces an array to be executed
     * later
     *
     * @return array
     * @throws Exception
     */
    final protected function prepareInsert(): array
    {
        $ast = $this->ast;

        if (
            !isset($ast["qualifiedName"]) ||
            !isset($ast["values"])
        ) {
            throw new CorruptedInsertAst();
        }

        $qualifiedName = $ast["qualifiedName"];

        // Check if the related model exists
        if (!isset($qualifiedName["name"])) {
            throw new CorruptedInsertAst();
        }

        $manager   = $this->manager;
        $modelName = $qualifiedName["name"];

        $model  = $manager->load($modelName);
        $source = $model->getSource();
        $schema = $model->getSchema();

        if ($schema) {
            $source = [$schema, $source];
        }

        $notQuoting = false;
        $exprValues = [];

        foreach ($ast["values"] as $exprValue) {
            // Resolve every expression in the "values" clause
            $exprValues[] = [
                "type"  => $exprValue["type"],
                "value" => $this->getExpression($exprValue, $notQuoting),
            ];
        }

        $sqlInsert = [
            "model" => $modelName,
            "table" => $source,
        ];

        $metaData = $this->metaData;

        if (isset($ast["fields"])) {
            $fields    = $ast["fields"];
            $sqlFields = [];

            foreach ($fields as $field) {
                $name = $field["name"];

                // Check that inserted fields are part of the model
                if (!$metaData->hasAttribute($model, $name)) {
                    throw new MissingModelAttribute($modelName, $name, (string) $this->phql);
                }

                // Add the file to the insert list
                $sqlFields[] = $name;
            }

            $sqlInsert["fields"] = $sqlFields;
        }

        $sqlInsert["values"] = $exprValues;

        return $sqlInsert;
    }

    /**
     * Analyzes a SELECT intermediate code and produces an array to be executed later
     *
     * @param mixed|null $ast
     * @param bool       $merge
     *
     * @return array
     * @throws Exception
     */
    final protected function prepareSelect(
        mixed $ast = null,
        bool $merge = false
    ): array {
        if (empty($ast)) {
            $ast = $this->ast;
        }

        $select = $ast["select"] ?? $ast;

        if (
            !isset($select["tables"]) ||
            !isset($select["columns"])
        ) {
            throw new CorruptedSelectAst();
        }

        $tables  = $select["tables"];
        $columns = $select["columns"];

        $this->nestingLevel++;

        /**
         * sqlModels is an array of the models to be used in the query
         */
        $sqlModels = [];

        /**
         * sqlTables is an array of the mapped models sources to be used in the
         * query
         */
        $sqlTables = [];

        /**
         * sqlColumns is an array of every column expression
         */
        $sqlColumns = [];

        /**
         * sqlAliases is a map from aliases to mapped sources
         */
        $sqlAliases = [];

        /**
         * sqlAliasesModels is a map from aliases to model names
         */
        $sqlAliasesModels = [];

        /**
         * sqlAliasesModels is a map from model names to aliases
         */
        $sqlModelsAliases = [];

        /**
         * sqlAliasesModelsInstances is a map from aliases to model instances
         */
        $sqlAliasesModelsInstances = [];

        /**
         * Models information
         */
        $models          = [];
        $modelsInstances = [];

        // Convert selected models in an array
        if (!isset($tables[0])) {
            $selectedModels = [$tables];
        } else {
            $selectedModels = $tables;
        }

        // Convert selected columns in an array
        if (!isset($columns[0])) {
            $selectColumns = [$columns];
        } else {
            $selectColumns = $columns;
        }

        $manager  = $this->manager;
        $metaData = $this->metaData;

        if (!is_object($manager)) {
            throw new MissingModelsManager();
        }

        if (!is_object($metaData)) {
            throw new MissingMetaData();
        }

        // Process selected models
        $number         = 0;
        $automaticJoins = [];

        foreach ($selectedModels as $selectedModel) {
            $qualifiedName = $selectedModel["qualifiedName"];
            $modelName     = $qualifiedName["name"];

            // Load a model instance from the models manager
            $model = $manager->load($modelName);

            // Define a complete schema/source
            $schema = $model->getSchema();
            $source = $model->getSource();

            // Obtain the real source including the schema
            if ($schema) {
                $completeSource = [$source, $schema];
            } else {
                $completeSource = $source;
            }

            /**
             * If an alias is defined for a model then the model cannot be
             * referenced in the column list
             */
            if (isset($selectedModel["alias"])) {
                $alias = $selectedModel["alias"];
                // Check if the alias was used before
                if (isset($sqlAliases[$alias])) {
                    throw new DuplicateAlias($alias, (string) $this->phql);
                }

                $sqlAliases[$alias]                = $alias;
                $sqlAliasesModels[$alias]          = $modelName;
                $sqlModelsAliases[$modelName]      = $alias;
                $sqlAliasesModelsInstances[$alias] = $model;

                /**
                 * Append or convert complete source to an array
                 */
                if (is_array($completeSource)) {
                    $completeSource[] = $alias;
                } else {
                    $completeSource = [$source, null, $alias];
                }

                $models[$modelName] = $alias;
            } else {
                $alias                                 = $source;
                $sqlAliases[$modelName]                = $source;
                $sqlAliasesModels[$modelName]          = $modelName;
                $sqlModelsAliases[$modelName]          = $modelName;
                $sqlAliasesModelsInstances[$modelName] = $model;
                $models[$modelName]                    = $source;
            }

            // Eager load any specified relationship(s)
            if (isset($selectedModel["with"])) {
                $with = $selectedModel["with"];
                if (!isset($with[0])) {
                    $withs = [$with];
                } else {
                    $withs = $with;
                }

                // Simulate the definition of inner joins
                foreach ($withs as $withItem) {
                    $joinAlias     = "AA" . $number;
                    $relationModel = $withItem["name"];

                    $relation = $manager->getRelationByAlias(
                        $modelName,
                        $relationModel
                    );

                    if (is_object($relation)) {
                        $bestAlias     = $relation->getOption("alias");
                        $relationModel = $relation->getReferencedModel();
                        $eagerType     = $relation->getType();
                    } else {
                        $relation = $manager->getRelationsBetween(
                            $modelName,
                            $relationModel
                        );

                        if (!is_object($relation)) {
                            throw new RelationshipNotFound($modelName, $relationModel, (string) $this->phql);
                        }

                        $bestAlias     = $relation->getOption("alias");
                        $relationModel = $relation->getReferencedModel();
                        $eagerType     = $relation->getType();
                    }

                    $selectColumns[] = [
                        "type"      => Opcode::DOMAINALL->value,
                        "column"    => $joinAlias,
                        "eager"     => $alias,
                        "eagerType" => $eagerType,
                        "balias"    => $bestAlias,
                    ];

                    $automaticJoins[] = [
                        "type"      => Opcode::INNERJOIN->value,
                        "qualified" => [
                            "type" => Opcode::QUALIFIED->value,
                            "name" => $relationModel,
                        ],
                        "alias"     => [
                            "type" => Opcode::QUALIFIED->value,
                            "name" => $joinAlias,
                        ],
                    ];

                    $number++;
                }
            }

            $sqlModels[]                 = $modelName;
            $sqlTables[]                 = $completeSource;
            $modelsInstances[$modelName] = $model;
        }

        // Assign Models/Tables information
        if (!$merge) {
            $this->models                    = $models;
            $this->modelsInstances           = $modelsInstances;
            $this->sqlAliases                = $sqlAliases;
            $this->sqlAliasesModels          = $sqlAliasesModels;
            $this->sqlModelsAliases          = $sqlModelsAliases;
            $this->sqlAliasesModelsInstances = $sqlAliasesModelsInstances;
        } else {
            $tempModels                    = $this->models;
            $tempModelsInstances           = $this->modelsInstances;
            $tempSqlAliases                = $this->sqlAliases;
            $tempSqlAliasesModels          = $this->sqlAliasesModels;
            $tempSqlModelsAliases          = $this->sqlModelsAliases;
            $tempSqlAliasesModelsInstances = $this->sqlAliasesModelsInstances;

            /**
             * In-place updates instead of array_merge: preserves the
             * "right-side wins, original order kept" semantics that
             * union (+) cannot give, with no fresh array allocation.
             */
            foreach ($models as $mergeKey => $mergeValue) {
                $this->models[$mergeKey] = $mergeValue;
            }

            foreach ($modelsInstances as $mergeKey => $mergeValue) {
                $this->modelsInstances[$mergeKey] = $mergeValue;
            }

            foreach ($sqlAliases as $mergeKey => $mergeValue) {
                $this->sqlAliases[$mergeKey] = $mergeValue;
            }

            foreach ($sqlAliasesModels as $mergeKey => $mergeValue) {
                $this->sqlAliasesModels[$mergeKey] = $mergeValue;
            }

            foreach ($sqlModelsAliases as $mergeKey => $mergeValue) {
                $this->sqlModelsAliases[$mergeKey] = $mergeValue;
            }

            foreach ($sqlAliasesModelsInstances as $mergeKey => $mergeValue) {
                $this->sqlAliasesModelsInstances[$mergeKey] = $mergeValue;
            }
        }

        $joins = $select["joins"] ?? [];

        // Join existing JOINS with automatic Joins
        if (count($joins)) {
            if (count($automaticJoins)) {
                if (isset($joins[0])) {
                    $select["joins"] = array_merge($joins, $automaticJoins);
                } else {
                    $automaticJoins[] = $joins;
                    $select["joins"]  = $automaticJoins;
                }
            }

            $sqlJoins = $this->getJoins($select);
        } else {
            if (count($automaticJoins)) {
                $select["joins"] = $automaticJoins;
                $sqlJoins        = $this->getJoins($select);
            } else {
                $sqlJoins = [];
            }
        }

        // Resolve selected columns
        $position         = 0;
        $sqlColumnAliases = [];

        foreach ($selectColumns as $column) {
            foreach ($this->getSelectColumn($column) as $sqlColumn) {
                /**
                 * If "alias" is set, the user defined an alias for the column
                 */
                if (isset($column["alias"])) {
                    $alias = $column["alias"];
                    /**
                     * The best alias is the one provided by the user
                     */
                    $sqlColumn["balias"]      = $alias;
                    $sqlColumn["sqlAlias"]    = $alias;
                    $sqlColumns[$alias]       = $sqlColumn;
                    $sqlColumnAliases[$alias] = true;
                } else {
                    /**
                     * "balias" is the best alias chosen for the column
                     */
                    if (isset($sqlColumn["balias"])) {
                        $alias              = $sqlColumn["balias"];
                        $sqlColumns[$alias] = $sqlColumn;
                    } else {
                        if ($sqlColumn["type"] === "scalar") {
                            $sqlColumns["_" . $position] = $sqlColumn;
                        } else {
                            $sqlColumns[] = $sqlColumn;
                        }
                    }
                }

                $position++;
            }
        }

        $this->sqlColumnAliases[$this->nestingLevel] = $sqlColumnAliases;

        // sqlSelect is the final prepared SELECT
        $sqlSelect = [
            "models"  => $sqlModels,
            "tables"  => $sqlTables,
            "columns" => $sqlColumns,
        ];

        if (isset($select["distinct"])) {
            $sqlSelect["distinct"] = $select["distinct"];
        }

        if (count($sqlJoins)) {
            $sqlSelect["joins"] = $sqlJoins;
        }

        // Process "WHERE" clause if set
        if (isset($ast["where"])) {
            $sqlSelect["where"] = $this->getExpression($ast["where"]);
        }

        // Process "GROUP BY" clause if set
        if (isset($ast["groupBy"])) {
            $sqlSelect["group"] = $this->getGroupClause($ast["groupBy"]);
        }

        // Process "HAVING" clause if set
        if (isset($ast["having"])) {
            $sqlSelect["having"] = $this->getExpression($ast["having"]);
        }

        // Process "ORDER BY" clause if set
        if (isset($ast["orderBy"])) {
            $sqlSelect["order"] = $this->getOrderClause($ast["orderBy"]);
        }

        // Process "LIMIT" clause if set
        if (isset($ast["limit"])) {
            $sqlSelect["limit"] = $this->getLimitClause($ast["limit"]);
        }

        // Process "FOR UPDATE" clause if set
        if (isset($ast["forUpdate"])) {
            $sqlSelect["forUpdate"] = true;
        }

        if ($merge) {
            $this->models                    = $tempModels;
            $this->modelsInstances           = $tempModelsInstances;
            $this->sqlAliases                = $tempSqlAliases;
            $this->sqlAliasesModels          = $tempSqlAliasesModels;
            $this->sqlModelsAliases          = $tempSqlModelsAliases;
            $this->sqlAliasesModelsInstances = $tempSqlAliasesModelsInstances;
        }

        $this->nestingLevel--;

        return $sqlSelect;
    }

    /**
     * Analyzes an UPDATE intermediate code and produces an array to be executed
     * later
     *
     * @return array
     * @throws Exception
     */
    final protected function prepareUpdate(): array
    {
        $ast = $this->ast;

        if (!isset($ast["update"])) {
            throw new CorruptedUpdateAst();
        }

        $update = $ast["update"];
        if (
            !isset($update["tables"]) ||
            !isset($update["values"])
        ) {
            throw new CorruptedUpdateAst();
        }

        $tables = $update["tables"];
        $values = $update["values"];

        /**
         * We use these arrays to store info related to models, alias and its
         * sources. With them we can rename columns later
         */
        $models                    = [];
        $modelsInstances           = [];
        $sqlTables                 = [];
        $sqlModels                 = [];
        $sqlAliases                = [];
        $sqlAliasesModels          = [];
        $sqlModelsAliases          = [];
        $sqlAliasesModelsInstances = [];

        if (!isset($tables[0])) {
            $updateTables = [$tables];
        } else {
            $updateTables = $tables;
        }

        $manager = $this->manager;

        foreach ($updateTables as $table) {
            $qualifiedName = $table["qualifiedName"];
            $modelName     = $qualifiedName["name"];

            /**
             * Load a model instance from the models manager
             */
            $model  = $manager->load($modelName);
            $source = $model->getSource();
            $schema = $model->getSchema();

            /**
             * Create a full source representation including schema
             */
            if ($schema) {
                $completeSource = [$source, $schema];
            } else {
                $completeSource = [$source, null];
            }

            /**
             * Check if the table is aliased
             */
            if (isset($table["alias"])) {
                $alias                             = $table["alias"];
                $sqlAliases[$alias]                = $alias;
                $sqlAliasesModels[$alias]          = $modelName;
                $sqlModelsAliases[$modelName]      = $alias;
                $completeSource[]                  = $alias;
                $sqlTables[]                       = $completeSource;
                $sqlAliasesModelsInstances[$alias] = $model;
                $models[$alias]                    = $modelName;
            } else {
                $sqlAliases[$modelName]                = $source;
                $sqlAliasesModels[$modelName]          = $modelName;
                $sqlModelsAliases[$modelName]          = $modelName;
                $sqlAliasesModelsInstances[$modelName] = $model;
                $sqlTables[]                           = $source;
                $models[$modelName]                    = $source;
            }

            $sqlModels[]                 = $modelName;
            $modelsInstances[$modelName] = $model;
        }

        /**
         * Update the models/alias/sources in the object
         */
        $this->models                    = $models;
        $this->modelsInstances           = $modelsInstances;
        $this->sqlAliases                = $sqlAliases;
        $this->sqlAliasesModels          = $sqlAliasesModels;
        $this->sqlModelsAliases          = $sqlModelsAliases;
        $this->sqlAliasesModelsInstances = $sqlAliasesModelsInstances;

        /**
         * Process the JOINs (if any) before resolving the SET and WHERE
         * expressions so that columns belonging to the joined models can be
         * resolved. The joined models are registered as aliases/sources but
         * are never added to "models", so the update still targets a single
         * model.
         */
        $sqlJoins = [];

        if (isset($update["joins"])) {
            $sqlJoins = $this->getJoins($update);
        }

        $sqlFields = [];
        $sqlValues = [];

        if (!isset($values[0])) {
            $updateValues = [$values];
        } else {
            $updateValues = $values;
        }

        $notQuoting = false;

        foreach ($updateValues as $updateValue) {
            $sqlFields[] = $this->getExpression($updateValue["column"], $notQuoting);
            $exprColumn  = $updateValue["expr"];
            $sqlValues[] = [
                "type"  => $exprColumn["type"],
                "value" => $this->getExpression($exprColumn, $notQuoting),
            ];
        }

        $sqlUpdate = [
            "tables" => $sqlTables,
            "models" => $sqlModels,
            "fields" => $sqlFields,
            "values" => $sqlValues,
        ];

        if (count($sqlJoins)) {
            $sqlUpdate["joins"] = $sqlJoins;
        }

        if (isset($ast["where"])) {
            $sqlUpdate["where"] = $this->getExpression($ast["where"]);
        }

        if (isset($ast["limit"])) {
            $sqlUpdate["limit"] = $this->getLimitClause($ast["limit"]);
        }

        return $sqlUpdate;
    }

    /**
     * Refreshes the schema/source of every model referenced in a cached
     * intermediate representation. The PHQL cache is keyed by the PHQL
     * string only, so a model that switches its schema or source at
     * runtime (for instance via setSchema()/setSource() in initialize())
     * would otherwise see the value frozen at first parse. See #17020.
     *
     * @param array $irPhql
     *
     * @return array
     */
    final protected function refreshSchemasInIntermediate(array $irPhql): array
    {
        $manager = $this->manager;

        if (!is_object($manager)) {
            return $irPhql;
        }

        if (!isset($irPhql["models"]) || !isset($irPhql["tables"])) {
            return $irPhql;
        }

        $models = $irPhql["models"];
        $tables = $irPhql["tables"];

        foreach ($models as $index => $modelName) {
            if (!isset($tables[$index])) {
                continue;
            }

            $model        = $manager->load($modelName);
            $schema       = $model->getSchema();
            $source       = $model->getSource();
            $currentTable = $tables[$index];
            $alias        = null;

            /**
             * Extract the alias from the cached entry (when present) so it
             * survives the rebuild. The cached shape is either a plain
             * source string, [source, schema], [source, null, alias] or
             * [source, schema, alias].
             */
            if (is_array($currentTable) && isset($currentTable[2])) {
                $alias = $currentTable[2];
            }

            if ($schema) {
                if ($alias !== null) {
                    $tables[$index] = [$source, $schema, $alias];
                } else {
                    $tables[$index] = [$source, $schema];
                }
            } else {
                if ($alias !== null) {
                    $tables[$index] = [$source, null, $alias];
                } else {
                    $tables[$index] = $source;
                }
            }
        }

        $irPhql["tables"] = $tables;

        return $irPhql;
    }

    /**
     * @param mixed $str
     *
     * @return mixed
     */
    private function ormSingleQuotes(mixed $str): mixed
    {
        // Check if input is a string
        if (!is_string($str)) {
            return $str;
        }

        // Replace unescaped single quotes with double single quotes
        $escaped = preg_replace("/(?<!\\\\)'/", "''", $str);

        return $escaped;
    }
}
