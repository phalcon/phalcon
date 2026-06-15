<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with the source code.
 */

declare(strict_types=1);

namespace Phalcon\Mvc;

use JsonSerializable;
use Phalcon\Contracts\Container\Service\Collection as ServiceCollection;
use Phalcon\Db\Adapter\AdapterInterface;
use Phalcon\Db\Column;
use Phalcon\Db\Enum;
use Phalcon\Db\Exceptions\InvalidWkb;
use Phalcon\Db\Geometry\WkbParser;
use Phalcon\Db\RawValue;
use Phalcon\Di\AbstractInjectionAware;
use Phalcon\Di\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Events\ManagerInterface as EventsManagerInterface;
use Phalcon\Filter\Validation\ValidationInterface;
use Phalcon\Messages\Message;
use Phalcon\Messages\MessageInterface;
use Phalcon\Mvc\Model\BehaviorInterface;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\Model\CriteriaInterface;
use Phalcon\Mvc\Model\Exception;
use Phalcon\Mvc\Model\Exceptions\BelongsToRequiresObject;
use Phalcon\Mvc\Model\Exceptions\BindTypeNotDefined;
use Phalcon\Mvc\Model\Exceptions\CannotResolveAttribute;
use Phalcon\Mvc\Model\Exceptions\ColumnNotInMap;
use Phalcon\Mvc\Model\Exceptions\ColumnNotInTableColumns;
use Phalcon\Mvc\Model\Exceptions\ColumnNotInTableMap;
use Phalcon\Mvc\Model\Exceptions\DataTypeNotDefined;
use Phalcon\Mvc\Model\Exceptions\IdentityNotInColumnMap;
use Phalcon\Mvc\Model\Exceptions\IdentityNotInTableColumns;
use Phalcon\Mvc\Model\Exceptions\InvalidDumpResultKey;
use Phalcon\Mvc\Model\Exceptions\InvalidFindParameters;
use Phalcon\Mvc\Model\Exceptions\InvalidModelsManagerService;
use Phalcon\Mvc\Model\Exceptions\InvalidModelsMetadataService;
use Phalcon\Mvc\Model\Exceptions\MethodNotFound;
use Phalcon\Mvc\Model\Exceptions\ModelOrmServicesUnavailable;
use Phalcon\Mvc\Model\Exceptions\PrimaryKeyAttributeNotSet;
use Phalcon\Mvc\Model\Exceptions\PrimaryKeyRequired;
use Phalcon\Mvc\Model\Exceptions\PropertyNotAccessible;
use Phalcon\Mvc\Model\Exceptions\RecordCannotRefresh;
use Phalcon\Mvc\Model\Exceptions\RecordNotPersisted;
use Phalcon\Mvc\Model\Exceptions\RelationNotDefined;
use Phalcon\Mvc\Model\Exceptions\RelationRequiresObjectOrArray;
use Phalcon\Mvc\Model\Exceptions\SnapshotsDisabled;
use Phalcon\Mvc\Model\Exceptions\StaticMethodRequiresOneArgument;
use Phalcon\Mvc\Model\Exceptions\UpdateSnapshotDisabled;
use Phalcon\Mvc\Model\ManagerInterface;
use Phalcon\Mvc\Model\MetaDataInterface;
use Phalcon\Mvc\Model\QueryInterface;
use Phalcon\Mvc\Model\Relation;
use Phalcon\Mvc\Model\ResultInterface;
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model\Resultset\Simple;
use Phalcon\Mvc\Model\ResultsetInterface;
use Phalcon\Mvc\Model\Row;
use Phalcon\Mvc\Model\TransactionInterface;
use Phalcon\Mvc\Model\ValidationFailed;
use Phalcon\Support\Collection;
use Phalcon\Support\Collection\CollectionInterface;
use Phalcon\Support\Settings;
use Phalcon\Traits\Helper\Str\CamelizeTrait;
use Phalcon\Traits\Helper\Str\UncamelizeTrait;
use Psr\EventDispatcher\StoppableEventInterface;
use Psr\Log\LoggerInterface;
use Serializable;
use Throwable;

use function array_intersect;
use function array_key_exists;
use function array_keys;
use function array_merge;
use function floatval;
use function get_called_class;
use function get_class;
use function get_object_vars;
use function implode;
use function in_array;
use function intval;
use function is_array;
use function is_bool;
use function is_numeric;
use function is_object;
use function is_string;
use function lcfirst;
use function method_exists;
use function property_exists;
use function serialize;
use function spl_object_id;
use function str_contains;
use function str_starts_with;
use function strtolower;
use function substr;
use function trigger_error;
use function unserialize;

/**
 * Phalcon\Mvc\Model connects business objects and database tables to create a
 * persistable domain model where logic and data are presented in one wrapping.
 * It‘s an implementation of the object-relational mapping (ORM).
 *
 * A model represents the information (data) of the application and the rules to
 * manipulate that data. Models are primarily used for managing the rules of
 * interaction with a corresponding database table. In most cases, each table in
 * your database will correspond to one model in your application. The bulk of
 * your application's business logic will be concentrated in the models.
 *
 * Phalcon\Mvc\Model is the first ORM written in Zephir/C languages for PHP,
 * giving to developers high performance when interacting with databases while
 * is also easy to use.
 *
 * ```php
 * $robot = new Robots();
 *
 * $robot->type = "mechanical";
 * $robot->name = "Astro Boy";
 * $robot->year = 1952;
 *
 * if ($robot->save() === false) {
 *     echo "Umh, We can store robots: ";
 *
 *     $messages = $robot->getMessages();
 *
 *     foreach ($messages as $message) {
 *         echo $message;
 *     }
 * } else {
 *     echo "Great, a new robot was saved successfully!";
 * }
 * ```
 *
 * @template T of static
 */
abstract class Model extends AbstractInjectionAware implements
    EntityInterface,
    ModelInterface,
    ResultInterface,
    Serializable,
    JsonSerializable
{
    use CamelizeTrait;
    use UncamelizeTrait;

    public const DIRTY_STATE_DETACHED   = 2;
    public const DIRTY_STATE_PERSISTENT = 0;
    public const DIRTY_STATE_TRANSIENT  = 1;
    public const OP_CREATE              = 1;
    public const OP_DELETE              = 3;
    public const OP_NONE                = 0;
    public const OP_UPDATE              = 2;
    public const TRANSACTION_INDEX      = "transaction";
    /**
     * @var array
     */
    protected array $dirtyRelated = [];
    /**
     * @var int
     */
    protected int $dirtyState = 1;
    /**
     * @var array
     */
    protected array $errorMessages = [];

    /**
     * @var ManagerInterface|null
     */
    protected ManagerInterface | null $modelsManager = null;

    /**
     * @var MetaDataInterface|null
     */
    protected MetaDataInterface | null $modelsMetaData = null;
    /**
     * @var array
     */
    protected array $oldSnapshot = [];
    /**
     * @var int
     */
    protected int $operationMade = 0;
    /**
     * @var array
     */
    protected array $rawValues = [];
    /**
     * @var array
     */
    protected array $related = [];
    /**
     * @var bool
     */
    protected bool $skipped = false;

    /**
     * @var array
     */
    protected array $snapshot = [];

    /**
     * Per-save many-to-many sync overrides, keyed by lowercased relation
     * alias (or "*" wildcard) => bool. Cleared after each save().
     *
     * @var array
     */
    protected array $syncRelated = [];

    /**
     * @var TransactionInterface|null
     */
    protected TransactionInterface | null $transaction = null;

    /**
     * @var string|null
     */
    protected string | null $uniqueKey = null;

    /**
     * @var array
     */
    protected array $uniqueParams = [];

    /**
     * @var array
     */
    protected array $uniqueTypes = [];

    /**
     * Phalcon\Mvc\Model constructor
     *
     * @param array|null                     $data
     * @param DiInterface|ServiceCollection|null $container
     * @param ManagerInterface|null          $modelsManager
     *
     * @throws Exception
     */
    final public function __construct(
        array | null $data = null,
        DiInterface | ServiceCollection | null $container = null,
        ManagerInterface | null $modelsManager = null
    ) {
        /**
         * We use a default DI if the user does not define one
         */
        if ($container === null) {
            $container = Di::getDefault();
        }

        if ($container === null) {
            throw new ModelOrmServicesUnavailable(get_class($this));
        }

        $this->container = $container;

        /**
         * Inject the manager service from the DI
         */
        if ($modelsManager === null) {
            $modelsManager = $container->get("modelsManager");
            if ($modelsManager === null) {
                throw new InvalidModelsManagerService(get_class($this));
            }
        }

        /**
         * Update the models-manager
         */
        $this->modelsManager = $modelsManager;

        /**
         * The manager always initializes the object
         */
        $modelsManager->initialize($this);

        /**
         * This allows the developer to execute initialization stuff every time
         * an instance is created
         */
        if (method_exists($this, "onConstruct")) {
            $this->onConstruct($data);
        }

        if (null !== $data) {
            $this->assign($data);
        }
    }

    /**
     * Handles method calls when a method is not implemented
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return bool|int|mixed|Simple|ModelInterface|ModelInterface[]|null
     * @throws Exception
     */
    public function __call(string $method, array $arguments)
    {
        $records = self::invokeFinder($method, $arguments);

        if ($records !== false) {
            return $records;
        }

        $modelName = get_class($this);

        /**
         * Check if there is a default action using the magic getter
         */
        $records = $this->getRelatedRecords($modelName, $method, $arguments);

        if ($records !== false) {
            return $records;
        }

        /**
         * Try to find a replacement for the missing method in a
         * behavior/listener
         */
        $status = $this->modelsManager->missingMethod($this, $method, $arguments);

        if ($status !== null) {
            return $status;
        }

        /**
         * The method does not exist throw an exception
         */
        throw new MethodNotFound($method, $modelName);
    }

    /**
     * Handles method calls when a static method is not implemented
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return bool|ModelInterface|ModelInterface[]|null
     * @throws Exception
     */
    public static function __callStatic(string $method, array $arguments)
    {
        $records = self::invokeFinder($method, $arguments);

        if ($records !== false) {
            return $records;
        }

        $modelName = get_called_class();

        /**
         * The method does not exist throw an exception
         */
        throw new MethodNotFound($method, $modelName);
    }

    /**
     * Magic method to get related records using the relation alias as a
     * property
     *
     * @param string $property
     *
     * @return mixed|null
     * @throws Exception
     */
    public function __get(string $property)
    {
        $modelName     = get_class($this);
        $manager       = $this->getModelsManager();
        $lowerProperty = strtolower($property);

        /**
         * Check if the property is a relationship
         */
        $relation = $manager->getRelationByAlias(
            $modelName,
            $lowerProperty
        );

        if (is_object($relation)) {
            /**
             * There might be unsaved related records that can be returned
             */
            if (isset($this->dirtyRelated[$lowerProperty])) {
                return $this->dirtyRelated[$lowerProperty];
            }

            /**
             * Return an already-loaded single model for non-reusable relations to
             * avoid overwriting modifications made to the object between accesses.
             * Resultsets (hasMany) are never returned from this cache because they
             * can go stale after external deletes; reusable relations delegate
             * caching to the models manager.
             */
            if (
                isset($this->related[$lowerProperty]) &&
                !$relation->isReusable()
            ) {
                if (
                    is_object($this->related[$lowerProperty]) &&
                    $this->related[$lowerProperty] instanceof ModelInterface
                ) {
                    return $this->related[$lowerProperty];
                }
            }

            /**
             * Get the related records
             */
            return $this->getRelated($lowerProperty);
        }

        /**
         * Check if the property has getters
         */
        $method = "get" . $this->toCamelize($property);

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        /**
         * A notice is shown if the property is not defined and it isn't a
         * relationship
         */
        trigger_error(
            "Access to undefined property "
            . $modelName . "::"
            . $property
        );

        return null;
    }

    /**
     * Magic method to check if a property is a valid relation
     *
     * @param string $property
     *
     * @return bool
     */
    public function __isset(string $property): bool
    {
        $modelName = get_class($this);
        $manager   = $this->getModelsManager();

        /**
         * Check if the property is a relationship
         */
        $relation = $manager->getRelationByAlias(
            $modelName,
            $property
        );

        if (is_object($relation)) {
            $result = true;
        } else {
            // If this is a property
            $method = "get" . $this->toCamelize($property);

            $result = method_exists($this, $method);
        }

        return $result;
    }

    /**
     * Serializes a model
     *
     * @return array
     * @throws Exception
     */
    public function __serialize(): array
    {
        /**
         * Use the standard serialize function to serialize the array data
         */
        $snapshot   = null;
        $attributes = $this->toArray(null, false);
        $dirtyState = $this->dirtyState;
        $manager    = $this->getModelsManager();

        if (
            $manager->isKeepingSnapshots($this) &&
            $this->snapshot !== null &&
            $attributes != $this->snapshot
        ) {
            $snapshot = $this->snapshot;
        }

        return [
            "attributes" => $attributes,
            "snapshot"   => $snapshot,
            "dirtyState" => $dirtyState,
        ];
    }

    /**
     * Magic method to assign values to the the model
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return array|mixed|ModelInterface
     * @throws Exception
     */
    public function __set(string $property, mixed $value)
    {
        /**
         * Values are probably relationships if they are objects
         */
        if (is_object($value) && $value instanceof ModelInterface) {
            $lowerProperty = strtolower($property);
            $modelName     = get_class($this);
            $manager       = $this->getModelsManager();
            $relation      = $manager->getRelationByAlias(
                $modelName,
                $lowerProperty
            );

            if (is_object($relation)) {
                $dirtyState = $this->dirtyState;

                if ($value->getDirtyState() != $dirtyState) {
                    $dirtyState = self::DIRTY_STATE_TRANSIENT;
                }

                unset($this->related[$lowerProperty]);

                $this->dirtyRelated[$lowerProperty] = $value;
                $this->dirtyState                   = $dirtyState;

                return $value;
            }
        } elseif (is_array($value)) {
            /**
             * Check if the value is an array
             */
            $lowerProperty = strtolower($property);
            $modelName     = get_class($this);
            $manager       = $this->getModelsManager();
            $relation      = $manager->getRelationByAlias(
                $modelName,
                $lowerProperty
            );

            if (is_object($relation)) {
                switch ($relation->getType()) {
                    case Relation::BELONGS_TO:
                    case Relation::HAS_ONE:
                        /**
                         * Load referenced model from local cache if its possible
                         */
                        $referencedModel = $manager->load(
                            $relation->getReferencedModel()
                        );

                        if (is_object($referencedModel)) {
                            $referencedModel->assign($value);

                            unset($this->related[$lowerProperty]);

                            $this->dirtyRelated[$lowerProperty] = $referencedModel;
                            $this->dirtyState                   = self::DIRTY_STATE_TRANSIENT;

                            return $value;
                        }

                        break;

                    case Relation::HAS_MANY:
                    case Relation::HAS_MANY_THROUGH:
                        $related = [];

                        foreach ($value as $item) {
                            if (
                                is_object($item) &&
                                $item instanceof ModelInterface
                            ) {
                                $related[] = $item;
                            }
                        }

                        unset($this->related[$lowerProperty]);

                        if (
                            !empty($related) ||
                            $relation->getType() === Relation::HAS_MANY_THROUGH
                        ) {
                            $this->dirtyRelated[$lowerProperty] = $related;
                            $this->dirtyState                   = self::DIRTY_STATE_TRANSIENT;
                        } else {
                            unset($this->dirtyRelated[$lowerProperty]);
                        }

                        return $value;
                }
            }
        } elseif ($value === null) {
            /**
             * Null assigned to a relationship alias must clear the cached
             * related records so that preSaveRelatedRecords() does not
             * overwrite the FK back to its old value during save().
             *
             * Pre-assigning $this->$property = null before calling
             * possibleSetter prevents infinite recursion: once the property
             * exists as a real (dynamic) property, any subsequent
             * `$this->property = null` inside the user-defined setter will
             * not re-enter __set.
             */
            $lowerProperty = strtolower($property);
            $modelName     = get_class($this);
            $manager       = $this->getModelsManager();
            $relation      = $manager->getRelationByAlias(
                $modelName,
                $lowerProperty
            );

            if (is_object($relation)) {
                unset($this->related[$lowerProperty]);
                unset($this->dirtyRelated[$lowerProperty]);

                $this->$property = null;
            }
        }

        // Use possible setter.
        if ($this->possibleSetter($property, $value)) {
            return $value;
        }

        /**
         * Throw an exception if there is an attempt to set a non-public
         * property.
         */
        if (property_exists($this, $property)) {
            $manager = $this->getModelsManager();

            if (!$manager->isVisibleModelProperty($this, $property)) {
                throw new PropertyNotAccessible($property, get_class($this));
            }
        }

        $this->$property = $value;

        return $value;
    }

    /**
     * Unserializes an array to the model
     *
     * @param array $data
     *
     * @return void
     * @throws Exception
     */
    public function __unserialize(array $data): void
    {
        if (!isset($data["attributes"])) {
            $data = [
                "attributes" => $data,
            ];
        }

        /**
         * Obtain the default DI
         */
        $container = Di::getDefault();
        if ($container === null) {
            throw new ModelOrmServicesUnavailable(get_class($this));
        }

        /**
         * Update the dependency injector
         */
        $this->container = $container;

        /**
         * Gets the default modelsManager service
         */
        $manager = $container->get("modelsManager");
        if ($manager === null) {
            throw new InvalidModelsManagerService(get_class($this));
        }

        /**
         * Update the models manager
         */
        $this->modelsManager = $manager;

        /**
         * Try to initialize the model
         */
        $manager->initialize($this);

        /**
         * Allow the developer to run initialization code every time
         * the model is instantiated, including when restored from cache
         */
        if (method_exists($this, "onConstruct")) {
            $this->onConstruct();
        }

        /**
         * Fetch serialized props
         */
        $properties = [];
        if (isset($data["attributes"])) {
            $properties = $data["attributes"];
            /**
             * Update the objects properties
             */
            foreach ($properties as $key => $value) {
                $this->$key = $value;
            }
        }

        /**
         * Fetch serialized dirtyState
         */
        if (isset($data["dirtyState"])) {
            $this->dirtyState = $data["dirtyState"];
        }

        /**
         * Fetch serialized snapshot when option is active
         */
        if ($manager->isKeepingSnapshots($this)) {
            $this->snapshot = $data["snapshot"] ?? $properties;
        }
    }

    /**
     * Setups a behavior in a model
     *
     *```php
     * use Phalcon\Mvc\Model;
     * use Phalcon\Mvc\Model\Behavior\Timestampable;
     *
     * class Robots extends Model
     * {
     *     public function initialize()
     *     {
     *         $this->addBehavior(
     *             new Timestampable(
     *                 [
     *                     "beforeCreate" => [
     *                         "field"  => "created_at",
     *                         "format" => "Y-m-d",
     *                     ],
     *                 ]
     *             )
     *         );
     *
     *         $this->addBehavior(
     *             new Timestampable(
     *                 [
     *                     "beforeUpdate" => [
     *                         "field"  => "updated_at",
     *                         "format" => "Y-m-d",
     *                     ],
     *                 ]
     *             )
     *         );
     *     }
     * }
     *```
     *
     * @param BehaviorInterface $behavior
     *
     * @return void
     */
    public function addBehavior(BehaviorInterface $behavior): void
    {
        $this->modelsManager->addBehavior($this, $behavior);
    }

    /**
     * Appends a customized message on the validation process
     *
     * ```php
     * use Phalcon\Mvc\Model;
     * use Phalcon\Messages\Message as Message;
     *
     * class Robots extends Model
     * {
     *     public function beforeSave()
     *     {
     *         if ($this->name === "Peter") {
     *             $message = new Message(
     *                 "Sorry, but a robot cannot be named Peter"
     *             );
     *
     *             $this->appendMessage($message);
     *         }
     *     }
     * }
     * ```
     *
     * @param MessageInterface $message
     *
     * @return ModelInterface
     */
    public function appendMessage(MessageInterface $message): ModelInterface
    {
        $this->errorMessages[] = $message;

        return $this;
    }

    /***
     * Append messages to this model from another Model.
     *
     * @param ModelInterface $model
     *
     * @return void
     */
    public function appendMessagesFrom(ModelInterface $model): void
    {
        $messages = $model->getMessages();
        if (!empty($messages)) {
            foreach ($messages as $message) {
                if (is_object($message)) {
                    $message->setMetaData(
                        [
                            "model" => $model,
                        ]
                    );
                }
                /**
                 * Appends the messages to the current model
                 */
                $this->appendMessage($message);
            }
        }
    }

    /**
     * Assigns values to a model from an array
     *
     * ```php
     * $robot->assign(
     *     [
     *         "type" => "mechanical",
     *         "name" => "Astro Boy",
     *         "year" => 1952,
     *     ]
     * );
     *
     * // Assign by db row, column map needed
     * $robot->assign(
     *     $dbRow,
     *     [
     *         "db_type" => "type",
     *         "db_name" => "name",
     *         "db_year" => "year",
     *     ]
     * );
     *
     * // Allow assign only name and year
     * $robot->assign(
     *     $_POST,
     *     [
     *         "name",
     *         "year",
     *     ]
     * );
     *
     * // By default assign method will use setters if exist, you can disable
     * // it by using ini_set to directly use properties
     *
     * ini_set("orm.disable_assign_setters", true);
     *
     * $robot->assign(
     *     $_POST,
     *     [
     *         "name",
     *         "year",
     *     ]
     * );
     * ```
     *
     * @param array      $data
     * @param mixed|null $whiteList
     * @param mixed|null $dataColumnMap
     *
     * @return ModelInterface
     * @throws Exception
     */
    public function assign(
        array $data,
        mixed $whiteList = null,
        mixed $dataColumnMap = null
    ): ModelInterface {
        $rawValues       = [];
        $this->rawValues = $rawValues;

        $disableAssignSetters = Settings::get("orm.disable_assign_setters");

        // apply column map for data, if exist
        if (is_array($dataColumnMap)) {
            $dataMapped = [];

            foreach ($data as $key => $value) {
                if (isset($dataColumnMap[$key])) {
                    $dataMapped[$dataColumnMap[$key]] = $value;
                }
            }
        } else {
            $dataMapped = $data;
        }

        if (empty($dataMapped)) {
            return $this;
        }

        $metaData  = $this->getModelsMetaData();
        $columnMap = null;
        if (Settings::get("orm.column_renaming")) {
            $columnMap = $metaData->getColumnMap($this);
        }

        foreach ($metaData->getAttributes($this) as $attribute) {
            // Try to find case-insensitive key variant
            if (
                !isset($columnMap[$attribute]) &&
                Settings::get("orm.case_insensitive_column_map")
            ) {
                $attribute = self::caseInsensitiveColumnMap(
                    $columnMap,
                    $attribute
                );
            }

            // Check if we need to rename the field
            if (is_array($columnMap)) {
                if (!isset($columnMap[$attribute])) {
                    if (!Settings::get("orm.ignore_unknown_columns")) {
                        throw new ColumnNotInMap($attribute, get_class($this));
                    }

                    continue;
                } else {
                    $attributeField = $columnMap[$attribute];
                }
            } else {
                $attributeField = $attribute;
            }

            // The value in the array passed
            // Check if we there is data for the field
            if (isset($dataMapped[$attributeField])) {
                $value = $dataMapped[$attributeField];
                // If white-list exists check if the attribute is on that list
                if (is_array($whiteList)) {
                    if (!in_array($attributeField, $whiteList)) {
                        continue;
                    }
                }

                // Try to find a possible getter
                if (is_object($value) && $value instanceof RawValue) {
                    $rawValues[$attributeField] = $value;
                } elseif (
                    $disableAssignSetters ||
                    !$this->possibleSetter($attributeField, $value)
                ) {
                    $this->$attributeField = $value;
                }
            }
        }

        $this->rawValues = $rawValues;

        return $this;
    }

    /**
     * Returns the average value on a column for a result-set of rows matching
     * the specified conditions.
     *
     * Returned value will be a float for simple queries or a ResultsetInterface
     * instance for when the GROUP condition is used. The results will
     * contain the average of each group.
     *
     * ```php
     * // What's the average price of robots?
     * $average = Robots::average(
     *     [
     *         "column" => "price",
     *     ]
     * );
     *
     * echo "The average price is ", $average, "\n";
     *
     * // What's the average price of mechanical robots?
     * $average = Robots::average(
     *     [
     *         "type = 'mechanical'",
     *         "column" => "price",
     *     ]
     * );
     *
     * echo "The average price of mechanical robots is ", $average, "\n";
     * ```
     *
     * @param array $parameters
     *
     * @return float|ResultsetInterface
     */
    public static function average(
        array $parameters = []
    ): float | ResultsetInterface {
        $result = self::groupResult("AVG", "average", $parameters);

        if (is_string($result)) {
            return (float)$result;
        }

        return $result;
    }

    /**
     * Assigns values to a model from an array returning a new model
     *
     *```php
     * $robot = Phalcon\Mvc\Model::cloneResult(
     *     new Robots(),
     *     [
     *         "type" => "mechanical",
     *         "name" => "Astro Boy",
     *         "year" => 1952,
     *     ]
     * );
     *```
     *
     * @param ModelInterface $base
     * @param array          $data
     * @param int            $dirtyState
     *
     * @return ModelInterface
     * @throws Exception
     */
    public static function cloneResult(
        ModelInterface $base,
        array $data,
        int $dirtyState = 0
    ): ModelInterface {
        /**
         * Clone the base record
         */
        $instance = clone $base;

        /**
         * Mark the object as persistent
         */
        $instance->setDirtyState($dirtyState);

        foreach ($data as $key => $value) {
            if (!is_string($key)) {
                throw new InvalidDumpResultKey(get_class($base));
            }

            $instance->$key = $value;
        }

        /**
         * Call afterFetch, this allows the developer to execute actions after a
         * record is fetched from the database
         */
        $instance->fireEvent("afterFetch");

        return $instance;
    }

    /**
     * Assigns values to a model from an array, returning a new model.
     *
     *```php
     * $robot = \Phalcon\Mvc\Model::cloneResultMap(
     *     new Robots(),
     *     [
     *         "type" => "mechanical",
     *         "name" => "Astro Boy",
     *         "year" => 1952,
     *     ]
     * );
     *```
     *
     * @param mixed     $base
     * @param array     $data
     * @param mixed     $columnMap
     * @param int       $dirtyState
     * @param bool|null $keepSnapshots
     *
     * @return ModelInterface|ResultInterface
     * @throws Exception
     */
    public static function cloneResultMap(
        mixed $base,
        array $data,
        mixed $columnMap,
        int $dirtyState = 0,
        bool | null $keepSnapshots = null
    ): ModelInterface | ResultInterface {
        $instance = clone $base;

        if ($instance instanceof Model) {
            $metaData          = $instance->getModelsMetaData();
            $notNullAttributes = $metaData->getNotNullAttributes($instance);
        } else {
            $metaData          = null;
            $notNullAttributes = [];
        }

        // Change the dirty state to persistent
        $instance->setDirtyState($dirtyState);

        $disableSetters = (bool) Settings::get("orm.disable_assign_setters");

        $localMethods = [
            "setConnectionService"      => 1,
            "setDirtyState"             => 1,
            "setEventsManager"          => 1,
            "setReadConnectionService"  => 1,
            "setOldSnapshotData"        => 1,
            "setSchema"                 => 1,
            "setSnapshotData"           => 1,
            "setSource"                 => 1,
            "setTransaction"            => 1,
            "setWriteConnectionService" => 1,
        ];

        /**
         * Assign the data in the model
         */
        foreach ($data as $key => $value) {
            // Only string keys in the data are valid
            if (!is_string($key)) {
                continue;
            }

            if ($value === null && in_array($key, $notNullAttributes)) {
                continue;
            }

            if (!is_array($columnMap)) {
                if (!$disableSetters) {
                    $setter = "set" . self::staticToCamelize($key);
                    if (
                        method_exists($instance, $setter) &&
                        !isset($localMethods[$setter])
                    ) {
                        try {
                            $instance->$setter($value);
                        } catch (\TypeError) {
                            self::assignCoercedValue($instance, $key, $value);
                        }

                        continue;
                    }
                }

                self::assignCoercedValue($instance, $key, $value);

                continue;
            }

            // Every field must be part of the column map
            if (!isset($columnMap[$key])) {
                if (is_array($columnMap) && !empty($columnMap)) {
                    if ($metaData === null) {
                        $metaData = $instance->getModelsMetaData();
                    }

                    $reverseMap = $metaData->getReverseColumnMap($instance);
                    if (!isset($reverseMap[$key])) {
                        if (!Settings::get("orm.ignore_unknown_columns")) {
                            throw new ColumnNotInMap($key, get_class($base));
                        }

                        continue;
                    }

                    $attribute = $reverseMap[$key];
                } else {
                    if (!Settings::get("orm.ignore_unknown_columns")) {
                        throw new ColumnNotInMap($key, get_class($base));
                    }

                    continue;
                }
            } else {
                $attribute = $columnMap[$key];
            }

            if (!is_array($attribute)) {
                if (!$disableSetters) {
                    $setter = "set" . self::staticToCamelize($attribute);
                    if (
                        method_exists($instance, $setter) &&
                        !isset($localMethods[$setter])
                    ) {
                        try {
                            $instance->$setter($value);
                        } catch (\TypeError) {
                            self::assignCoercedValue(
                                $instance,
                                $attribute,
                                $value
                            );
                        }

                        continue;
                    }
                }

                self::assignCoercedValue($instance, $attribute, $value);

                continue;
            }

            if ($value != "" && $value !== null) {
                $castValue = match ($attribute[1]) {
                    Column::TYPE_INTEGER,
                    Column::TYPE_MEDIUMINTEGER,
                    Column::TYPE_SMALLINTEGER,
                    Column::TYPE_TINYINTEGER => intval($value),
                    Column::TYPE_DECIMAL,
                    Column::TYPE_DOUBLE,
                    Column::TYPE_FLOAT       => (float)$value,
                    Column::TYPE_BOOLEAN     => (bool)$value,
                    Column::TYPE_GEOMETRY,
                    Column::TYPE_POINT,
                    Column::TYPE_LINESTRING,
                    Column::TYPE_POLYGON,
                    Column::TYPE_MULTIPOINT,
                    Column::TYPE_MULTILINESTRING,
                    Column::TYPE_MULTIPOLYGON,
                    Column::TYPE_GEOMETRYCOLLECTION => self::castSpatial($value),
                    default                  => $value,
                };
            } else {
                $castValue = match ($attribute[1]) {
                    Column::TYPE_BIGINTEGER,
                    Column::TYPE_BOOLEAN,
                    Column::TYPE_DECIMAL,
                    Column::TYPE_DOUBLE,
                    Column::TYPE_FLOAT,
                    Column::TYPE_INTEGER,
                    Column::TYPE_MEDIUMINTEGER,
                    Column::TYPE_SMALLINTEGER,
                    Column::TYPE_TINYINTEGER => null,
                    default                  => $value,
                };
            }

            $attributeName = $attribute[0];
            $data[$key]    = $castValue;

            if (!$disableSetters) {
                $setter = "set" . self::staticToCamelize($attributeName);
                if (
                    method_exists($instance, $setter) &&
                    !isset($localMethods[$setter])
                ) {
                    try {
                        $instance->$setter($castValue);
                    } catch (\TypeError) {
                        $instance->$attributeName = $castValue;
                    }

                    continue;
                }
            }

            $instance->$attributeName = $castValue;
        }

        /**
         * Models that keep snapshots store the original data in t.
         * At hydration both snapshot and oldSnapshot are the same
         * "as fetched" baseline - reuse the column-mapped result of
         * setSnapshotData() instead of running the column-map walk
         * a second time. PHP's COW lets the two refs share memory
         * until one is mutated by save().
         */
        if ($keepSnapshots) {
            $instance->setSnapshotData($data, $columnMap);
            $instance->setOldSnapshotData($instance->getSnapshotData());
        }

        /**
         * Call afterFetch, this allows the developer to execute actions after a
         * record is fetched from the database
         */
        if (method_exists($instance, "fireEvent")) {
            $instance->fireEvent("afterFetch");
        }

        return $instance;
    }

    private static function castSpatial(mixed $value): mixed
    {
        try {
            return (new WkbParser())->parse((string) $value);
        } catch (InvalidWkb) {
            return $value;
        }
    }

    /**
     * Returns an hydrated result based on the data and the column map
     *
     * @param array $data
     * @param mixed $columnMap
     * @param int   $hydrationMode
     *
     * @return array|mixed|object
     * @throws Exception
     */
    public static function cloneResultMapHydrate(
        array $data,
        mixed $columnMap,
        int $hydrationMode
    ) {
        /**
         * If there is no column map and the hydration mode is arrays return the
         * data as it is
         */
        if (!is_array($columnMap) && $hydrationMode == Resultset::HYDRATE_ARRAYS) {
            return $data;
        }

        /**
         * Create the destination object
         */
        $hydrateArray = [];

        foreach ($data as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            if (is_array($columnMap)) {
                // Try to find case-insensitive key variant
                if (
                    !isset($columnMap[$key]) &&
                    Settings::get("orm.case_insensitive_column_map")
                ) {
                    $key = self::caseInsensitiveColumnMap($columnMap, $key);
                }

                /**
                 * Every field must be part of the column map
                 */
                if (!isset($columnMap[$key])) {
                    if (!Settings::get("orm.ignore_unknown_columns")) {
                        /**
                         * @todo unless we pass the model name in the function
                         *       we cannot tell what model has this problem
                         */
                        throw new ColumnNotInMap($key, get_called_class());
                    }

                    continue;
                } else {
                    $attribute = $columnMap[$key];
                }

                /**
                 * Attribute can store info about his type
                 */
                if (is_array($attribute)) {
                    $attributeName = $attribute[0];
                } else {
                    $attributeName = $attribute;
                }

                $hydrateArray[$attributeName] = $value;
            } else {
                $hydrateArray[$key] = $value;
            }
        }

        if ($hydrationMode != Resultset::HYDRATE_ARRAYS) {
            return (object)$hydrateArray;
        }

        return $hydrateArray;
    }

    /**
     * Counts how many records match the specified conditions.
     *
     * Returns an integer for simple queries or a ResultsetInterface
     * instance for when the GROUP condition is used. The results will
     * contain the count of each group.
     *
     * ```php
     * // How many robots are there?
     * $number = Robots::count();
     *
     * echo "There are ", $number, "\n";
     *
     * // How many mechanical robots are there?
     * $number = Robots::count("type = 'mechanical'");
     *
     * echo "There are ", $number, " mechanical robots\n";
     * ```
     *
     * @param array|string|null $parameters
     *
     * @return int|ResultsetInterface
     */
    public static function count(
        mixed $parameters = null
    ): int | ResultsetInterface {
        /**
         * Removing `order by` for postgresql
         */
        if (isset($parameters["order"])) {
            unset($parameters["order"]);
        }

        $result = self::groupResult("COUNT", "rowcount", $parameters);

        if (is_string($result)) {
            return (int)$result;
        }

        return $result;
    }

    /**
     * Inserts a model instance. If the instance already exists in the
     * persistence it will throw an exception
     * Returning true on success or false otherwise.
     *
     *```php
     * // Creating a new robot
     * $robot = new Robots();
     *
     * $robot->type = "mechanical";
     * $robot->name = "Astro Boy";
     * $robot->year = 1952;
     *
     * $robot->create();
     *
     * // Passing an array to create
     * $robot = new Robots();
     *
     * $robot->assign(
     *     [
     *         "type" => "mechanical",
     *         "name" => "Astro Boy",
     *         "year" => 1952,
     *     ]
     * );
     *
     * $robot->create();
     *```
     *
     * @return bool
     * @throws Exception
     */
    public function create(): bool
    {
        $metaData = $this->getModelsMetaData();

        /**
         * Get the current connection use write to prevent replica lag
         * If the record already exists we must throw an exception
         */
        if ($this->has($metaData, $this->getWriteConnection())) {
            $this->errorMessages = [
                new Message(
                    "Record cannot be created because it already exists",
                    '',
                    "InvalidCreateAttempt",
                    0,
                    [
                        "model" => get_class($this),
                    ]
                ),
            ];

            return false;
        }

        /**
         * Using save() anyways
         */
        return $this->save();
    }

    /**
     * Deletes a model instance. Returning true on success or false otherwise.
     *
     * ```php
     * $robot = Robots::findFirst("id=100");
     *
     * $robot->delete();
     *
     * $robots = Robots::find("type = 'mechanical'");
     *
     * foreach ($robots as $robot) {
     *     $robot->delete();
     * }
     * ```
     *
     * @return bool
     * @throws Exception
     */
    public function delete(): bool
    {
        $metaData        = $this->getModelsMetaData();
        $writeConnection = $this->getWriteConnection();

        /**
         * Operation made is OP_DELETE
         */
        $this->operationMade = self::OP_DELETE;
        $this->errorMessages = [];

        /**
         * Check if deleting the record violates a virtual foreign key
         */
        if (
            Settings::get("orm.virtual_foreign_keys") &&
            $this->checkForeignKeysReverseRestrict() === false
        ) {
            return false;
        }

        $values     = [];
        $bindTypes  = [];
        $conditions = [];

        $primaryKeys   = $metaData->getPrimaryKeyAttributes($this);
        $bindDataTypes = $metaData->getBindTypes($this);
        $columnMap     = null;

        if (Settings::get("orm.column_renaming")) {
            $columnMap = $metaData->getColumnMap($this);
        }

        /**
         * We can't create dynamic SQL without a primary key
         */
        if (!count($primaryKeys)) {
            throw new PrimaryKeyRequired(get_class($this));
        }

        /**
         * Create a condition from the primary keys
         */
        foreach ($primaryKeys as $primaryKey) {
            /**
             * Every column part of the primary key must be in the bind data
             * types
             */
            if (!isset($bindDataTypes[$primaryKey])) {
                throw new BindTypeNotDefined($primaryKey, get_class($this));
            }

            $bindType = $bindDataTypes[$primaryKey];

            /**
             * Take the column values based on the column map if any
             */
            if (is_array($columnMap)) {
                if (!isset($columnMap[$primaryKey])) {
                    throw new ColumnNotInTableMap($primaryKey, get_class($this));
                }
                $attributeField = $columnMap[$primaryKey];
            } else {
                $attributeField = $primaryKey;
            }

            /**
             * If the attribute is currently set in the object add it to the
             * conditions
             */
            if (!property_exists($this, $attributeField)) {
                throw new PrimaryKeyAttributeNotSet($attributeField, get_class($this));
            }

            $value = $this->$attributeField;
            /**
             * Escape the column identifier
             */
            $values[]     = $value;
            $conditions[] = $writeConnection->escapeIdentifier($primaryKey) . " = ?";
            $bindTypes[]  = $bindType;
        }

        if (Settings::get("orm.events")) {
            $this->skipped = false;

            /**
             * Fire the beforeDelete event
             */
            if ($this->fireEventCancel("beforeDelete") === false) {
                return false;
            }

            /**
             * The operation can be skipped
             */
            if ($this->skipped === true) {
                return true;
            }
        }

        $schema = $this->getSchema();
        $source = $this->getSource();

        if ($schema) {
            $table = [$schema, $source];
        } else {
            $table = $source;
        }

        /**
         * Join the conditions in the array using an AND operator
         * Do the deletion
         */
        $success = $writeConnection->delete(
            $table,
            implode(" AND ", $conditions),
            $values,
            $bindTypes
        );

        /**
         * Check if there is virtual foreign keys with cascade action
         */
        if (
            Settings::get("orm.virtual_foreign_keys") &&
            $this->checkForeignKeysReverseCascade() === false
        ) {
            return false;
        }

        if (Settings::get("orm.events") && $success) {
            $this->fireEvent("afterDelete");
        }

        /**
         * Clear related records from the internal cache so that next time
         * we can get proper counts.
         */
        if ($success) {
            $this->related      = [];
            $this->dirtyRelated = [];
            $this->modelsManager->clearReusableObjects();
        }

        /**
         * Force perform the record existence checking again
         */
        $this->dirtyState = self::DIRTY_STATE_DETACHED;

        return $success;
    }

    /**
     * Inserted or updates model instance, expects a visited list of objects.
     *
     * @param CollectionInterface $visited
     *
     * @return bool
     * @throws Exception
     * @throws ValidationFailed
     */
    public function doSave(CollectionInterface $visited): bool
    {
        $objId = (string)spl_object_id($this);

        if (true === $visited->has($objId)) {
            return true;
        }

        $visited->set($objId, $this);

        $metaData = $this->getModelsMetaData();

        /**
         * Create/Get the current database connection
         */
        $writeConnection = $this->getWriteConnection();

        /**
         * Fire the start event
         */
        $this->fireEvent("prepareSave");

        /**
         * Load unsaved related records and collect
         * previously queried related records that
         * may have been modified
         */
        $relatedToSave = $this->collectRelatedToSave();

        /**
         * Does it have unsaved related records
         */
        $hasRelatedToSave = count($relatedToSave) > 0;

        if (
            $hasRelatedToSave &&
            $this->preSaveRelatedRecords($writeConnection, $relatedToSave, $visited) === false
        ) {
            $this->syncRelated = [];

            return false;
        }

        $schema = $this->getSchema();
        $source = $this->getSource();

        if ($schema) {
            $table = [$schema, $source];
        } else {
            $table = $source;
        }

        /**
         * We need to check if the record exists. Use the write connection
         * to prevent replica lag
         */
        $exists = $this->has($metaData, $writeConnection);

        if ($exists) {
            $this->operationMade = self::OP_UPDATE;
        } else {
            $this->operationMade = self::OP_CREATE;
        }

        /**
         * Clean the messages
         */
        $this->errorMessages = [];

        /**
         * Query the identity field
         */
        $identityField = $metaData->getIdentityField($this);

        /**
         * preSave() makes all the validations
         */
        if ($this->preSave($metaData, $exists, $identityField) === false) {
            /**
             * Rollback the current transaction if there was validation errors
             */
            if ($hasRelatedToSave) {
                $writeConnection->rollback(false);
            }

            $this->syncRelated = [];

            /**
             * Throw exceptions on failed saves?
             */
            if (Settings::get("orm.exception_on_failed_save")) {
                /**
                 * Launch a Phalcon\Mvc\Model\ValidationFailed to notify that
                 * the save failed
                 */
                throw new ValidationFailed(
                    $this,
                    $this->getMessages()
                );
            }

            return false;
        }

        /**
         * Capture the current snapshot before the write so it can be restored
         * if postSaveRelatedRecords later rolls back the transaction
         */
        $manager          = $this->getModelsManager();
        $savedSnapshot    = $this->snapshot;
        $savedOldSnapshot = $this->oldSnapshot;

        /**
         * Depending if the record exists we do an update or an insert operation
         */
        if ($exists) {
            $success = $this->doLowUpdate($metaData, $writeConnection, $table);
        } else {
            $success = $this->doLowInsert(
                $metaData,
                $writeConnection,
                $table,
                $identityField
            );
        }

        /**
         * Change the dirty state to persistent
         */
        if (true === $success) {
            $this->dirtyState = self::DIRTY_STATE_PERSISTENT;
        }

        if ($hasRelatedToSave) {
            /**
             * Rollbacks the implicit transaction if the master save has failed
             */
            if ($success === false) {
                $writeConnection->rollback(false);
            } else {
                /**
                 * Save the post-related records
                 */
                $success = $this->postSaveRelatedRecords(
                    $writeConnection,
                    $relatedToSave,
                    $visited
                );
            }
        }

        /**
         * postSave() invokes after* events if the operation was successful
         */
        if (Settings::get("orm.events")) {
            $success = $this->postSave($success, $exists);
        }

        if ($success === false) {
            $this->cancelOperation();

            /**
             * If the transaction was rolled back, restore the snapshot to its
             * pre-save state so that Dynamic Update can detect changes correctly
             * on the next save attempt
             */
            if (
                $manager->isKeepingSnapshots($this) &&
                Settings::get("orm.update_snapshot_on_save")
            ) {
                $this->snapshot    = $savedSnapshot;
                $this->oldSnapshot = $savedOldSnapshot;
            }
        } else {
            if ($hasRelatedToSave) {
                /**
                 * Clear unsaved related records storage
                 */
                $this->dirtyRelated = [];
            }

            $this->related = [];
            $this->modelsManager->clearReusableObjects();
            $this->fireEvent("afterSave");
        }

        $this->syncRelated = [];

        return $success;
    }

    /**
     * Returns a simple representation of the object that can be used with
     * `var_dump()`
     *
     *```php
     * var_dump(
     *     $robot->dump()
     * );
     *```
     *
     * @return array
     */
    public function dump(): array
    {
        return get_object_vars($this);
    }

    /**
     * Query for a set of records that match the specified conditions
     *
     * ```php
     * // How many robots are there?
     * $robots = Robots::find();
     *
     * echo "There are ", count($robots), "\n";
     *
     * // How many mechanical robots are there?
     * $robots = Robots::find(
     *     "type = 'mechanical'"
     * );
     *
     * echo "There are ", count($robots), "\n";
     *
     * // Get and print virtual robots ordered by name
     * $robots = Robots::find(
     *     [
     *         "type = 'virtual'",
     *         "order" => "name",
     *     ]
     * );
     *
     * foreach ($robots as $robot) {
     *     echo $robot->name, "\n";
     * }
     *
     * // Get first 100 virtual robots ordered by name
     * $robots = Robots::find(
     *     [
     *         "type = 'virtual'",
     *         "order" => "name",
     *         "limit" => 100,
     *     ]
     * );
     *
     * foreach ($robots as $robot) {
     *     echo $robot->name, "\n";
     * }
     *
     * // encapsulate find it into an running transaction esp. useful for application unit-tests
     * // or complex business logic where we wanna control which transactions are used.
     *
     * $myTransaction = new Transaction(\Phalcon\Di\Di::getDefault());
     * $myTransaction->begin();
     *
     * $newRobot = new Robot();
     * $newRobot->setTransaction($myTransaction);
     *
     * $newRobot->assign(
     *     [
     *         'name' => 'test',
     *         'type' => 'mechanical',
     *         'year' => 1944,
     *     ]
     * );
     *
     * $newRobot->save();
     *
     * $resultInsideTransaction = Robot::find(
     *     [
     *         'name' => 'test',
     *         Model::TRANSACTION_INDEX => $myTransaction,
     *     ]
     * );
     *
     * $resultOutsideTransaction = Robot::find(['name' => 'test']);
     *
     * foreach ($setInsideTransaction as $robot) {
     *     echo $robot->name, "\n";
     * }
     *
     * foreach ($setOutsideTransaction as $robot) {
     *     echo $robot->name, "\n";
     * }
     *
     * // reverts all not commited changes
     * $myTransaction->rollback();
     *
     * // creating two different transactions
     * $myTransaction1 = new Transaction(\Phalcon\Di\Di::getDefault());
     * $myTransaction1->begin();
     * $myTransaction2 = new Transaction(\Phalcon\Di\Di::getDefault());
     * $myTransaction2->begin();
     *
     *  // add a new robots
     * $firstNewRobot = new Robot();
     * $firstNewRobot->setTransaction($myTransaction1);
     * $firstNewRobot->assign(
     *     [
     *         'name' => 'first-transaction-robot',
     *         'type' => 'mechanical',
     *         'year' => 1944,
     *     ]
     * );
     * $firstNewRobot->save();
     *
     * $secondNewRobot = new Robot();
     * $secondNewRobot->setTransaction($myTransaction2);
     * $secondNewRobot->assign(
     *     [
     *         'name' => 'second-transaction-robot',
     *         'type' => 'fictional',
     *         'year' => 1984,
     *     ]
     * );
     * $secondNewRobot->save();
     *
     * // this transaction will find the robot.
     * $resultInFirstTransaction = Robot::find(
     *     [
     *         'name'                   => 'first-transaction-robot',
     *         Model::TRANSACTION_INDEX => $myTransaction1,
     *     ]
     * );
     *
     * // this transaction won't find the robot.
     * $resultInSecondTransaction = Robot::find(
     *     [
     *         'name'                   => 'first-transaction-robot',
     *         Model::TRANSACTION_INDEX => $myTransaction2,
     *     ]
     * );
     *
     * // this transaction won't find the robot.
     * $resultOutsideAnyExplicitTransaction = Robot::find(
     *     [
     *         'name' => 'first-transaction-robot',
     *     ]
     * );
     *
     * // this transaction won't find the robot.
     * $resultInFirstTransaction = Robot::find(
     *     [
     *         'name'                   => 'second-transaction-robot',
     *         Model::TRANSACTION_INDEX => $myTransaction2,
     *     ]
     * );
     *
     * // this transaction will find the robot.
     * $resultInSecondTransaction = Robot::find(
     *     [
     *         'name'                   => 'second-transaction-robot',
     *         Model::TRANSACTION_INDEX => $myTransaction1,
     *     ]
     * );
     *
     * // this transaction won't find the robot.
     * $resultOutsideAnyExplicitTransaction = Robot::find(
     *     [
     *         'name' => 'second-transaction-robot',
     *     ]
     * );
     *
     * $transaction1->rollback();
     * $transaction2->rollback();
     * ```
     *
     * @param array|string|int|null $parameters = {
     *
     * @option string "conditions"
     * @option string "columns"
     * @option array  "bind"
     * @option array  "bindTypes"
     * @option string "order"
     * @option int    "limit"
     * @option int    "offset"
     * @option string "group"
     * @option bool   "for_updated"
     * @option bool   "shared_lock"
     * @option array  "cache" {
     * @option string "lifetime"
     * @option string "key"
     *      },
     * @option ?bool  "hydration"
     * }
     *
     * @return \Phalcon\Mvc\Model\Resultset<int, T>
     */
    public static function find(
        mixed $parameters = null
    ): ResultsetInterface {
        if (!is_array($parameters)) {
            $params = [];

            if ($parameters !== null) {
                $params[] = $parameters;
            }
        } else {
            $params = $parameters;
        }

        $query = static::getPreparedQuery($params);

        /**
         * Execute the query passing the bind-params and casting-types
         */
        $resultset = $query->execute();

        /**
         * Define an hydration mode
         */
        if (is_object($resultset) && isset($params["hydration"])) {
            $resultset->setHydrateMode($params["hydration"]);
        }

        return $resultset;
    }

    /**
     * Query the first record that matches the specified conditions
     *
     * ```php
     * // What's the first robot in robots table?
     * $robot = Robots::findFirst();
     *
     * echo "The robot name is ", $robot->name;
     *
     * // What's the first mechanical robot in robots table?
     * $robot = Robots::findFirst(
     *     "type = 'mechanical'"
     * );
     *
     * echo "The first mechanical robot name is ", $robot->name;
     *
     * // Get first virtual robot ordered by name
     * $robot = Robots::findFirst(
     *     [
     *         "type = 'virtual'",
     *         "order" => "name",
     *     ]
     * );
     *
     * echo "The first virtual robot name is ", $robot->name;
     *
     * // behaviour with transaction
     * $myTransaction = new Transaction(\Phalcon\Di\Di::getDefault());
     * $myTransaction->begin();
     *
     * $newRobot = new Robot();
     * $newRobot->setTransaction($myTransaction);
     * $newRobot->assign(
     *     [
     *         'name' => 'test',
     *         'type' => 'mechanical',
     *         'year' => 1944,
     *     ]
     * );
     * $newRobot->save();
     *
     * $findsARobot = Robot::findFirst(
     *     [
     *         'name'                   => 'test',
     *         Model::TRANSACTION_INDEX => $myTransaction,
     *     ]
     * );
     *
     * $doesNotFindARobot = Robot::findFirst(
     *     [
     *         'name' => 'test',
     *     ]
     * );
     *
     * var_dump($findARobot);
     * var_dump($doesNotFindARobot);
     *
     * $transaction->commit();
     *
     * $doesFindTheRobotNow = Robot::findFirst(
     *     [
     *         'name' => 'test',
     *     ]
     * );
     * ```
     *
     * @param array|string|int|null $parameters = {
     *
     * @option string "conditions"
     * @option string "columns"
     * @option array  "bind"
     * @option array  "bindTypes"
     * @option string "order"
     * @option int    "limit"
     * @option int    "offset"
     * @option string "group"
     * @option bool   "for_updated"
     * @option bool   "shared_lock"
     * @option array  "cache" {
     * @option string "lifetime"
     * @option string "key"
     *      },
     * @option ?bool  "hydration"
     * }
     *
     * @return T|Row|null
     * @throws Exception
     */
    public static function findFirst(
        mixed $parameters = null
    ) {
        if (null === $parameters) {
            $params = [];
        } elseif (is_array($parameters)) {
            $params = $parameters;
        } elseif (is_string($parameters) || is_numeric($parameters)) {
            $params = [$parameters];
        } else {
            throw new InvalidFindParameters(get_called_class());
        }

        $query = static::getPreparedQuery($params, 1);

        /**
         * Return only the first row
         */
        $query->setUniqueRow(true);

        /**
         * Execute the query passing the bind-params and casting-types
         */
        return $query->execute();
    }

    /**
     * Fires an event, implicitly calls behaviors and listeners in the events
     * manager are notified
     *
     * @param string $eventName
     *
     * @return bool|null
     */
    public function fireEvent(string $eventName): bool | null
    {
        /**
         * Check if there is a method with the same name of the event
         */
        if (method_exists($this, $eventName)) {
            $this->$eventName();
        }

        $container = $this->getDI();
        if (
            ($em = $this->getEventsManager()) &&
            $eventObject = $container?->get('modelsEventFactory')->create($eventName, $this)
        ) {
            $logger = $this->getEventLogger($container);
            foreach ([static::class, ...class_parents($this), ...class_implements($this)] as $className) {
                // make sure that every event has a chance to be fired
                try {
                    // wildcard event
                    $em->dispatch($eventObject, name: $className, source: $this);
                } catch (Throwable $t) {
                    $logger?->error(
                        'Error processing model event',
                        [
                            'exception' => $t,
                            'class' => $className,
                            'event' => $eventName,
                        ]
                    );
                }
                try {
                    // specific event
                    $em->dispatch($eventObject, name: [$className, $eventName], source: $this);
                } catch (Throwable $t) {
                    $logger?->error(
                        'Error processing model event',
                        [
                            'exception' => $t,
                            'class' => $className,
                            'event' => $eventName,
                        ]
                    );
                }
            }
        }

        /**
         * Send a notification to the events manager
         */
        return $this->modelsManager->notifyEvent(
            $eventName,
            $this
        );
    }

    /**
     * Fires an event, implicitly calls behaviors and listeners in the events
     * manager are notified
     * This method stops if one of the callbacks/listeners returns bool false
     *
     * @param string $eventName
     *
     * @return bool|null
     */
    public function fireEventCancel(string $eventName): bool | null
    {
        /**
         * Check if there is a method with the same name of the event
         */
        if (method_exists($this, $eventName) && $this->$eventName() === false) {
            return false;
        }

        $container = $this->getDI();
        if (
            ($em = $this->getEventsManager()) &&
            $eventObject = $container?->get('modelsEventFactory')->create($eventName, $this)
        ) {
            $logger = $this->getEventLogger($container);

            foreach ([static::class, ...class_parents($this), ...class_implements($this)] as $className) {
                // make sure that every event has a chance to be fired
                try {
                    // wildcard event
                    $em->dispatch($eventObject, name: $className, source: $this);
                } catch (Throwable $t) {
                    $logger?->error(
                        'Error processing model event',
                        [
                            'exception' => $t,
                            'class' => $className,
                            'event' => $eventName,
                        ]
                    );
                }
                try {
                    // specific event
                    $em->dispatch($eventObject, name: [$className, $eventName], source: $this);
                } catch (Throwable $t) {
                    $logger?->error(
                        'Error processing model event',
                        [
                            'exception' => $t,
                            'class' => $className,
                            'event' => $eventName,
                        ]
                    );
                }

                if ($eventObject instanceof StoppableEventInterface && $eventObject->isPropagationStopped()) {
                    break;
                }
            }
        }

        if (
            isset($eventObject) &&
            $eventObject instanceof StoppableEventInterface &&
            $eventObject->isPropagationStopped()
        ) {
            return false;
        }

        /**
         * Send a notification to the events manager
         */
        return $this->modelsManager->notifyEvent(
            $eventName,
            $this
        );
    }

    /**
     * Resolves an optional PSR-3 logger from the container. Returns null when
     * no logger service is registered: logging model-event dispatch errors is
     * best-effort and must not abort the operation. The container's get()
     * throws on a missing service, so has() is checked first.
     *
     * @param object|null $container
     *
     * @return LoggerInterface|null
     */
    protected function getEventLogger(object | null $container): LoggerInterface | null
    {
        if (!$container instanceof DiInterface) {
            return null;
        }

        foreach (['logger', LoggerInterface::class] as $service) {
            if ($container->has($service)) {
                $logger = $container->get($service);

                if ($logger instanceof LoggerInterface) {
                    return $logger;
                }
            }
        }

        return null;
    }

    /**
     * Returns a list of changed values.
     *
     * ```php
     * $robots = Robots::findFirst();
     * print_r($robots->getChangedFields()); // []
     *
     * $robots->deleted = 'Y';
     *
     * $robots->getChangedFields();
     * print_r($robots->getChangedFields()); // ["deleted"]
     * ```
     *
     * @return array
     * @throws Exception
     */
    public function getChangedFields(): array
    {
        $snapshot = $this->snapshot;

        if (!is_array($snapshot)) {
            throw new SnapshotsDisabled(get_class($this));
        }

        /**
         * Return the models meta-data
         */
        $metaData = $this->getModelsMetaData();

        /**
         * The reversed column map is an array if the model has a column map
         */
        $columnMap = $metaData->getReverseColumnMap($this);

        /**
         * Data types are field indexed
         */
        if (!is_array($columnMap)) {
            $allAttributes = $metaData->getDataTypes($this);
        } else {
            $allAttributes = $columnMap;
        }

        /**
         * Check every attribute in the model
         */
        $changed = [];
        foreach ($allAttributes as $name => $item) {
            /**
             * If some attribute is not present in the snapshot, we assume the
             * record as changed. array_key_exists() / property_exists() are
             * used so a snapshot or model property that legitimately holds
             * `null` (e.g. a nullable DB column loaded from a fresh row) is
             * not mistaken for absent. [#17042]
             */
            if (!array_key_exists($name, $snapshot)) {
                $changed[] = $name;

                continue;
            }

            /**
             * If some attribute is not present in the model, we assume the
             * record as changed
             */
            if (!property_exists($this, $name)) {
                $changed[] = $name;

                continue;
            }

            $value = $this->$name;

            /**
             * Check if the field has changed
             */
            if ($value !== $snapshot[$name]) {
                $changed[] = $name;
            }
        }

        return $changed;
    }

    /**
     * Returns one of the DIRTY_STATE_* constants telling if the record exists
     * in the database or not
     *
     * @return int
     */
    public function getDirtyState(): int
    {
        return $this->dirtyState;
    }

    /**
     * Returns the custom events manager or null if there is no custom events manager
     *
     * @return EventsManagerInterface|null
     */
    public function getEventsManager(): EventsManagerInterface | null
    {
        return $this->modelsManager->getCustomEventsManager($this);
    }

    /**
     * Returns array of validation messages
     *
     *```php
     * $robot = new Robots();
     *
     * $robot->type = "mechanical";
     * $robot->name = "Astro Boy";
     * $robot->year = 1952;
     *
     * if ($robot->save() === false) {
     *     echo "Umh, We can't store robots right now ";
     *
     *     $messages = $robot->getMessages();
     *
     *     foreach ($messages as $message) {
     *         echo $message;
     *     }
     * } else {
     *     echo "Great, a new robot was saved successfully!";
     * }
     * ```
     *
     * @param array|string|null $filter
     *
     * @return array|MessageInterface[]
     */
    public function getMessages(array | string | null $filter = null): array
    {
        if ((is_string($filter) || is_array($filter)) && !empty($filter)) {
            $filtered = [];

            if (is_string($filter)) {
                $filter = [$filter];
            }

            foreach ($this->errorMessages as $message) {
                if (in_array($message->getField(), $filter)) {
                    $filtered[] = $message;
                }
            }

            return $filtered;
        }

        return $this->errorMessages;
    }

    /**
     * Returns the models manager related to the entity instance
     *
     * @return ManagerInterface
     */
    public function getModelsManager(): ManagerInterface
    {
        return $this->modelsManager;
    }

    /**
     * @return MetaDataInterface
     * @throws Exception
     */
    public function getModelsMetaData(): MetaDataInterface
    {
        if ($this->modelsMetaData === null) {
            /**
             * Obtain the models-metadata service from the DI
             */
            $metaData = $this->container->get("modelsMetadata");

            if (!is_object($metaData)) {
                throw new InvalidModelsMetadataService(get_class($this));
            }

            /**
             * Update the models-metadata property
             */
            $this->modelsMetaData = $metaData;
        }

        return $this->modelsMetaData;
    }

    /**
     * Returns the internal old snapshot data
     *
     * @return array
     */
    public function getOldSnapshotData(): array
    {
        return $this->oldSnapshot;
    }

    /**
     * Returns the type of the latest operation performed by the ORM
     * Returns one of the OP_* class constants
     *
     * @return int
     */
    public function getOperationMade(): int
    {
        return $this->operationMade;
    }

    /**
     * Gets the connection used to read data for the model
     *
     * @return AdapterInterface
     */
    final public function getReadConnection(): AdapterInterface
    {
        if ($this->transaction !== null) {
            return $this->transaction->getConnection();
        }

        return $this->modelsManager->getReadConnection($this);
    }

    /**
     * Returns the DependencyInjection connection service name used to read data
     * related the model
     *
     * @return string
     */
    final public function getReadConnectionService(): string
    {
        return $this->modelsManager->getReadConnectionService($this);
    }

    /**
     * Returns related records based on defined relations
     *
     * @param string     $alias
     * @param mixed|null $arguments
     *
     * @return mixed
     * @throws Exception
     */
    public function getRelated(string $alias, mixed $arguments = null): mixed
    {
        /**
         * Query the relation by alias
         */
        $className  = get_class($this);
        $lowerAlias = strtolower($alias);
        $relation   = $this->modelsManager->getRelationByAlias(
            $className,
            $lowerAlias
        );

        if (!is_object($relation)) {
            throw new RelationNotDefined($className, $alias);
        }

        /**
         * If there are any arguments, Manager with handle the caching of the records
         */
        if ($arguments === null) {
//            /**
//             * If the related records are already in cache and the relation is reusable,
//             * we return the cached records.
//             */
//            if relation->isReusable() && this->isRelationshipLoaded(lowerAlias) {
//                $result = $this->related[lowerAlias];
//            } else {
//                /**
//                 * Call the 'getRelationRecords' in the models manager.
//                 */
//                $result = manager->getRelationRecords(relation, this, arguments);
//
//                /**
//                 * We store relationship objects in the related cache if there were no arguments.
//                 */
//                $this->related[lowerAlias] = result;
//            }
            if (isset($this->dirtyRelated[$lowerAlias])) {
                return $this->dirtyRelated[$lowerAlias];
            }
            if (isset($this->related[$lowerAlias])) {
                return $this->related[$lowerAlias];
            }

            /**
             * We do not need conditionals here. The models manager stores
             * reusable related records so we utilize that and remove complexity
             * from here. There is a very small decrease in performance since
             * the models manager needs to calculate the unique key from
             * the passed arguments and then check its internal cache
             */
            $result = $this->modelsManager->getRelationRecords($relation, $this, $arguments);

            /**
             * We store relationship objects in the related cache if there were no arguments.
             */
            $this->related[$lowerAlias] = $result;
        } else {
            /**
             * Individually queried related records are handled by Manager.
             * The Manager also checks and stores reusable records.
             */
            $result = $this->modelsManager->getRelationRecords($relation, $this, $arguments);
        }

        return $result;
    }

    /**
     * Returns schema name where the mapped table is located
     *
     * @return string|null
     */
    final public function getSchema(): string | null
    {
        return $this->modelsManager->getModelSchema($this);
    }

    /**
     * Returns the internal snapshot data
     *
     * @return array
     */
    public function getSnapshotData(): array
    {
        return $this->snapshot;
    }

    /**
     * Returns the table name mapped in the model
     *
     * @return string
     */
    final public function getSource(): string
    {
        return $this->modelsManager->getModelSource($this);
    }

    /**
     * @return TransactionInterface|null
     */
    public function getTransaction(): TransactionInterface | null
    {
        return $this->transaction;
    }

    /**
     * Returns a list of updated values.
     *
     * ```php
     * $robots = Robots::findFirst();
     * print_r($robots->getChangedFields()); // []
     *
     * $robots->deleted = 'Y';
     *
     * $robots->getChangedFields();
     * print_r($robots->getChangedFields()); // ["deleted"]
     * $robots->save();
     * print_r($robots->getChangedFields()); // []
     * print_r($robots->getUpdatedFields()); // ["deleted"]
     * ```
     *
     * @return array
     * @throws Exception
     */
    public function getUpdatedFields(): array
    {
        $snapshot    = $this->snapshot;
        $oldSnapshot = $this->oldSnapshot;

        if (!Settings::get("orm.update_snapshot_on_save")) {
            throw new UpdateSnapshotDisabled(get_class($this));
        }

        if (!is_array($snapshot)) {
            throw new SnapshotsDisabled(get_class($this));
        }

        /**
         * Dirty state must be DIRTY_PERSISTENT to make the checking
         */
        if ($this->dirtyState != self::DIRTY_STATE_PERSISTENT) {
            throw new RecordNotPersisted(get_class($this));
        }

        $updated = [];

        foreach ($snapshot as $name => $value) {
            /**
             * If some attribute is not present in the oldSnapshot, we assume
             * the record as changed. array_key_exists() is used so a stored
             * `null` is not mistaken for an absent key. [#17042]
             */
            if (!array_key_exists($name, $oldSnapshot) || $value !== $oldSnapshot[$name]) {
                $updated[] = $name;
            }
        }

        return $updated;
    }

    /**
     * Gets the connection used to write data to the model
     *
     * @return AdapterInterface
     */
    final public function getWriteConnection(): AdapterInterface
    {
        if ($this->transaction !== null) {
            return $this->transaction->getConnection();
        }

        return $this->modelsManager->getWriteConnection($this);
    }

    /**
     * Returns the DependencyInjection connection service name used to write
     * data related to the model
     *
     * @return string
     */
    final public function getWriteConnectionService(): string
    {
        return $this->modelsManager->getWriteConnectionService($this);
    }

    /**
     * Check if a specific attribute has changed
     * This only works if the model is keeping data snapshots
     *
     *```php
     * $robot = new Robots();
     *
     * $robot->type = "mechanical";
     * $robot->name = "Astro Boy";
     * $robot->year = 1952;
     *
     * $robot->create();
     *
     * $robot->type = "hydraulic";
     *
     * $hasChanged = $robot->hasChanged("type"); // returns true
     * $hasChanged = $robot->hasChanged(["type", "name"]); // returns true
     * $hasChanged = $robot->hasChanged(["type", "name"], true); // returns false
     *```
     *
     * @param array|string $fieldName
     * @param bool         $allFields
     *
     * @return bool
     * @throws Exception
     */
    public function hasChanged(
        mixed $fieldName = null,
        bool $allFields = false
    ): bool {
        $changedFields = $this->getChangedFields();

        /**
         * If a field was specified we only check it
         */
        if (is_string($fieldName)) {
            return in_array($fieldName, $changedFields);
        }

        if (is_array($fieldName)) {
            $intersect = array_intersect($fieldName, $changedFields);

            if ($allFields) {
                return $intersect == $fieldName;
            }

            return count($intersect) > 0;
        }

        return count($changedFields) > 0;
    }

    /**
     * Checks if the object has internal snapshot data
     *
     * @return bool
     */
    public function hasSnapshotData(): bool
    {
        return !empty($this->snapshot);
    }

    /**
     * Check if a specific attribute was updated
     * This only works if the model is keeping data snapshots
     *
     * @param mixed|null $fieldName
     * @param bool       $allFields
     *
     * @return bool
     * @throws Exception
     */
    public function hasUpdated(
        mixed $fieldName = null,
        bool $allFields = false
    ): bool {
        $updatedFields = $this->getUpdatedFields();

        /**
         * If a field was specified we only check it
         */
        if (is_string($fieldName)) {
            return in_array($fieldName, $updatedFields);
        }

        if (is_array($fieldName)) {
            $intersect = array_intersect($fieldName, $updatedFields);
            if ($allFields) {
                return $intersect == $fieldName;
            }

            return count($intersect) > 0;
        }

        return count($updatedFields) > 0;
    }

    /**
     * Checks if saved related records have already been loaded.
     *
     * Only returns true if the records were previously fetched
     * through the model without any additional parameters.
     *
     * ```php
     * $robot = Robots::findFirst();
     * var_dump($robot->isRelationshipLoaded('robotsParts')); // false
     *
     * $robotsParts = $robot->getRobotsParts(['id > 0']);
     * var_dump($robot->isRelationshipLoaded('robotsParts')); // false
     *
     * $robotsParts = $robot->getRobotsParts(); // or $robot->robotsParts
     * var_dump($robot->isRelationshipLoaded('robotsParts')); // true
     *
     * $robot->robotsParts = [new RobotsParts()];
     * var_dump($robot->isRelationshipLoaded('robotsParts')); // false
     * ```
     *
     * @param string $relationshipAlias
     *
     * @return bool
     */
    public function isRelationshipLoaded(string $relationshipAlias): bool
    {
        return isset($this->related[strtolower($relationshipAlias)]);
    }

    /**
     * Serializes the object for json_encode
     *
     *```php
     * echo json_encode($robot);
     *```
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Returns the maximum value of a column for a result-set of rows that match
     * the specified conditions
     *
     * ```php
     * // What is the maximum robot id?
     * $id = Robots::maximum(
     *     [
     *         "column" => "id",
     *     ]
     * );
     *
     * echo "The maximum robot id is: ", $id, "\n";
     *
     * // What is the maximum id of mechanical robots?
     * $sum = Robots::maximum(
     *     [
     *         "type = 'mechanical'",
     *         "column" => "id",
     *     ]
     * );
     *
     * echo "The maximum robot id of mechanical robots is ", $id, "\n";
     * ```
     *
     * @param mixed|null $parameters
     *
     * @return mixed
     */
    public static function maximum(mixed $parameters = null): mixed
    {
        return self::groupResult("MAX", "maximum", $parameters);
    }

    /**
     * Returns the minimum value of a column for a result-set of rows that match
     * the specified conditions
     *
     * ```php
     * // What is the minimum robot id?
     * $id = Robots::minimum(
     *     [
     *         "column" => "id",
     *     ]
     * );
     *
     * echo "The minimum robot id is: ", $id;
     *
     * // What is the minimum id of mechanical robots?
     * $sum = Robots::minimum(
     *     [
     *         "type = 'mechanical'",
     *         "column" => "id",
     *     ]
     * );
     *
     * echo "The minimum robot id of mechanical robots is ", $id;
     * ```
     *
     * @param mixed|null $parameters
     *
     * @return mixed
     */
    public static function minimum(mixed $parameters = null): mixed
    {
        return self::groupResult("MIN", "minimum", $parameters);
    }

    /**
     * Create a criteria for a specific model
     *
     * @param DiInterface|ServiceCollection|null $container
     *
     * @return CriteriaInterface
     */
    public static function query(DiInterface | ServiceCollection | null $container = null): CriteriaInterface
    {
        /**
         * Use the global dependency injector if there is no one defined
         */
        if (null === $container) {
            $container = Di::getDefault();
        }

        /**
         * Gets Criteria instance from DI container
         */
        if ($container instanceof DiInterface) {
            $criteria = $container->get(
                "Phalcon\\Mvc\\Model\\Criteria"
            );
        } elseif (null !== $container) {
            $criteria = new Criteria();
            $criteria->setDI($container);
        } else {
            $criteria = new Criteria();
        }

        $criteria->setModelName(get_called_class());

        return $criteria;
    }

    /**
     * Reads an attribute value by its name
     *
     * ```php
     * echo $robot->readAttribute("name");
     * ```
     *
     * @param string $attribute
     *
     * @return mixed
     */
    public function readAttribute(string $attribute): mixed
    {
        if (!isset($this->$attribute)) {
            return null;
        }

        return $this->$attribute;
    }

    /**
     * Refreshes the model attributes re-querying the record from the database
     *
     * @return ModelInterface
     * @throws Exception
     */
    public function refresh(): ModelInterface
    {
        if ($this->dirtyState != self::DIRTY_STATE_PERSISTENT) {
            throw new RecordCannotRefresh(get_class($this));
        }

        $metaData       = $this->getModelsMetaData();
        $readConnection = $this->getReadConnection();
        $manager        = $this->modelsManager;

        $schema = $this->getSchema();
        $source = $this->getSource();

        if ($schema) {
            $table = [$schema, $source];
        } else {
            $table = $source;
        }

        $uniqueKey = $this->uniqueKey;

        if (!$uniqueKey) {
            /**
             * We need to check if the record exists
             */
            if (!$this->has($metaData, $readConnection)) {
                throw new RecordCannotRefresh(get_class($this));
            }

            $uniqueKey = $this->uniqueKey;
        }

        $uniqueParams = $this->uniqueParams;

        if (!is_array($uniqueParams)) {
            throw new RecordCannotRefresh(get_class($this));
        }

        /**
         * We only refresh the attributes in the model's metadata
         */
        $fields = [];

        foreach ($metaData->getAttributes($this) as $attribute) {
            $fields[] = [$attribute];
        }

        /**
         * We directly build the SELECT to save resources
         */
        $dialect = $readConnection->getDialect();
        $tables  = $dialect->select(
            [
                "columns" => $fields,
                "tables"  => $readConnection->escapeIdentifier($table),
                "where"   => $uniqueKey,
            ]
        );

        $row = $readConnection->fetchOne(
            $tables,
            Enum::FETCH_ASSOC,
            $uniqueParams,
            $this->uniqueTypes
        );

        /**
         * Get a column map if any
         * Assign the resulting array to the this object
         */
        if (is_array($row)) {
            $columnMap = $metaData->getColumnMap($this);

            $this->assign($row, null, $columnMap);

            if ($manager->isKeepingSnapshots($this)) {
                $this->setSnapshotData($row, $columnMap);
                $this->setOldSnapshotData($row, $columnMap);
            }
        }

        $this->fireEvent("afterFetch");

        return $this;
    }

    /**
     * Inserts or updates a model instance. Returning true on success or false
     * otherwise.
     *
     *```php
     * // Creating a new robot
     * $robot = new Robots();
     *
     * $robot->type = "mechanical";
     * $robot->name = "Astro Boy";
     * $robot->year = 1952;
     *
     * $robot->save();
     *
     * // Updating a robot name
     * $robot = Robots::findFirst("id = 100");
     *
     * $robot->name = "Biomass";
     *
     * $robot->save();
     *```
     *
     * @return bool
     * @throws Exception
     * @throws ValidationFailed
     */
    public function save(): bool
    {
        $visited = new Collection();

        return $this->doSave($visited);
    }

    /**
     * Serializes the object ignoring connections, services, related objects or
     * static properties
     *
     * @return string|null
     * @throws Exception
     */
    public function serialize(): string | null
    {
        /**
         * Use the standard serialize function to serialize the array data
         */
        $attributes = $this->toArray(null, false);
        $dirtyState = $this->dirtyState;
        $manager    = $this->getModelsManager();
        $snapshot   = null;

        if (
            $manager->isKeepingSnapshots($this) &&
            $this->snapshot != null &&
            $attributes != $this->snapshot
        ) {
            $snapshot = $this->snapshot;
        }

        return serialize(
            [
                "attributes" => $attributes,
                "snapshot"   => $snapshot,
                "dirtyState" => $dirtyState,
            ]
        );
    }

    /**
     * Sets the DependencyInjection connection service name
     *
     * @param string $connectionService
     *
     * @return void
     */
    final public function setConnectionService(string $connectionService): void
    {
        $this->modelsManager->setConnectionService(
            $this,
            $connectionService
        );
    }

    /**
     * Sets the dirty state of the object using one of the DIRTY_STATE_* constants
     *
     * @param int $dirtyState
     *
     * @return ModelInterface|bool
     */
    public function setDirtyState(int $dirtyState): ModelInterface | bool
    {
        $this->dirtyState = $dirtyState;

        return $this;
    }

    /**
     * Sets a custom events manager
     *
     * @param EventsManagerInterface $eventsManager
     *
     * @return void
     */
    public function setEventsManager(EventsManagerInterface $eventsManager)
    {
        $this->modelsManager->setCustomEventsManager($this, $eventsManager);
    }

    /**
     * Sets the record's old snapshot data.
     * This method is used internally to set old snapshot data when the model
     * was set up to keep snapshot data
     *
     * @param array      $data
     * @param array|null $columnMap
     *
     * @return void
     * @throws Exception
     */
    public function setOldSnapshotData(array $data, mixed $columnMap = null)
    {
        /**
         * Build the snapshot based on a column map
         */
        if (is_array($columnMap)) {
            $snapshot = [];

            foreach ($data as $key => $value) {
                /**
                 * Use only strings
                 */
                if (!is_string($key)) {
                    continue;
                }

                /**
                 * Every field must be part of the column map
                 */
                if (!isset($columnMap[$key])) {
                    if (!Settings::get("orm.ignore_unknown_columns")) {
                        throw new ColumnNotInMap($key, get_class($this));
                    }

                    continue;
                }

                $attribute = $columnMap[$key];
                if (is_array($attribute)) {
                    if (!isset($attribute[0])) {
                        if (!Settings::get("orm.ignore_unknown_columns")) {
                            throw new ColumnNotInMap($key, get_class($this));
                        }

                        continue;
                    }

                    $attribute = $attribute[0];
                }

                $snapshot[$attribute] = $value;
            }
        } else {
            $snapshot = $data;
        }

        $this->oldSnapshot = $snapshot;
    }

    /**
     * Sets the DependencyInjection connection service name used to read data
     *
     * @param string $connectionService
     *
     * @return void
     */
    final public function setReadConnectionService(string $connectionService): void
    {
        $this->modelsManager->setReadConnectionService(
            $this,
            $connectionService
        );
    }

    /**
     * Sets the record's snapshot data.
     * This method is used internally to set snapshot data when the model was
     * set up to keep snapshot data
     *
     * @param array      $data
     * @param array|null $columnMap
     *
     * @return void
     * @throws Exception
     */
    public function setSnapshotData(array $data, mixed $columnMap = null): void
    {
        /**
         * Build the snapshot based on a column map
         */
        if (is_array($columnMap)) {
            $snapshot = [];

            foreach ($data as $key => $value) {
                /**
                 * Use only strings
                 */
                if (!is_string($key)) {
                    continue;
                }

                // Try to find case-insensitive key variant
                if (!isset($columnMap[$key]) && Settings::get("orm.case_insensitive_column_map")) {
                    $key = self::caseInsensitiveColumnMap($columnMap, $key);
                }

                /**
                 * Every field must be part of the column map
                 */
                if (!isset($columnMap[$key])) {
                    if (!Settings::get("orm.ignore_unknown_columns")) {
                        throw new ColumnNotInMap($key, get_class($this));
                    }

                    continue;
                }

                $attribute = $columnMap[$key];

                if (is_array($attribute)) {
                    if (!isset($attribute[0])) {
                        if (!Settings::get("orm.ignore_unknown_columns")) {
                            throw new ColumnNotInMap($key, get_class($this));
                        }

                        continue;
                    }

                    $attribute = $attribute[0];
                }

                $snapshot[$attribute] = $value;
            }
        } else {
            $snapshot = $data;
        }


        $this->snapshot = $snapshot;
    }

    /**
     * Marks one or more many-to-many relationships to be synchronized (or not)
     * on the next save() call, overriding the relation's `sync` option for that
     * save only. The flag is cleared after save().
     *
     * When syncing is enabled, intermediate rows for related records no longer
     * present in the assigned array are deleted.
     *
     *```php
     * // Sync only the "tags" relationship on this save
     * $post->setSync("tags")->save();
     *
     * // Sync every many-to-many relationship on this save
     * $post->setSync()->save();
     *
     * // Disable syncing for every relationship on this save
     * $post->setSync("*", false)->save();
     *
     * // Disable syncing for specific relationships on this save
     * $post->setSync(["tags", "categories"], false)->save();
     *```
     *
     * @param string|array|null $elements
     * @param bool              $enabled
     *
     * @return ModelInterface
     */
    public function setSync(
        mixed $elements = null,
        bool $enabled = true
    ): ModelInterface {
        if ($elements === null || $elements === "*") {
            $this->syncRelated["*"] = $enabled;

            return $this;
        }

        if (is_array($elements)) {
            foreach ($elements as $alias) {
                $this->syncRelated[strtolower($alias)] = $enabled;
            }

            return $this;
        }

        $this->syncRelated[strtolower($elements)] = $enabled;

        return $this;
    }

    /**
     * Sets a transaction related to the Model instance
     *
     *```php
     * use Phalcon\Mvc\Model\Transaction\Manager as TxManager;
     * use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
     *
     * try {
     *     $txManager = new TxManager();
     *
     *     $transaction = $txManager->get();
     *
     *     $robot = new Robots();
     *
     *     $robot->setTransaction($transaction);
     *
     *     $robot->name       = "WALL·E";
     *     $robot->created_at = date("Y-m-d");
     *
     *     if ($robot->save() === false) {
     *         $transaction->rollback("Can't save robot");
     *     }
     *
     *     $robotPart = new RobotParts();
     *
     *     $robotPart->setTransaction($transaction);
     *
     *     $robotPart->type = "head";
     *
     *     if ($robotPart->save() === false) {
     *         $transaction->rollback("Robot part cannot be saved");
     *     }
     *
     *     $transaction->commit();
     * } catch (TxFailed $e) {
     *     echo "Failed, reason: ", $e->getMessage();
     * }
     *```
     *
     * @param TransactionInterface $transaction
     *
     * @return ModelInterface
     */
    public function setTransaction(TransactionInterface $transaction): ModelInterface
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * Sets the DependencyInjection connection service name used to write data
     *
     * @param string $connectionService
     *
     * @return void
     */
    final public function setWriteConnectionService(string $connectionService): void
    {
        $this->modelsManager->setWriteConnectionService(
            $this,
            $connectionService
        );
    }

    /**
     * Enables/disables options in the ORM
     *
     * @param array $options
     *
     * @return void
     */
    public static function setup(array $options): void
    {
        /**
         * Enables/Disables globally the internal events
         */
        $map = [
            "orm.events"                            => "events",
            "orm.virtual_foreign_keys"              => "virtualForeignKeys",
            "orm.column_renaming"                   => "columnRenaming",
            "orm.not_null_validations"              => "notNullValidations",
            "orm.exception_on_failed_save"          => "exceptionOnFailedSave",
            "orm.exception_on_failed_metadata_save" => "exceptionOnFailedMetaDataSave",
            "orm.enable_literals"                   => "phqlLiterals",
            "orm.late_state_binding"                => "lateStateBinding",
            "orm.cast_on_hydrate"                   => "castOnHydrate",
            "orm.ignore_unknown_columns"            => "ignoreUnknownColumns",
            "orm.case_insensitive_column_map"       => "caseInsensitiveColumnMap",
            "orm.update_snapshot_on_save"           => "updateSnapshotOnSave",
            "orm.disable_assign_setters"            => "disableAssignSetters",
            "orm.cast_last_insert_id_to_int"        => "castLastInsertIdToInt",
        ];

        foreach ($map as $setting => $value) {
            if (isset($options[$value])) {
                Settings::set($setting, (bool)$options[$value]);
            }
        }

        if (isset($options["prefetchRecords"])) {
            Settings::set("orm.resultset_prefetch_records", (int)$options["prefetchRecords"]);
        }
    }

    /**
     * Skips the current operation forcing a success state
     *
     * @param bool $skip
     *
     * @return void
     */
    public function skipOperation(bool $skip): void
    {
        $this->skipped = $skip;
    }

    /**
     * Calculates the sum on a column for a result-set of rows that match the
     * specified conditions
     *
     * ```php
     * // How much are all robots?
     * $sum = Robots::sum(
     *     [
     *         "column" => "price",
     *     ]
     * );
     *
     * echo "The total price of robots is ", $sum, "\n";
     *
     * // How much are mechanical robots?
     * $sum = Robots::sum(
     *     [
     *         "type = 'mechanical'",
     *         "column" => "price",
     *     ]
     * );
     *
     * echo "The total price of mechanical robots is  ", $sum, "\n";
     * ```
     *
     * @param mixed|null $parameters
     *
     * @return float|ResultsetInterface
     */
    public static function sum(mixed $parameters = null): float | ResultsetInterface
    {
        $result = self::groupResult("SUM", "sumatory", $parameters);

        return is_string($result) ? (float)$result : $result;
    }

    /**
     * Returns the instance as an array representation
     *
     *```php
     * print_r(
     *     $robot->toArray()
     * );
     *```
     *
     * @param mixed $columns
     * @param bool  $useGetter
     *
     * @return array
     * @throws Exception
     */
    public function toArray(
        mixed $columns = null,
        bool $useGetter = true
    ): array {
        $data      = [];
        $metaData  = $this->getModelsMetaData();
        $columnMap = $metaData->getColumnMap($this);

        foreach ($metaData->getAttributes($this) as $attribute) {
            /**
             * Check if the columns must be renamed
             */
            if (is_array($columnMap)) {
                // Try to find case-insensitive key variant
                if (
                    !isset($columnMap[$attribute]) &&
                    Settings::get("orm.case_insensitive_column_map")
                ) {
                    $attribute = self::caseInsensitiveColumnMap(
                        $columnMap,
                        $attribute
                    );
                }

                if (!isset($columnMap[$attribute])) {
                    if (!Settings::get("orm.ignore_unknown_columns")) {
                        throw new ColumnNotInMap($attribute, get_class($this));
                    }

                    continue;
                }

                $attributeField = $columnMap[$attribute];
            } else {
                $attributeField = $attribute;
            }

            if (
                is_array($columns) &&
                !in_array($attributeField, $columns)
            ) {
                continue;
            }

            /**
             * Check if there is a getter for this property
             */
            $method = "get" . $this->toCamelize($attributeField);

            /**
             * Do not use the getter if the field name is `source` (getSource)
             */
            if (
                true === $useGetter &&
                "getSource" !== $method &&
                method_exists($this, $method)
            ) {
                try {
                    $data[$attributeField] = $this->$method();
                } catch (\Error) {
                    $data[$attributeField] = null;
                }
            } elseif (isset($this->$attributeField)) {
                $data[$attributeField] = $this->$attributeField;
            } else {
                $data[$attributeField] = null;
            }
        }

        return $data;
    }

    /**
     * Unserializes the object from a serialized string
     *
     * @param string $data
     *
     * @return void
     * @throws Exception
     */
    public function unserialize(string $data)
    {
        $attributes = unserialize($data);

        if (is_array($attributes)) {
            if (!isset($attributes["attributes"])) {
                $attributes = [
                    "attributes" => $attributes,
                ];
            }

            /**
             * Obtain the default DI
             */
            $container = Di::getDefault();
            if ($container === null) {
                throw new ModelOrmServicesUnavailable(get_class($this));
            }

            /**
             * Update the dependency injector
             */
            $this->container = $container;

            /**
             * Gets the default modelsManager service
             */
            $manager = $container->get("modelsManager");

            if (!is_object($manager)) {
                throw new InvalidModelsManagerService(get_class($this));
            }

            /**
             * Update the models manager
             */
            $this->modelsManager = $manager;

            /**
             * Try to initialize the model
             */
            $manager->initialize($this);

            /**
             * Allow the developer to run initialization code every time
             * the model is instantiated, including when restored from cache
             */
            if (method_exists($this, "onConstruct")) {
                $this->onConstruct();
            }

            /**
             * Fetch serialized props
             */
            if (isset($attributes["attributes"])) {
                $properties = $attributes["attributes"];
                /**
                 * Update the objects properties
                 */
                foreach ($properties as $key => $value) {
                    try {
                        $this->$key = $value;
                    } catch (\TypeError) {
                    }
                }
            } else {
                $properties = [];
            }

            /**
             * Fetch serialized dirtyState
             */
            if (isset($attributes["dirtyState"])) {
                $this->dirtyState = $attributes["dirtyState"];
            }

            /**
             * Fetch serialized snapshot when option is active
             */
            if ($manager->isKeepingSnapshots($this)) {
                if (isset($attributes["snapshot"])) {
                    $this->snapshot = $attributes["snapshot"];
                } else {
                    $this->snapshot = $properties;
                }
            }
        }
    }

    /**
     * Updates a model instance. If the instance does not exist in the
     * persistence it will throw an exception. Returning `true` on success or
     * `false` otherwise.
     *
     * ```php
     * <?php
     *
     * use MyApp\Models\Invoices;
     *
     * $invoice = Invoices::findFirst('inv_id = 4');
     *
     * $invoice->inv_total = 120;
     *
     * $invoice->update();
     * ```
     *
     * !!! warning "NOTE"
     *
     *     When retrieving the record with `findFirst()`, you need to get the full
     *     object back (no `columns` definition) but also retrieve it using the
     *     primary key. If not, the ORM will issue an `INSERT` instead of `UPDATE`.
     *
     * @return bool
     */
    public function update(): bool
    {
        /**
         * We don't check if the record exists if the record is already checked
         */
        if ($this->dirtyState) {
            $metaData = $this->getModelsMetaData();

            if (!$this->has($metaData, $this->getWriteConnection())) {
                $this->errorMessages = [
                    new Message(
                        "Record cannot be updated because it does not exist",
                        '',
                        "InvalidUpdateAttempt",
                        0,
                        [
                            "model" => get_class($this),
                        ]
                    ),
                ];

                return false;
            }
        }

        /**
         * Call save() anyways
         */
        return $this->save();
    }

    /**
     * Check whether validation process has generated any messages
     *
     *```php
     * use Phalcon\Mvc\Model;
     * use Phalcon\Filter\Validation;
     * use Phalcon\Filter\Validation\Validator\ExclusionIn;
     *
     * class Subscriptors extends Model
     * {
     *     public function validation()
     *     {
     *         $validator = new Validation();
     *
     *         $validator->validate(
     *             "status",
     *             new ExclusionIn(
     *                 [
     *                     "domain" => [
     *                         "A",
     *                         "I",
     *                     ],
     *                 ]
     *             )
     *         );
     *
     *         return $this->validate($validator);
     *     }
     * }
     *```
     *
     * @return bool
     */
    public function validationHasFailed(): bool
    {
        return count($this->errorMessages) > 0;
    }

    /**
     * Writes an attribute value by its name
     *
     *```php
     * $robot->writeAttribute("name", "Rosey");
     *```
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return void
     */
    public function writeAttribute(string $attribute, mixed $value): void
    {
        $this->$attribute = $value;
    }

    /**
     * Sets a list of attributes that must be skipped from the
     * generated UPDATE statement
     *
     *```php
     * class Robots extends \Phalcon\Mvc\Model
     * {
     *     public function initialize()
     *     {
     *         $this->allowEmptyStringValues(
     *             [
     *                 "name",
     *             ]
     *         );
     *     }
     * }
     *```
     *
     * @param array $attributes
     *
     * @return void
     * @throws Exception
     */
    protected function allowEmptyStringValues(array $attributes): void
    {
        $keysAttributes = [];

        foreach ($attributes as $attribute) {
            $keysAttributes[$attribute] = true;
        }

        $this->getModelsMetaData()->setEmptyStringAttributes(
            $this,
            $keysAttributes
        );
    }

    /**
     * Setup a reverse 1-1 or n-1 relation between two models
     *
     *```php
     * class RobotsParts extends \Phalcon\Mvc\Model
     * {
     *     public function initialize()
     *     {
     *         $this->belongsTo(
     *             "robots_id",
     *             Robots::class,
     *             "id"
     *         );
     *     }
     * }
     *```
     *
     * @param mixed  $fields
     * @param string $referenceModel
     * @param string $referencedFields
     * @param array  $options {
     *
     * @option bool   "reusable"
     * @option string "alias"
     * @option array  "foreignKey" {
     * @option string|null "message"
     * @option bool        "allowNulls"
     * @option string|null "action"
     *      }
     * @option array params {
     * @option string "conditions"
     * @option string "columns"
     * @option array  "bind"
     * @option array  "bindTypes"
     * @option string "order"
     * @option int    "limit"
     * @option int    "offset"
     * @option string "group"
     * @option bool   "for_updated"
     * @option bool   "shared_lock"
     * @option array  "cache" {
     * @option int    "lifetime"
     * @option string "key"
     *          }
     * @option string "hydration"
     * }
     *
     * @return Relation
     */
    protected function belongsTo(
        mixed $fields,
        string $referenceModel,
        mixed $referencedFields,
        array $options = []
    ): Relation {
        return $this->modelsManager->addBelongsTo(
            $this,
            $fields,
            $referenceModel,
            $referencedFields,
            $options
        );
    }

    /**
     * Cancel the current operation
     *
     * @return void
     */
    protected function cancelOperation(): void
    {
        if ($this->operationMade == self::OP_DELETE) {
            $this->fireEvent("notDeleted");
        } else {
            $this->fireEvent("notSaved");
        }
    }

    /**
     * Reads "belongs to" relations and check the virtual foreign keys when
     * inserting or updating records to verify that inserted/updated values are
     * present in the related entity
     *
     * @return bool
     */
    final protected function checkForeignKeysRestrict(): bool
    {
        /**
         * Get the models manager
         */
        $manager = $this->modelsManager;

        /**
         * We check if some of the belongsTo relations act as virtual foreign
         * key
         */
        $belongsTo = $manager->getBelongsTo($this);

        $error = false;

        foreach ($belongsTo as $relation) {
            $validateWithNulls = false;
            $foreignKey        = $relation->getForeignKey();

            if ($foreignKey === false) {
                continue;
            }

            /**
             * By default action is restrict
             */
            $action = Relation::ACTION_RESTRICT;

            /**
             * Try to find a different action in the foreign key's options
             */
            if (isset($foreignKey["action"])) {
                $action = (int)$foreignKey["action"];
            }

            /**
             * Check only if the operation is restrict
             */
            if ($action != Relation::ACTION_RESTRICT) {
                continue;
            }

            /**
             * Load the referenced model if needed
             */
            $referencedModel = $manager->load($relation->getReferencedModel());

            /**
             * Since relations can have multiple columns or a single one, we
             * need to build a condition for each of these cases
             */
            $conditions = [];
            $bindParams = [];

            $numberNull       = 0;
            $fields           = $relation->getFields();
            $referencedFields = $relation->getReferencedFields();

            if (is_array($fields)) {
                /**
                 * Create a compound condition
                 */
                foreach ($fields as $position => $field) {
                    $value = $this->$field ?? null;

                    $conditions[] = "[" . $referencedFields[$position] . "] = ?" . $position;
                    $bindParams[] = $value;

                    if ($value === null) {
                        $numberNull++;
                    }
                }

                $validateWithNulls = $numberNull == count($fields);
            } else {
                $value = $this->$fields ?? null;

                $conditions[] = "[" . $referencedFields . "] = ?0";
                $bindParams[] = $value;

                if ($value === null) {
                    $validateWithNulls = true;
                }
            }

            /**
             * Check if the virtual foreign key has extra conditions
             */
            if (isset($foreignKey["conditions"])) {
                $conditions[] = $foreignKey["conditions"];
            }

            /**
             * Check if the relation definition allows nulls
             */
            if ($validateWithNulls) {
                if (isset($foreignKey["allowNulls"])) {
                    $validateWithNulls = (bool)$foreignKey["allowNulls"];
                } else {
                    $validateWithNulls = false;
                }
            }

            /**
             * We don't trust the actual values in the object and pass the
             * values using bound parameters. Let's check
             */
            if (
                !$validateWithNulls &&
                !$referencedModel->count(
                    [
                        implode(" AND ", $conditions),
                        "bind" => $bindParams,
                    ]
                )
            ) {
                /**
                 * Get the user message or produce a new one
                 */
                if (!isset($foreignKey["message"])) {
                    if (is_array($fields)) {
                        $message = "Value of fields \""
                            . implode(", ", $fields)
                            . "\" does not exist on referenced table";
                    } else {
                        $message = "Value of field \""
                            . $fields
                            . "\" does not exist on referenced table";
                    }
                } else {
                    $message = $foreignKey["message"];
                }

                /**
                 * Create a message
                 */
                $this->appendMessage(
                    new Message(
                        $message,
                        $fields,
                        "ConstraintViolation",
                        0,
                        [
                            "model"          => get_class($this),
                            "referenceModel" => $relation->getReferencedModel(),
                        ]
                    )
                );

                $error = true;

                break;
            }
        }

        /**
         * Call 'onValidationFails' if the validation fails
         */
        if ($error) {
            if (Settings::get("orm.events")) {
                $this->fireEvent("onValidationFails");
                $this->cancelOperation();
            }

            return false;
        }

        return true;
    }

    /**
     * Reads both "hasMany" and "hasOne" relations and checks the virtual
     * foreign keys (cascade) when deleting records
     *
     * @return bool
     * @throws Exception
     */
    final protected function checkForeignKeysReverseCascade(): bool
    {
        /**
         * Get the models manager
         */
        $manager = $this->modelsManager;

        /**
         * We check if some of the hasOne/hasMany relations is a foreign key
         */
        $relations = $manager->getHasOneAndHasMany($this);

        foreach ($relations as $relation) {
            /**
             * Check if the relation has a virtual foreign key
             */
            $foreignKey = $relation->getForeignKey();

            if ($foreignKey === false) {
                continue;
            }

            /**
             * By default action is restrict
             */
            $action = Relation::NO_ACTION;

            /**
             * Try to find a different action in the foreign key's options
             */
            if (is_array($foreignKey) && isset($foreignKey["action"])) {
                $action = (int)$foreignKey["action"];
            }

            /**
             * Check only if the operation is restrict
             */
            if ($action != Relation::ACTION_CASCADE) {
                continue;
            }

            $related = $manager->getRelationRecords(
                $relation,
                $this
            );

            if ($related) {
                /**
                 * Delete related if there is any
                 * Stop the operation if needed
                 */
                if ($related->delete() === false) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Reads both "hasMany" and "hasOne" relations and checks the virtual
     * foreign keys (restrict) when deleting records
     *
     * @return bool
     */
    final protected function checkForeignKeysReverseRestrict(): bool
    {
        /**
         * Get the models manager
         */
        $manager = $this->modelsManager;

        /**
         * We check if some of the hasOne/hasMany relations is a foreign key
         */
        $relations = $manager->getHasOneAndHasMany($this);
        $error     = false;

        foreach ($relations as $relation) {
            /**
             * Check if the relation has a virtual foreign key
             */
            $foreignKey = $relation->getForeignKey();

            if ($foreignKey === false) {
                continue;
            }

            /**
             * By default action is restrict
             */
            $action = Relation::ACTION_RESTRICT;

            /**
             * Try to find a different action in the foreign key's options
             */
            if (is_array($foreignKey) && isset($foreignKey["action"])) {
                $action = (int)$foreignKey["action"];
            }

            /**
             * Check only if the operation is restrict
             */
            if ($action != Relation::ACTION_RESTRICT) {
                continue;
            }

            $relationClass = $relation->getReferencedModel();
            $fields        = $relation->getFields();

            if ($manager->getRelationRecords($relation, $this, null, "count")) {
                /**
                 * Create a new message
                 */
                $message = $foreignKey["message"] ?? "Record is referenced by model " . $relationClass;

                /**
                 * Create a message
                 */
                $this->appendMessage(
                    new Message(
                        $message,
                        $fields,
                        "ConstraintViolation",
                        0,
                        [
                            "model"          => get_class($this),
                            "referenceModel" => $relationClass,
                        ]
                    )
                );

                $error = true;

                break;
            }
        }

        /**
         * Call validation fails event
         */
        if ($error) {
            if (Settings::get("orm.events")) {
                $this->fireEvent("onValidationFails");
                $this->cancelOperation();
            }

            return false;
        }

        return true;
    }

    /**
     * Collects previously queried (belongs-to, has-one and has-one-through)
     * related records along with freshly added one
     *
     * @return array
     */
    protected function collectRelatedToSave(): array
    {
        /**
         * Load previously queried related records
         */
        $related = $this->related;

        /**
         * Load unsaved related records
         */
        $dirtyRelated = $this->dirtyRelated;

        foreach ($related as $name => $record) {
            if (isset($dirtyRelated[$name])) {
                continue;
            }

            if (!is_object($record) || !($record instanceof ModelInterface)) {
                continue;
            }

            if ($record->hasSnapshotData() && !$record->hasChanged()) {
                continue;
            }

            $record->setDirtyState(self::DIRTY_STATE_TRANSIENT);
            $dirtyRelated[$name] = $record;
        }

        return $dirtyRelated;
    }

    /**
     * Sends a pre-build INSERT SQL statement to the relational database system
     *
     * @param MetaDataInterface $metaData
     * @param AdapterInterface  $connection
     * @param array|string      $table
     * @param bool|string       $identityField
     *
     * @return bool
     * @throws Exception
     */
    protected function doLowInsert(
        MetaDataInterface $metaData,
        AdapterInterface $connection,
        array | string $table,
        bool | string $identityField
    ): bool {
        $attributeField      = null;
        $bindSkip            = Column::BIND_SKIP;
        $manager             = $this->modelsManager;
        $fields              = [];
        $values              = [];
        $snapshot            = [];
        $bindTypes           = [];
        $unsetDefaultValues  = [];
        $rawValues           = $this->rawValues;
        $attributes          = $metaData->getAttributes($this);
        $bindDataTypes       = $metaData->getBindTypes($this);
        $automaticAttributes = $metaData->getAutomaticCreateAttributes($this);
        $defaultValues       = $metaData->getDefaultValues($this);
        $value               = null;

        $columnMap = null;
        if (Settings::get("orm.column_renaming")) {
            $columnMap = $metaData->getColumnMap($this);
        }

        /**
         * All fields in the model makes part or the INSERT
         */
        foreach ($attributes as $field) {
            /**
             * Check if the model has a column map
             */
            if (is_array($columnMap)) {
                if (!isset($columnMap[$field])) {
                    throw new ColumnNotInTableMap($field, get_class($this));
                }

                $attributeField = $columnMap[$field];
            } else {
                $attributeField = $field;
            }

            if (!isset($automaticAttributes[$attributeField])) {
                /**
                 * Check every attribute in the model except identity field
                 */
                if ($field != $identityField) {
                    /**
                     * This isset checks that the property be defined in the
                     * model
                     */
                    if (isset($rawValues[$attributeField])) {
                        $rawValue = $rawValues[$attributeField];

                        if (!isset($bindDataTypes[$field])) {
                            throw new BindTypeNotDefined($field, get_class($this));
                        }

                        $bindType                  = $bindDataTypes[$field];
                        $fields[]                  = $field;
                        $values[]                  = $rawValue;
                        $bindTypes[]               = $bindType;
                        $snapshot[$attributeField] = $rawValue;
                    } elseif (property_exists($this, $attributeField)) {
                        $value = $this->$attributeField;
                        if ($value === null && isset($defaultValues[$field])) {
                            $snapshot[$attributeField]           = $defaultValues[$field];
                            $unsetDefaultValues[$attributeField] = $defaultValues[$field];

                            if (false === $connection->supportsDefaultValue()) {
                                continue;
                            }

                            $value = $connection->getDefaultValue();
                        } else {
                            $snapshot[$attributeField] = $value;
                        }

                        /**
                         * Every column must have a bind data type defined
                         */
                        if (!isset($bindDataTypes[$field])) {
                            throw new BindTypeNotDefined($field, get_class($this));
                        }

                        $bindType    = $bindDataTypes[$field];
                        $fields[]    = $field;
                        $values[]    = $value;
                        $bindTypes[] = $bindType;
                    } else {
                        if (isset($defaultValues[$field])) {
                            $snapshot[$attributeField]           = $defaultValues[$field];
                            $unsetDefaultValues[$attributeField] = $defaultValues[$field];

                            if (false === $connection->supportsDefaultValue()) {
                                continue;
                            }

                            $values[] = $connection->getDefaultValue();
                        } else {
                            $values[]                  = $value;
                            $snapshot[$attributeField] = $value;
                        }

                        $fields[]    = $field;
                        $bindTypes[] = $bindSkip;
                    }
                }
            }
        }

        /**
         * If there is an identity field we add it using "null" or "default"
         */
        if ($identityField !== false) {
            $defaultValue = $connection->getDefaultIdValue();

            /**
             * Not all the database systems require an explicit value for
             * identity columns
             */
            $useExplicitIdentity = $connection->useExplicitIdValue();

            if ($useExplicitIdentity) {
                $fields[] = $identityField;
            }

            /**
             * Check if the model has a column map
             */
            if (is_array($columnMap)) {
                if (!isset($columnMap[$identityField])) {
                    throw new IdentityNotInColumnMap($identityField, get_class($this));
                }

                $attributeField = $columnMap[$identityField];
            } else {
                $attributeField = $identityField;
            }

            /**
             * Check if the developer set an explicit value for the column
             */
            if (property_exists($this, $attributeField)) {
                $value = $this->$attributeField;
                if ($value === null || $value === "") {
                    if ($useExplicitIdentity) {
                        $values[]    = $defaultValue;
                        $bindTypes[] = $bindSkip;
                    } elseif (!count($values)) {
                        /**
                         * Model has only the identity column; force an
                         * explicit default so the underlying adapter does not
                         * reject the insert because of an empty values array.
                         */
                        $fields[]    = $identityField;
                        $values[]    = $defaultValue;
                        $bindTypes[] = $bindSkip;
                    }
                } else {
                    /**
                     * Add the explicit value to the field list if the user has
                     * defined a value for it
                     */
                    if (!$useExplicitIdentity) {
                        $fields[] = $identityField;
                    }

                    /**
                     * The field is valid we look for a bind value (normally int)
                     */
                    if (!isset($bindDataTypes[$identityField])) {
                        throw new IdentityNotInTableColumns($identityField, get_class($this));
                    }

                    $bindType    = $bindDataTypes[$identityField];
                    $values[]    = $value;
                    $bindTypes[] = $bindType;
                }
            } else {
                if ($useExplicitIdentity) {
                    $values[]    = $defaultValue;
                    $bindTypes[] = $bindSkip;
                } elseif (!count($values)) {
                    /**
                     * Model has only the identity column; force an explicit
                     * default so the underlying adapter does not reject the
                     * insert because of an empty values array.
                     */
                    $fields[]    = $identityField;
                    $values[]    = $defaultValue;
                    $bindTypes[] = $bindSkip;
                }
            }
        }

        /**
         * The insert will escape the table name
         */
        if (is_array($table)) {
            $table = $table[0] . "." . $table[1];
        }

        /**
         * The low level insert is performed
         */
        $success = $connection->insert($table, $values, $fields, $bindTypes);

        if ($success && $identityField !== false) {
            /**
             * We check if the model have sequences
             */
            $sequenceName = null;

            if ($connection->supportSequences()) {
                if (method_exists($this, "getSequenceName")) {
                    $sequenceName = $this->getSequenceName();
                } else {
                    $source = $this->getSource();
                    $schema = $this->getSchema();

                    if (empty($schema)) {
                        $sequenceName = $source . "_" . $identityField . "_seq";
                    } else {
                        $sequenceName = $schema . "." . $source . "_" . $identityField . "_seq";
                    }
                }
            }

            /**
             * Recover the last "insert id" and assign it to the object.
             * If an explicit identity value was provided the sequence was not
             * used, so calling lastInsertId() would fail on PostgreSQL because
             * currval() requires nextval() to have been called in the session.
             * Reuse the value already present on the model in that case.
             */
            $value = $this->$attributeField ?? null;

            if ($value !== null && $value !== "") {
                $lastInsertedId = $value;
            } else {
                $lastInsertedId = $connection->lastInsertId($sequenceName);
            }

            /**
             * If we want auto casting
             */
            if (Settings::get("orm.cast_last_insert_id_to_int")) {
                $lastInsertedId = intval($lastInsertedId, 10);
            }

            /**
             * PDO returns the auto-increment id as a numeric string. cphalcon
             * relies on the C extension's coercive property write to convert it
             * to the property's declared type. Under strict_types we coerce
             * explicitly: cast to int only when the identity property is typed
             * int, otherwise keep the original value so an unsigned BIGINT that
             * overflows PHP's int range (mapped to a string property) keeps its
             * full string representation.
             */
            if (
                is_numeric($lastInsertedId) &&
                property_exists($this, $attributeField)
            ) {
                $propertyType = (new \ReflectionProperty($this, $attributeField))
                    ->getType();

                if (
                    $propertyType instanceof \ReflectionNamedType &&
                    $propertyType->getName() === "int"
                ) {
                    $lastInsertedId = (int) $lastInsertedId;
                }
            }

            $this->$attributeField     = $lastInsertedId;
            $snapshot[$attributeField] = $lastInsertedId;

            /**
             * Since the primary key was modified, we delete the uniqueKey
             * and uniqueParams to force any future has() call to re-build
             * the primary key condition from current attribute values
             */
            $this->uniqueKey    = null;
            $this->uniqueParams = [];
        }

        if ($success) {
            /**
             * Default values from the database should be
             * written to the model attributes upon successful
             * insert.
             */
            foreach ($unsetDefaultValues as $attributeField => $defaultValue) {
                $this->$attributeField = $defaultValue;
            }

            if (
                $manager->isKeepingSnapshots($this) &&
                Settings::get("orm.update_snapshot_on_save")
            ) {
                $this->snapshot = $snapshot;
            }
        }

        return $success;
    }

    /**
     * Sends a pre-build UPDATE SQL statement to the relational database system
     *
     * @param MetaDataInterface $metaData
     * @param AdapterInterface  $connection
     * @param array|string      $table
     *
     * @return bool
     * @throws Exception
     */
    protected function doLowUpdate(
        MetaDataInterface $metaData,
        AdapterInterface $connection,
        array | string $table
    ): bool {
        $bindSkip    = Column::BIND_SKIP;
        $fields      = [];
        $values      = [];
        $bindTypes   = [];
        $newSnapshot = [];
        $rawValues   = $this->rawValues;
        $manager     = $this->modelsManager;

        /**
         * Check if the model must use dynamic update
         */
        $useDynamicUpdate    = $manager->isUsingDynamicUpdate($this);
        $snapshot            = $this->snapshot;
        $dataTypes           = $metaData->getDataTypes($this);
        $bindDataTypes       = $metaData->getBindTypes($this);
        $defaultValues       = $metaData->getDefaultValues($this);
        $nonPrimary          = $metaData->getNonPrimaryKeyAttributes($this);
        $automaticAttributes = $metaData->getAutomaticUpdateAttributes($this);

        $columnMap = null;
        if (Settings::get("orm.column_renaming")) {
            $columnMap = $metaData->getColumnMap($this);
        }

        if ($useDynamicUpdate && is_array($snapshot)) {
            foreach ($nonPrimary as $field) {
                $changed = false;
                if (is_array($columnMap)) {
                    if (!isset($columnMap[$field])) {
                        if (!Settings::get("orm.ignore_unknown_columns")) {
                            throw new ColumnNotInTableMap($field, get_class($this));
                        }
                    }

                    $attributeField = $columnMap[$field];
                } else {
                    $attributeField = $field;
                }
                if (!isset($automaticAttributes[$attributeField])) {
                    /**
                     * Check a bind type for field to update
                     */
                    if (!isset($bindDataTypes[$field])) {
                        throw new BindTypeNotDefined($field, get_class($this));
                    }

                    $bindType = $bindDataTypes[$field];

                    /**
                     * Get the field's value
                     * If a field isn't set there was no change
                     */
                    if (isset($rawValues[$attributeField])) {
                        $rawValue                     = $rawValues[$attributeField];
                        $fields[]                     = $field;
                        $values[]                     = $rawValue;
                        $bindTypes[]                  = $bindType;
                        $newSnapshot[$attributeField] = $rawValue;
                    } elseif (property_exists($this, $attributeField)) {
                        $value = $this->$attributeField;
                        /**
                         * If the field is not part of the snapshot we add them as changed
                         */
                        if (!array_key_exists($attributeField, $snapshot)) {
                            $changed = true;
                        } else {
                            $snapshotValue = $snapshot[$attributeField];
                            /**
                             * See https://github.com/phalcon/cphalcon/issues/3247
                             * Take a TEXT column with value '4' and replace it by
                             * the value '4.0'. For PHP '4' and '4.0' are the same.
                             * We can't use simple comparison...
                             *
                             * We must use the type of snapshotValue.
                             */
                            if ($value === null) {
                                $changed = $snapshotValue !== null;
                            } else {
                                if ($snapshotValue === null) {
                                    $changed = true;
                                } else {
                                    if (!isset($dataTypes[$field])) {
                                        throw new DataTypeNotDefined($field, get_class($this));
                                    }

                                    $dataType = $dataTypes[$field];

                                    /**
                                     * Get actual values before comparison
                                     */
                                    if (
                                        is_object($snapshotValue) &&
                                        $snapshotValue instanceof RawValue
                                    ) {
                                        $snapshotValue = $snapshotValue->getValue();
                                    }

                                    $updateValue = $value;
                                    if (
                                        is_object($value) &&
                                        $value instanceof RawValue
                                    ) {
                                        $updateValue = $value->getValue();
                                    }

                                    $changed = match ($dataType) {
                                        Column::TYPE_BOOLEAN    => (bool)$snapshotValue !== (bool)$updateValue,
                                        Column::TYPE_DECIMAL,
                                        Column::TYPE_FLOAT      =>
                                            floatval($snapshotValue) !== floatval($updateValue),
                                        Column::TYPE_INTEGER,
                                        Column::TYPE_DATE,
                                        Column::TYPE_DATETIME,
                                        Column::TYPE_CHAR,
                                        Column::TYPE_TEXT,
                                        Column::TYPE_VARCHAR,
                                        Column::TYPE_BIGINTEGER => (string)$snapshotValue !== (string)$updateValue,
                                        default                 => $updateValue != $snapshotValue,
                                    };
                                }
                            }
                        }
                        /**
                         * Only changed values are added to the SQL Update
                         */
                        if ($changed) {
                            $fields[]    = $field;
                            $values[]    = $value;
                            $bindTypes[] = $bindType;
                        }
                        $newSnapshot[$attributeField] = $value;
                    } else {
                        $newSnapshot[$attributeField] = null;
                    }
                }
            }
            /**
             * If there is no fields to update we return true
             */
            if (!count($fields)) {
                $this->oldSnapshot = $snapshot;
                return true;
            }
        } else {
            /**
             * We only make the update based on the non-primary attributes, values
             * in primary key attributes are ignored
             */
            foreach ($nonPrimary as $field) {
                /**
                 * Check if the model has a column map
                 */
                if (is_array($columnMap)) {
                    if (!isset($columnMap[$field])) {
                        if (!Settings::get("orm.ignore_unknown_columns")) {
                            throw new ColumnNotInTableMap($field, get_class($this));
                        }
                    }

                    $attributeField = $columnMap[$field];
                } else {
                    $attributeField = $field;
                }

                if (!isset($automaticAttributes[$attributeField])) {
                    /**
                     * Check a bind type for field to update
                     */
                    if (!isset($bindDataTypes[$field])) {
                        throw new BindTypeNotDefined($field, get_class($this));
                    }

                    $bindType = $bindDataTypes[$field];

                    /**
                     * Get the field's value
                     * If a field isn't set we pass a null value
                     */
                    if (isset($rawValues[$attributeField])) {
                        $rawValue                     = $rawValues[$attributeField];
                        $fields[]                     = $field;
                        $values[]                     = $rawValue;
                        $bindTypes[]                  = $bindType;
                        $newSnapshot[$attributeField] = $rawValue;
                    } elseif (property_exists($this, $attributeField)) {
                        $value = $this->$attributeField;

                        /**
                         * Skip columns whose value is still the function-call
                         * default from the DB (e.g. "gen_random_uuid()"). Passing
                         * such a string as a bound parameter would fail type
                         * validation on the DB side.
                         */
                        if (
                            is_string($value) &&
                            isset($defaultValues[$field]) &&
                            $value === $defaultValues[$field] &&
                            str_contains($value, "(")
                        ) {
                            $newSnapshot[$attributeField] = $value;

                            continue;
                        }

                        /**
                         * When dynamic update is not used we pass every field to the update
                         */
                        $fields[]                     = $field;
                        $values[]                     = $value;
                        $bindTypes[]                  = $bindType;
                        $newSnapshot[$attributeField] = $value;
                    } else {
                        $newSnapshot[$attributeField] = null;
                        $fields[]                     = $field;
                        $values[]                     = null;
                        $bindTypes[]                  = $bindSkip;
                    }
                }
            }
        }

        /**
         * If there is no fields to update we return true
         */
        if (!count($fields)) {
            return true;
        }

        $uniqueKey    = $this->uniqueKey;
        $uniqueParams = $this->uniqueParams;

        /**
         * When unique params is null we need to rebuild the bind params
         */
        if (!is_array($uniqueParams)) {
            $primaryKeys = $metaData->getPrimaryKeyAttributes($this);

            /**
             * We can't create dynamic SQL without a primary key
             */
            if (!count($primaryKeys)) {
                throw new PrimaryKeyRequired(get_class($this));
            }

            $uniqueParams = [];

            foreach ($primaryKeys as $field) {
                /**
                 * Check if the model has a column map
                 */
                if (is_array($columnMap)) {
                    if (!isset($columnMap[$field])) {
                        throw new ColumnNotInTableMap($field, get_class($this));
                    }

                    $attributeField = $columnMap[$field];
                } else {
                    $attributeField = $field;
                }

                if (isset($this->$attributeField)) {
                    $value                        = $this->$attributeField;
                    $newSnapshot[$attributeField] = $value;
                    $uniqueParams[]               = $value;
                } else {
                    $newSnapshot[$attributeField] = null;
                    $uniqueParams[]               = null;
                }
            }
        }

        /**
         * The insert will escape the table name
         */
        if (is_array($table)) {
            $table = $table[0] . "." . $table[1];
        }

        /**
         * We build the conditions as an array
         * Perform the low level update
         */
        $success = $connection->update(
            $table,
            $fields,
            $values,
            [
                "conditions" => $uniqueKey,
                "bind"       => $uniqueParams,
                "bindTypes"  => $this->uniqueTypes,
            ],
            $bindTypes
        );

        if (
            $success &&
            $manager->isKeepingSnapshots($this) &&
            Settings::get("orm.update_snapshot_on_save")
        ) {
            if (is_array($snapshot)) {
                $this->oldSnapshot = $snapshot;
                $this->snapshot    = array_merge($snapshot, $newSnapshot);
            } else {
                $this->oldSnapshot = [];
                $this->snapshot    = $newSnapshot;
            }
        }

        return $success;
    }

    /**
     * Returns related records defined relations depending on the method name.
     * Returns false if the relation is non-existent.
     *
     * @param string $modelName
     * @param string $method
     * @param array  $arguments
     *
     * @return false|int|mixed|Simple|ModelInterface
     * @throws Exception
     */
    protected function getRelatedRecords(
        string $modelName,
        string $method,
        array $arguments
    ) {
        $relation    = false;
        $queryMethod = null;
        $extraArgs   = $arguments[0] ?? null;

        /**
         * Calling find/findFirst if the method starts with "get"
         */
        if (str_starts_with($method, "get")) {
            $alias    = substr($method, 3);
            $relation = $this->modelsManager->getRelationByAlias(
                $modelName,
                $alias
            );

            /**
             * Return if the relation was not found because getRelated()
             * throws an exception if the relation is unknown
             */
            if (!is_object($relation)) {
                return false;
            }

            return $this->getRelated($alias, $extraArgs);
        }

        /**
         * Calling count if the method starts with "count"
         */
        if (str_starts_with($method, "count")) {
            $queryMethod = "count";
            $relation    = $this->modelsManager->getRelationByAlias(
                $modelName,
                substr($method, 5)
            );

            /**
             * If the relation was found perform the query via the models manager
             */
            if (!is_object($relation)) {
                return false;
            }

            return $this->modelsManager->getRelationRecords(
                $relation,
                $this,
                $extraArgs,
                $queryMethod
            );
        }

        return false;
    }

    /**
     * Generate a PHQL SELECT statement for an aggregate
     *
     * @param string            $functionName
     * @param string            $alias
     * @param array|string|null $parameters
     *
     * @return mixed
     */
    protected static function groupResult(
        string $functionName,
        string $alias,
        array | string | null $parameters = null
    ): mixed {
        $bindParams = [];
        $bindTypes  = [];

        $container = Di::getDefault();
        $manager   = $container->get("modelsManager");

        if (!is_array($parameters)) {
            $params = [];

            if ($parameters !== null) {
                $params[] = $parameters;
            }
        } else {
            $params = $parameters;
        }

        $groupColumn = $params["column"] ?? "*";

        /**
         * Builds the columns to query according to the received parameters
         */
        if (isset($params["distinct"])) {
            $distinctColumn = $params["distinct"];
            $columns        = $functionName . "(DISTINCT " . $distinctColumn . ") AS " . $alias;
        } else {
            if (isset($params["group"])) {
                $groupColumns = $params["group"];
                $columns      = $groupColumns . ", " . $functionName . "(" . $groupColumn . ") AS " . $alias;
            } else {
                $columns = $functionName . "(" . $groupColumn . ") AS " . $alias;
            }
        }

        /**
         * Builds a query with the passed parameters
         */
        $builder = $manager->createBuilder($params);

        $builder->columns($columns);
        $builder->from(get_called_class());

        $query = $builder->getQuery();

        if (isset($params[self::TRANSACTION_INDEX])) {
            $transaction = $params[self::TRANSACTION_INDEX];
            if ($transaction instanceof TransactionInterface) {
                $query->setTransaction($transaction);
            }
        }

        /**
         * Check for bind parameters
         */
        if (isset($params["bind"])) {
            $bindParams = $params["bind"];

            if (isset($params["bindTypes"])) {
                $bindTypes = $params["bindTypes"];
            }
        }

        /**
         * Pass the cache options to the query
         */
        if (isset($params["cache"])) {
            $cache = $params["cache"];
            $query->cache($cache);
        }

        /**
         * Execute the query
         */
        $resultset = $query->execute($bindParams, $bindTypes);

        /**
         * Return the full resultset if the query is grouped
         */
        if (isset($params["group"])) {
            return $resultset;
        }

        /**
         * Return only the value in the first result
         */
        $firstRow = $resultset->getFirst();

        return $firstRow->$alias;
    }

    /**
     * Checks whether the current record already exists
     *
     * @param MetaDataInterface $metaData
     * @param AdapterInterface  $connection
     *
     * @return bool
     * @throws Exception
     */
    protected function has(
        MetaDataInterface $metaData,
        AdapterInterface $connection
    ): bool {
        $uniqueParams = null;
        $uniqueTypes  = null;

        /**
         * Builds a unique primary key condition
         */
        $uniqueKey = $this->uniqueKey;

        if ($uniqueKey === null) {
            $primaryKeys   = $metaData->getPrimaryKeyAttributes($this);
            $bindDataTypes = $metaData->getBindTypes($this);
            $numberPrimary = count($primaryKeys);

            if (!$numberPrimary) {
                return false;
            }

            /**
             * Check if column renaming is globally activated
             */
            $columnMap = null;
            if (Settings::get("orm.column_renaming")) {
                $columnMap = $metaData->getColumnMap($this);
            }

            $numberEmpty  = 0;
            $wherePk      = [];
            $uniqueParams = [];
            $uniqueTypes  = [];

            /**
             * We need to create a primary key based on the current data
             */
            foreach ($primaryKeys as $field) {
                if (is_array($columnMap)) {
                    if (!isset($columnMap[$field])) {
                        throw new ColumnNotInTableMap($field, get_class($this));
                    }
                    $attributeField = $columnMap[$field];
                } else {
                    $attributeField = $field;
                }

                /**
                 * If the primary key attribute is set append it to the
                 * conditions
                 */
                $value = null;

                if (isset($this->$attributeField)) {
                    $value = $this->$attributeField;

                    /**
                     * We count how many fields are empty, if all fields are
                     * empty we don't perform an 'exist' check
                     */
                    if ($value === null || $value === "") {
                        $numberEmpty++;
                    }

                    $uniqueParams[] = $value;
                } else {
                    $uniqueParams[] = null;
                    $numberEmpty++;
                }

                if (!isset($bindDataTypes[$field])) {
                    throw new ColumnNotInTableColumns($field, get_class($this));
                }

                $type          = $bindDataTypes[$field];
                $uniqueTypes[] = $type;
                $wherePk[]     = $connection->escapeIdentifier($field) . " = ?";
            }

            /**
             * There are no primary key fields defined, assume the record does
             * not exist
             */
            if ($numberPrimary == $numberEmpty) {
                return false;
            }

            $joinWhere = implode(" AND ", $wherePk);

            /**
             * The unique key is composed of 3 parts uniqueKey, uniqueParams,
             * uniqueTypes
             */
            $this->uniqueKey    = $joinWhere;
            $this->uniqueParams = $uniqueParams;
            $this->uniqueTypes  = $uniqueTypes;
            $uniqueKey          = $joinWhere;
        }

        /**
         * If we already know if the record exists we don't check it
         */
        if (!$this->dirtyState) {
            return true;
        }

        if ($uniqueKey === null) {
            $uniqueKey = $this->uniqueKey;
        }

        if ($uniqueParams === null) {
            $uniqueParams = $this->uniqueParams;
        }

        if ($uniqueTypes === null) {
            $uniqueTypes = $this->uniqueTypes;
        }

        $schema = $this->getSchema();
        $source = $this->getSource();

        if ($schema) {
            $table = [$schema, $source];
        } else {
            $table = $source;
        }

        /**
         * Here we use a single COUNT(*) without PHQL to make the execution
         * faster
         */
        $num = $connection->fetchOne(
            "SELECT COUNT(*) \"rowcount\" FROM "
            . $connection->escapeIdentifier($table)
            . " WHERE " . $uniqueKey,
            2,
            $uniqueParams,
            $uniqueTypes
        );

        if ($num["rowcount"]) {
            $this->dirtyState = self::DIRTY_STATE_PERSISTENT;

            return true;
        } else {
            $this->dirtyState = self::DIRTY_STATE_TRANSIENT;
        }

        return false;
    }

    /**
     * Setup a 1-n relation between two models
     *
     *```php
     * class Robots extends \Phalcon\Mvc\Model
     * {
     *     public function initialize()
     *     {
     *         $this->hasMany(
     *             "id",
     *             RobotsParts::class,
     *             "robots_id"
     *         );
     *     }
     * }
     *```
     *
     * @param mixed        $fields
     * @param string       $referenceModel
     * @param string|array $referencedFields
     * @param array  $options {
     *
     * @option bool   "reusable"
     * @option string "alias"
     * @option array  "foreignKey" {
     * @option string|null "message"
     * @option bool        "allowNulls"
     * @option string|null "action"
     *      }
     * @option array params {
     * @option string "conditions"
     * @option string "columns"
     * @option array  "bind"
     * @option array  "bindTypes"
     * @option string "order"
     * @option int    "limit"
     * @option int    "offset"
     * @option string "group"
     * @option bool   "for_updated"
     * @option bool   "shared_lock"
     * @option array  "cache" {
     * @option int    "lifetime"
     * @option string "key"
     *          }
     * @option string "hydration"
     * }
     *
     * @return Relation
     */
    protected function hasMany(
        mixed $fields,
        string $referenceModel,
        mixed $referencedFields,
        array $options = []
    ): Relation {
        return $this->modelsManager->addHasMany(
            $this,
            $fields,
            $referenceModel,
            $referencedFields,
            $options
        );
    }

    /**
     * Setup an n-n relation between two models, through an intermediate
     * relation
     *
     *```php
     * class Robots extends \Phalcon\Mvc\Model
     * {
     *     public function initialize()
     *     {
     *         // Setup a many-to-many relation to Parts through RobotsParts
     *         $this->hasManyToMany(
     *             "id",
     *             RobotsParts::class,
     *             "robots_id",
     *             "parts_id",
     *             Parts::class,
     *             "id",
     *         );
     *     }
     * }
     *```
     *
     * @param mixed        $fields
     * @param string       $intermediateModel
     * @param array|string $intermediateFields
     * @param array|string $intermediateReferencedFields
     * @param string       $referenceModel
     * @param string       $referencedFields
     * @param array        $options {
     *
     * @option bool   "reusable"
     * @option string "alias"
     * @option array  "foreignKey" {
     * @option string|null "message"
     * @option bool        "allowNulls"
     * @option string|null "action"
     *      }
     * @option array params {
     * @option string "conditions"
     * @option string "columns"
     * @option array  "bind"
     * @option array  "bindTypes"
     * @option string "order"
     * @option int    "limit"
     * @option int    "offset"
     * @option string "group"
     * @option bool   "for_updated"
     * @option bool   "shared_lock"
     * @option array  "cache" {
     * @option int    "lifetime"
     * @option string "key"
     *          }
     * @option string "hydration"
     * }
     *
     * @return Relation
     */
    protected function hasManyToMany(
        mixed $fields,
        string $intermediateModel,
        mixed $intermediateFields,
        mixed $intermediateReferencedFields,
        string $referenceModel,
        mixed $referencedFields,
        array $options = []
    ): Relation {
        return $this->modelsManager->addHasManyToMany(
            $this,
            $fields,
            $intermediateModel,
            $intermediateFields,
            $intermediateReferencedFields,
            $referenceModel,
            $referencedFields,
            $options
        );
    }

    /**
     * Setup a 1-1 relation between two models
     *
     *```php
     * class Robots extends \Phalcon\Mvc\Model
     * {
     *     public function initialize()
     *     {
     *         $this->hasOne(
     *             "id",
     *             RobotsDescription::class,
     *             "robots_id"
     *         );
     *     }
     * }
     *```
     *
     * @param mixed  $fields
     * @param string $referenceModel
     * @param string $referencedFields
     * @param array  $options {
     *
     * @option bool   "reusable"
     * @option string "alias"
     * @option array  "foreignKey" {
     * @option string|null "message"
     * @option bool        "allowNulls"
     * @option string|null "action"
     *      }
     * @option array params {
     * @option string "conditions"
     * @option string "columns"
     * @option array  "bind"
     * @option array  "bindTypes"
     * @option string "order"
     * @option int    "limit"
     * @option int    "offset"
     * @option string "group"
     * @option bool   "for_updated"
     * @option bool   "shared_lock"
     * @option array  "cache" {
     * @option int    "lifetime"
     * @option string "key"
     *          }
     * @option string "hydration"
     * }
     *
     * @return Relation
     */
    protected function hasOne(
        mixed $fields,
        string $referenceModel,
        mixed $referencedFields,
        array $options = []
    ): Relation {
        return $this->modelsManager->addHasOne(
            $this,
            $fields,
            $referenceModel,
            $referencedFields,
            $options
        );
    }

    /**
     * Setup a 1-1 relation between two models, through an intermediate
     * relation
     *
     *```php
     * class Robots extends \Phalcon\Mvc\Model
     * {
     *     public function initialize()
     *     {
     *         // Setup a 1-1 relation to one item from Parts through RobotsParts
     *         $this->hasOneThrough(
     *             "id",
     *             RobotsParts::class,
     *             "robots_id",
     *             "parts_id",
     *             Parts::class,
     *             "id",
     *         );
     *     }
     * }
     *```
     *
     * @param mixed        $fields
     * @param string       $intermediateModel
     * @param array|string $intermediateFields
     * @param array|string $intermediateReferencedFields
     * @param string       $referenceModel
     * @param string       $referencedFields
     * @param array        $options {
     *
     * @option bool   "reusable"
     * @option string "alias"
     * @option array  "foreignKey" {
     * @option string|null "message"
     * @option bool        "allowNulls"
     * @option string|null "action"
     *      }
     * @option array params {
     * @option string "conditions"
     * @option string "columns"
     * @option array  "bind"
     * @option array  "bindTypes"
     * @option string "order"
     * @option int    "limit"
     * @option int    "offset"
     * @option string "group"
     * @option bool   "for_updated"
     * @option bool   "shared_lock"
     * @option array  "cache" {
     * @option int    "lifetime"
     * @option string "key"
     *          }
     * @option string "hydration"
     * }
     *
     * @return Relation
     */
    protected function hasOneThrough(
        mixed $fields,
        string $intermediateModel,
        mixed $intermediateFields,
        mixed $intermediateReferencedFields,
        string $referenceModel,
        mixed $referencedFields,
        array $options = []
    ): Relation {
        return $this->modelsManager->addHasOneThrough(
            $this,
            $fields,
            $intermediateModel,
            $intermediateFields,
            $intermediateReferencedFields,
            $referenceModel,
            $referencedFields,
            $options
        );
    }

    /**
     * Try to check if the query must invoke a finder
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return ModelInterface[]|ModelInterface|bool|void
     * @throws Exception
     */
    final protected static function invokeFinder(
        string $method,
        array $arguments
    ) {
        $extraMethod = null;
        $type        = "find";

        /**
         * Check if the method starts with "findFirst"
         */
        if (str_starts_with($method, "findFirstBy")) {
            $type        = "findFirst";
            $extraMethod = substr($method, 11);
        } elseif (str_starts_with($method, "findBy")) {
            /**
             * Check if the method starts with "find"
             */
            $type        = "find";
            $extraMethod = substr($method, 6);
        } elseif (str_starts_with($method, "countBy")) {
            /**
             * Check if the method starts with "count"
             */
            $type        = "count";
            $extraMethod = substr($method, 7);
        }

        /**
         * The called class is the model
         */
        $modelName = get_called_class();

        if (!$extraMethod) {
            return false;
        }

        if (!array_key_exists(0, $arguments)) {
            throw new StaticMethodRequiresOneArgument($method, get_called_class());
        }

        $model    = new $modelName();
        $metaData = $model->getModelsMetaData();

        /**
         * Get the attributes
         */
        $attributes = $metaData->getReverseColumnMap($model);

        if (!is_array($attributes)) {
            $attributes = $metaData->getDataTypes($model);
        }

        /**
         * Check if the extra-method is an attribute
         */
        if (isset($attributes[$extraMethod])) {
            $field = $extraMethod;
        } else {
            /**
             * Lowercase the first letter of the extra-method
             */
            $extraMethodFirst = lcfirst($extraMethod);

            if (isset($attributes[$extraMethodFirst])) {
                $field = $extraMethodFirst;
            } else {
                /**
                 * Get the possible real method name
                 */
                $field = self::staticToUncamelize($extraMethod);

                if (!isset($attributes[$field])) {
                    throw new CannotResolveAttribute($extraMethod, get_called_class());
                }
            }
        }

        /**
         * Check if we have "conditions" and "bind" defined
         */
        $value = $arguments[0] ?? null;

        if ($value !== null) {
            $params = [
                "conditions" => "[" . $field . "] = ?0",
                "bind"       => [$value],
            ];
        } else {
            $params = [
                "conditions" => "[" . $field . "] IS NULL",
            ];
        }

        /**
         * Just in case remove 'conditions' and 'bind'
         */
        unset($arguments[0]);
        unset($arguments["conditions"]);
        unset($arguments["bind"]);

        $params = array_merge($params, $arguments);

        /**
         * Execute the query
         */
        return $modelName::$type($params);
    }

    /**
     * Sets if the model must keep the original record snapshot in memory
     *
     *```php
     * use Phalcon\Mvc\Model;
     *
     * class Robots extends Model
     * {
     *     public function initialize()
     *     {
     *         $this->keepSnapshots(true);
     *     }
     * }
     *```
     *
     * @param bool $keepSnapshot
     *
     * @return void
     */
    protected function keepSnapshots(bool $keepSnapshot): void
    {
        $this->modelsManager->keepSnapshots(
            $this,
            $keepSnapshot
        );
    }

    /**
     * Check for, and attempt to use, possible setter.
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return bool
     */
    final protected function possibleSetter(
        string $property,
        mixed $value
    ): bool {
        $localMethods = [
            "setConnectionService"      => 1,
            "setDirtyState"             => 1,
            "setEventsManager"          => 1,
            "setReadConnectionService"  => 1,
            "setOldSnapshotData"        => 1,
            "setSchema"                 => 1,
            "setSnapshotData"           => 1,
            "setSource"                 => 1,
            "setTransaction"            => 1,
            "setWriteConnectionService" => 1,
        ];


        $possibleSetter = "set" . $this->toCamelize($property);

        if (!method_exists($this, $possibleSetter)) {
            return false;
        }

        if (isset($localMethods[$possibleSetter])) {
            return false;
        }

        try {
            $this->$possibleSetter($value);
        } catch (\TypeError) {
            $this->$property = $value;
        }

        return true;
    }

    /**
     * Executes internal events after save a record
     *
     * @param bool $success
     * @param bool $exists
     *
     * @return bool
     */
    protected function postSave(bool $success, bool $exists): bool
    {
        if ($success) {
            if ($exists) {
                $this->fireEvent("afterUpdate");
            } else {
                $this->fireEvent("afterCreate");
            }
        }

        return $success;
    }

    /**
     * Save the related records assigned in the has-one/has-many relations
     *
     * @param AdapterInterface    $connection
     * @param array               $related
     * @param CollectionInterface $visited
     *
     * @return bool
     * @throws Exception
     */
    protected function postSaveRelatedRecords(
        AdapterInterface $connection,
        array $related,
        CollectionInterface $visited
    ): bool {
        $nesting   = false;
        $className = get_class($this);
        $manager   = $this->getModelsManager();

        foreach ($related as $name => $record) {
            /**
             * Try to get a relation with the same name
             */
            $relation = $manager->getRelationByAlias(
                $className,
                $name
            );

            if (is_object($relation)) {
                /**
                 * Discard belongsTo relations
                 */
                if ($relation->getType() == Relation::BELONGS_TO) {
                    continue;
                }

                if (!is_object($record) && !is_array($record)) {
                    $connection->rollback($nesting);

                    throw new RelationRequiresObjectOrArray($className, $name);
                }

                $columns          = $relation->getFields();
                $referencedModel  = $relation->getReferencedModel();
                $referencedFields = $relation->getReferencedFields();

                /**
                 * Create an implicit array for has-many/has-one records
                 */
                if (is_object($record)) {
                    $relatedRecords = [$record];
                } else {
                    $relatedRecords = $record;
                }

                $isThrough = $relation->isThrough();

                /**
                 * Many-to-Many
                 */
                if ($isThrough) {
                    $intermediateModelName        = $relation->getIntermediateModel();
                    $intermediateFields           = $relation->getIntermediateFields();
                    $intermediateReferencedFields = $relation->getIntermediateReferencedFields();
                    $placeholders                 = [];
                    $conditions                   = [];
                    $columnCount                  = 0;
                    $keptKeys                     = [];

                    /**
                     * Resolve sync behavior: per-save override (specific alias,
                     * then "*" wildcard) wins over the relation's `sync` option.
                     */
                    $doSync = (bool) $relation->getOption("sync");

                    if (isset($this->syncRelated[$name])) {
                        $doSync = (bool) $this->syncRelated[$name];
                    } elseif (isset($this->syncRelated["*"])) {
                        $doSync = (bool) $this->syncRelated["*"];
                    }

                    /**
                     * Always check for existing intermediate models
                     * otherwise conflicts will arise on insert instead of update
                     */
                    if (is_array($columns)) {
                        $columnCount = count($columns) - 1;

                        for ($i = 0; $i <= $columnCount; $i++) {
                            $columnA                  = $columns[$i];
                            $conditions[]             = "[" . $intermediateFields[$i] . "] = :APR" . $i . ":";
                            $placeholders["APR" . $i] = $this->readAttribute($columnA);
                        }

                        $i = $columnCount + 1;
                    } else {
                        $conditions[]         = "[" . $intermediateFields . "] = :APR0:";
                        $placeholders["APR0"] = $this->readAttribute($columns);
                        $i                    = 1;
                    }

                    foreach ($relatedRecords as $recordAfter) {
                        /**
                         * Save the record and get messages
                         */
                        if (!$recordAfter->doSave($visited)) {
                            /**
                             * Get the validation messages generated by the
                             * referenced model
                             */
                            $this->appendMessagesFrom($recordAfter);

                            /**
                             * Rollback the implicit transaction
                             */
                            $connection->rollback($nesting);

                            return false;
                        }

                        /**
                         * Build per-iteration query: start from parent conditions, add
                         * child (referenced) conditions for HAS_MANY_THROUGH so that
                         * conditions and their placeholder values are always in sync.
                         */
                        $loopConditions   = $conditions;
                        $loopPlaceholders = $placeholders;

                        if ($relation->getType() === Relation::HAS_MANY_THROUGH) {
                            if (is_array($referencedFields)) {
                                $referencedFieldsCount = count($referencedFields) - 1;

                                for ($j = 0; $j <= $referencedFieldsCount; $j++) {
                                    $columnA          = $referencedFields[$j];
                                    $t                = $j + $i;
                                    $loopConditions[] = "[" . $intermediateReferencedFields[$j] . "] = :APR" . $t . ":";
                                    $loopPlaceholders["APR" . $t] = $recordAfter->readAttribute($columnA);
                                }
                            } else {
                                $loopConditions[] = "[" . $intermediateReferencedFields . "] = :APR" . $i . ":";
                                $loopPlaceholders["APR" . $i] = $recordAfter->readAttribute($referencedFields);
                            }
                        }

                        /**
                         * Create a new instance of the intermediate model
                         */
                        $intermediateModel = $manager->load(
                            $intermediateModelName
                        );

                        /**
                         * If it already exist, it can be updated with the new referenced key.
                         */
                        $existingIntermediateModel = $intermediateModel->findFirst(
                            [
                                implode(" AND ", $loopConditions),
                                "bind" => $loopPlaceholders,
                            ]
                        );

                        if ($existingIntermediateModel) {
                            $intermediateModel = $existingIntermediateModel;
                        }

                        if (
                            !$existingIntermediateModel ||
                            $relation->getType() === Relation::HAS_ONE_THROUGH
                        ) {
                            /**
                             * Write value in the intermediate model
                             */
                            if (is_array($columns)) {
                                for ($h = 0; $h <= $columnCount; $h++) {
                                    $columnA = $columns[$h];
                                    $columnB = $intermediateFields[$h];

                                    $intermediateModel->writeAttribute(
                                        $columnB,
                                        $this->readAttribute($columnA)
                                    );
                                }
                            } else {
                                $intermediateModel->writeAttribute(
                                    $intermediateFields,
                                    $this->readAttribute($columns)
                                );
                            }

                            if (is_array($referencedFields)) {
                                $referencedFieldsCount = count($referencedFields) - 1;

                                for ($h = 0; $h <= $referencedFieldsCount; $h++) {
                                    $columnA = $referencedFields[$h];
                                    $columnB = $intermediateReferencedFields[$h];

                                    $intermediateModel->writeAttribute(
                                        $columnB,
                                        $recordAfter->readAttribute($columnA)
                                    );
                                }
                            } else {
                                $intermediateModel->writeAttribute(
                                    $intermediateReferencedFields,
                                    $recordAfter->readAttribute($referencedFields)
                                );
                            }
                        }

                        /**
                         * Save the record and get messages
                         */
                        if (!$intermediateModel->doSave($visited)) {
                            /**
                             * Get the validation messages generated by the referenced model
                             */
                            $this->appendMessagesFrom($intermediateModel);

                            /**
                             * Rollback the implicit transaction
                             */
                            $connection->rollback($nesting);

                            return false;
                        }

                        /**
                         * Track the referenced key of each kept record so that
                         * sync can delete the intermediate rows left behind.
                         */
                        if ($doSync) {
                            if (is_array($referencedFields)) {
                                $keepKey = "";

                                foreach ($referencedFields as $columnA) {
                                    $keepKey .= $recordAfter->readAttribute($columnA) . "|";
                                }
                            } else {
                                $keepKey = (string) $recordAfter->readAttribute($referencedFields);
                            }

                            $keptKeys[$keepKey] = true;
                        }
                    }

                    /**
                     * Sync: remove intermediate rows for records that are no
                     * longer present in the assigned array. Only applies to
                     * many-to-many (HAS_MANY_THROUGH).
                     */
                    if (
                        $doSync &&
                        $relation->getType() === Relation::HAS_MANY_THROUGH
                    ) {
                        $intermediateModel = $manager->load(
                            $intermediateModelName
                        );

                        $existingRecords = $intermediateModel->find(
                            [
                                implode(" AND ", $conditions),
                                "bind" => $placeholders,
                            ]
                        );

                        foreach ($existingRecords as $existingRecord) {
                            if (is_array($intermediateReferencedFields)) {
                                $keepKey = "";

                                foreach ($intermediateReferencedFields as $columnB) {
                                    $keepKey .= $existingRecord->readAttribute($columnB) . "|";
                                }
                            } else {
                                $keepKey = (string) $existingRecord->readAttribute(
                                    $intermediateReferencedFields
                                );
                            }

                            if (!isset($keptKeys[$keepKey])) {
                                if (!$existingRecord->delete()) {
                                    $this->appendMessagesFrom($existingRecord);

                                    $connection->rollback($nesting);

                                    return false;
                                }
                            }
                        }
                    }
                } else {
                    if (is_array($columns)) {
                        $columnCount = count($columns) - 1;

                        foreach ($relatedRecords as $recordAfter) {
                            for ($i = 0; $i <= $columnCount; $i++) {
                                $columnA = $columns[$i];
                                $columnB = $referencedFields[$i];

                                $recordAfter->writeAttribute(
                                    $columnB,
                                    $this->readAttribute($columnA)
                                );
                            }

                            /**
                             * Save the record and get messages
                             */
                            if (!$recordAfter->doSave($visited)) {
                                /**
                                 * Get the validation messages generated by the
                                 * referenced model
                                 */
                                $this->appendMessagesFrom($recordAfter);

                                /**
                                 * Rollback the implicit transaction
                                 */
                                $connection->rollback($nesting);

                                return false;
                            }
                        }
                    } else {
                        foreach ($relatedRecords as $recordAfter) {
                            /**
                             * Assign the value to the
                             */
                            $recordAfter->writeAttribute(
                                $referencedFields,
                                $this->readAttribute($columns)
                            );

                            /**
                             * Save the record and get messages
                             */
                            if (!$recordAfter->doSave($visited)) {
                                /**
                                 * Get the validation messages generated by the
                                 * referenced model
                                 */
                                $this->appendMessagesFrom($recordAfter);

                                /**
                                 * Rollback the implicit transaction
                                 */
                                $connection->rollback($nesting);

                                return false;
                            }
                        }
                    }
                }
            } else {
                if (!is_array($record)) {
                    $connection->rollback($nesting);

                    throw new RelationNotDefined($className, $name);
                }
            }
        }

        /**
         * Commit the implicit transaction
         */
        $connection->commit($nesting);

        return true;
    }

    /**
     * Executes internal hooks before save a record
     *
     * @param MetaDataInterface $metaData
     * @param bool              $exists
     * @param mixed             $identityField
     *
     * @return bool
     * @throws Exception
     */
    protected function preSave(
        MetaDataInterface $metaData,
        bool $exists,
        mixed $identityField
    ): bool {
        /**
         * Run Validation Callbacks Before
         */
        if (Settings::get("orm.events")) {
            /**
             * Call the beforeValidation
             */
            if ($this->fireEventCancel("beforeValidation") === false) {
                return false;
            }

            /**
             * Call the specific beforeValidation event for the current action
             */
            if ($exists) {
                $eventName = "beforeValidationOnUpdate";
            } else {
                $eventName = "beforeValidationOnCreate";
            }

            if ($this->fireEventCancel($eventName) === false) {
                return false;
            }
        }

        /**
         * Check for Virtual foreign keys
         */
        if (
            Settings::get("orm.virtual_foreign_keys") &&
            $this->checkForeignKeysRestrict() === false
        ) {
            return false;
        }

        /**
         * Columns marked as not null are automatically validated by the ORM
         */
        if (Settings::get("orm.not_null_validations")) {
            $notNull = $metaData->getNotNullAttributes($this);

            if (is_array($notNull)) {
                /**
                 * Gets the fields that are numeric, these are validated in a
                 * different way
                 */
                $dataTypeNumeric = $metaData->getDataTypesNumeric($this);

                $columnMap = null;
                if (Settings::get("orm.column_renaming")) {
                    $columnMap = $metaData->getColumnMap($this);
                }

                /**
                 * Get fields that must be omitted from the SQL generation
                 */
                if ($exists) {
                    $automaticAttributes = $metaData->getAutomaticUpdateAttributes($this);
                } else {
                    $automaticAttributes = $metaData->getAutomaticCreateAttributes($this);
                }

                $defaultValues = $metaData->getDefaultValues($this);

                /**
                 * Get string attributes that allow empty strings as defaults
                 */
                $emptyStringValues = $metaData->getEmptyStringAttributes($this);
                $error             = false;

                foreach ($notNull as $field) {
                    if (is_array($columnMap)) {
                        if (!isset($columnMap[$field])) {
                            if (!Settings::get("orm.ignore_unknown_columns")) {
                                throw new Exception(
                                    "Column '"
                                    . $field . "' in '"
                                    . get_class($this) . "' isn't part of the column map"
                                );
                            }
                        }

                        $attributeField = $columnMap[$field];
                    } else {
                        $attributeField = $field;
                    }

                    /**
                     * We don't check fields that must be omitted
                     */
                    if (!isset($automaticAttributes[$attributeField])) {
                        $isNull = false;

                        /**
                         * Field is null when: 1) is not set, 2) is numeric but
                         * its value is not numeric, 3) is null or 4) is empty string
                         * Read the attribute from the this_ptr using the real or renamed name
                         */
                        if (isset($this->$attributeField)) {
                            $value = $this->$attributeField;
                            /**
                             * Objects are never treated as null, numeric fields
                             * must be numeric to be accepted as not null
                             */
                            if (!is_object($value)) {
                                if (!isset($dataTypeNumeric[$field])) {
                                    if (isset($emptyStringValues[$field])) {
                                        if ($value === null) {
                                            $isNull = true;
                                        }
                                    } else {
                                        if (
                                            $value === null ||
                                            (
                                                $value === "" &&
                                                (
                                                    !isset($defaultValues[$field]) ||
                                                    $value !== $defaultValues[$field]
                                                )
                                            )
                                        ) {
                                            $isNull = true;
                                        }
                                    }
                                } else {
                                    if (!is_numeric($value)) {
                                        $isNull = true;
                                    }
                                }
                            }
                        } else {
                            $isNull = true;
                        }

                        if ($isNull) {
                            if (!$exists) {
                                /**
                                 * The identity field can be null
                                 */
                                if ($field == $identityField) {
                                    continue;
                                }

                                /**
                                 * The field have default value can be null
                                 */
                                if (isset($defaultValues[$field])) {
                                    continue;
                                }
                            }

                            /**
                             * An implicit PresenceOf message is created
                             */
                            $this->errorMessages[] = new Message(
                                $attributeField . " is required",
                                $attributeField,
                                "PresenceOf",
                                0,
                                [
                                    "model" => get_class($this),
                                ]
                            );

                            $error = true;
                        }
                    }
                }

                if ($error) {
                    if (Settings::get("orm.events")) {
                        $this->fireEvent("onValidationFails");
                        $this->cancelOperation();
                    }

                    return false;
                }
            }
        }

        /**
         * Call the main validation event
         */
        if ($this->fireEventCancel("validation") === false) {
            if (Settings::get("orm.events")) {
                $this->fireEvent("onValidationFails");
            }

            return false;
        }

        /**
         * Run Validation
         */
        if (Settings::get("orm.events")) {
            /**
             * Run Validation Callbacks After
             */
            if ($exists) {
                $eventName = "afterValidationOnUpdate";
            } else {
                $eventName = "afterValidationOnCreate";
            }

            if ($this->fireEventCancel($eventName) === false) {
                return false;
            }

            if ($this->fireEventCancel("afterValidation") === false) {
                return false;
            }

            /**
             * Run Before Callbacks
             */
            if ($this->fireEventCancel("beforeSave") === false) {
                return false;
            }

            $this->skipped = false;

            /**
             * The operation can be skipped here
             */
            if ($exists) {
                $eventName = "beforeUpdate";
            } else {
                $eventName = "beforeCreate";
            }

            if ($this->fireEventCancel($eventName) === false) {
                return false;
            }

            /**
             * Always return true if the operation is skipped
             */
            if ($this->skipped === true) {
                return true;
            }
        }

        return true;
    }

    /**
     * Saves related records that must be stored prior to save the master record
     *
     * @param AdapterInterface    $connection
     * @param array               $related
     * @param CollectionInterface $visited
     *
     * @return bool
     * @throws Exception
     */
    protected function preSaveRelatedRecords(
        AdapterInterface $connection,
        array $related,
        CollectionInterface $visited
    ): bool {
        $nesting = false;

        /**
         * Start an implicit transaction
         */
        $connection->begin($nesting);

        $className = get_class($this);
        $manager   = $this->getModelsManager();

        foreach ($related as $name => $record) {
            /**
             * Try to get a relation with the same name
             */
            $relation = $manager->getRelationByAlias(
                $className,
                $name
            );

            if (is_object($relation)) {
                /**
                 * Get the relation type
                 */
                $type = $relation->getType();

                /**
                 * Only belongsTo are stored before save the master record
                 */
                if ($type == Relation::BELONGS_TO) {
                    if (!is_object($record)) {
                        $connection->rollback($nesting);

                        throw new BelongsToRequiresObject(get_class($this), $name);
                    }

                    /**
                     * If dynamic update is enabled, saving the record must not
                     * take any action. Recursion through circular relations is
                     * prevented by the visited collection inside doSave().
                     */
                    if (!$record->doSave($visited)) {
                        /**
                         * Get the validation messages generated by the
                         * referenced model
                         */
                        $this->appendMessagesFrom($record);

                        /**
                         * Rollback the implicit transaction
                         */
                        $connection->rollback($nesting);

                        return false;
                    }

                    /**
                     * Read the attribute from the referenced model and assign
                     * it to the current model
                     */
                    $columns          = $relation->getFields();
                    $referencedFields = $relation->getReferencedFields();

                    if (is_array($columns)) {
                        $columnCount = count($columns) - 1;

                        for ($i = 0; $i <= $columnCount; $i++) {
                            $columnA = $columns[$i];
                            $columnB = $referencedFields[$i];

                            $this->$columnA = $record->readAttribute($columnB);
                        }
                    } else {
                        $this->$columns = $record->readAttribute($referencedFields);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Sets schema name where the mapped table is located
     *
     * @param string $schema
     *
     * @return ModelInterface
     */
    final protected function setSchema(string $schema): ModelInterface
    {
        $this->modelsManager->setModelSchema(
            $this,
            $schema
        );

        return $this;
    }

    /**
     * Sets the table name to which model should be mapped
     *
     * @param string $source
     *
     * @return ModelInterface
     */
    final protected function setSource(string $source): ModelInterface
    {
        $this->modelsManager->setModelSource($this, $source);

        return $this;
    }

    /**
     * Sets a list of attributes that must be skipped from the
     * generated INSERT/UPDATE statement
     *
     *```php
     * class Robots extends \Phalcon\Mvc\Model
     * {
     *     public function initialize()
     *     {
     *         $this->skipAttributes(
     *             [
     *                 "price",
     *             ]
     *         );
     *     }
     * }
     *```
     *
     * @param array $attributes
     *
     * @return void
     */
    protected function skipAttributes(array $attributes): void
    {
        $this->skipAttributesOnCreate($attributes);
        $this->skipAttributesOnUpdate($attributes);
    }

    /**
     * Sets a list of attributes that must be skipped from the
     * generated INSERT statement
     *
     *```php
     * class Robots extends \Phalcon\Mvc\Model
     * {
     *     public function initialize()
     *     {
     *         $this->skipAttributesOnCreate(
     *             [
     *                 "created_at",
     *             ]
     *         );
     *     }
     * }
     *```
     *
     * @param array $attributes
     *
     * @return void
     * @throws Exception
     */
    protected function skipAttributesOnCreate(array $attributes): void
    {
        $keysAttributes = [];

        foreach ($attributes as $attribute) {
            $keysAttributes[$attribute] = null;
        }

        $this->getModelsMetaData()->setAutomaticCreateAttributes(
            $this,
            $keysAttributes
        );
    }

    /**
     * Sets a list of attributes that must be skipped from the
     * generated UPDATE statement
     *
     *```php
     * class Robots extends \Phalcon\Mvc\Model
     * {
     *     public function initialize()
     *     {
     *         $this->skipAttributesOnUpdate(
     *             [
     *                 "modified_in",
     *             ]
     *         );
     *     }
     * }
     *```
     *
     * @param array $attributes
     *
     * @return void
     * @throws Exception
     */
    protected function skipAttributesOnUpdate(array $attributes): void
    {
        $keysAttributes = [];

        foreach ($attributes as $attribute) {
            $keysAttributes[$attribute] = null;
        }

        $this->getModelsMetaData()->setAutomaticUpdateAttributes(
            $this,
            $keysAttributes
        );
    }

    /**
     * Sets if a model must use dynamic update instead of the all-field update
     *
     *```php
     * use Phalcon\Mvc\Model;
     *
     * class Robots extends Model
     * {
     *     public function initialize()
     *     {
     *         $this->useDynamicUpdate(true);
     *     }
     * }
     *```
     *
     * @param bool $dynamicUpdate
     *
     * @return void
     */
    protected function useDynamicUpdate(bool $dynamicUpdate): void
    {
        $this->modelsManager->useDynamicUpdate(
            $this,
            $dynamicUpdate
        );
    }

    /**
     * Executes validators on every validation call
     *
     *```php
     * use Phalcon\Mvc\Model;
     * use Phalcon\Filter\Validation;
     * use Phalcon\Filter\Validation\Validator\ExclusionIn;
     *
     * class Subscriptors extends Model
     * {
     *     public function validation()
     *     {
     *         $validator = new Validation();
     *
     *         $validator->add(
     *             "status",
     *             new ExclusionIn(
     *                 [
     *                     "domain" => [
     *                         "A",
     *                         "I",
     *                     ],
     *                 ]
     *             )
     *         );
     *
     *         return $this->validate($validator);
     *     }
     * }
     *```
     *
     * @param ValidationInterface $validator
     *
     * @return bool
     */
    protected function validate(ValidationInterface $validator): bool
    {
        $messages = $validator->validate(null, $this);

        // Call the validation, if it returns not the bool
        // we append the messages to the current object
        if (is_bool($messages)) {
            return $messages;
        }

        foreach ($messages as $message) {
            $this->appendMessage(
                new Message(
                    $message->getMessage(),
                    $message->getField(),
                    $message->getType(),
                    $message->getCode(),
                    $message->getMetaData()
                )
            );
        }

        // If there is a message, it returns false otherwise true
        return !count($messages);
    }

    /**
     * Assigns a value to a model property replicating the coercive property
     * write performed by the C extension. PDO adapters (notably PostgreSQL)
     * return numeric columns as strings; cphalcon relies on the extension to
     * coerce them to the property's declared scalar type on assignment. Under
     * strict_types a direct write throws a TypeError, so when that happens we
     * coerce the scalar value to the declared type and retry. Non-coercible
     * cases are re-thrown unchanged.
     *
     * @param object $instance
     * @param string $property
     * @param mixed  $value
     *
     * @return void
     */
    private static function assignCoercedValue(
        object $instance,
        string $property,
        mixed $value
    ): void {
        try {
            $instance->$property = $value;
        } catch (\TypeError $ex) {
            if (!is_scalar($value) || !property_exists($instance, $property)) {
                throw $ex;
            }

            $propertyType = (new \ReflectionProperty($instance, $property))
                ->getType();

            if (!$propertyType instanceof \ReflectionNamedType) {
                throw $ex;
            }

            $instance->$property = match ($propertyType->getName()) {
                "bool"   => (bool) $value,
                "float"  => (float) $value,
                "int"    => (int) $value,
                "string" => (string) $value,
                default  => throw $ex,
            };
        }
    }

    /**
     * Attempts to find key case-insensitively
     *
     * @param array  $columnMap
     * @param string $key
     *
     * @return string
     */
    private static function caseInsensitiveColumnMap(
        array $columnMap,
        string $key
    ): string {
        $keys = array_keys($columnMap);
        foreach ($keys as $cmKey) {
            if (strtolower($cmKey) == strtolower($key)) {
                return $cmKey;
            }
        }

        return $key;
    }

    /**
     * shared prepare query logic for find and findFirst method
     *
     * @param array|string|null $params
     * @param mixed|null        $limit
     *
     * @return QueryInterface
     */
    private static function getPreparedQuery(
        array | string | null $params,
        mixed $limit = null
    ): QueryInterface {
        $container = Di::getDefault();
        $manager   = $container->get("modelsManager");

        /**
         * Builds a query with the passed parameters
         */
        $builder = $manager->createBuilder($params);

        $builder->from(get_called_class());

        if ($limit != null) {
            $builder->limit($limit);
        }

        $query = $builder->getQuery();

        /**
         * Check for bind parameters
         */
        if (isset($params["bind"])) {
            $bindParams = $params["bind"];
            if (is_array($bindParams)) {
                $query->setBindParams($bindParams, true);
            }

            if (isset($params["bindTypes"])) {
                $bindTypes = $params["bindTypes"];
                if (is_array($bindTypes)) {
                    $query->setBindTypes($bindTypes, true);
                }
            }
        }

        if (isset($params[self::TRANSACTION_INDEX])) {
            $transaction = $params[self::TRANSACTION_INDEX];
            if ($transaction instanceof TransactionInterface) {
                $query->setTransaction($transaction);
            }
        }

        /**
         * Pass the cache options to the query
         */
        if (isset($params["cache"])) {
            $cache = $params["cache"];
            $query->cache($cache);
        }

        return $query;
    }
}
