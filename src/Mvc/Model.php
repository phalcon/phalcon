<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Phalcon\Mvc;

use JsonSerializable;
use Phalcon\Db\Adapter\AdapterInterface;
use Phalcon\Db\Column;
use Phalcon\Db\DialectInterface;
use Phalcon\Db\Enum;
use Phalcon\Db\RawValue;
use Phalcon\Di\AbstractInjectionAware;
use Phalcon\Di\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Events\ManagerInterface as EventsManagerInterface;
use Phalcon\Helper\Arr;
use Phalcon\Messages\Message;
use Phalcon\Messages\MessageInterface;
use Phalcon\Mvc\Model\BehaviorInterface;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\Model\CriteriaInterface;
use Phalcon\Mvc\Model\Exception;
use Phalcon\Mvc\Model\ManagerInterface;
use Phalcon\Mvc\Model\MetaDataInterface;
use Phalcon\Mvc\Model\Query;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Mvc\Model\Query\BuilderInterface;
use Phalcon\Mvc\Model\QueryInterface;
use Phalcon\Mvc\Model\ResultInterface;
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model\ResultsetInterface;
use Phalcon\Mvc\Model\Relation;
use Phalcon\Mvc\Model\RelationInterface;
use Phalcon\Mvc\Model\TransactionInterface;
use Phalcon\Mvc\Model\ValidationFailed;
use Phalcon\Validation\ValidationInterface;
use Serializable;

/**
 * Phalcon\Mvc\Model
 *
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
 * if (($robot->save() === false)) {
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
 */
abstract class Model extends AbstractInjectionAware implements EntityInterface, ModelInterface, ResultInterface, Serializable, JsonSerializable
{
    const DIRTY_STATE_DETACHED   = 2;
    const DIRTY_STATE_PERSISTENT = 0;
    const DIRTY_STATE_TRANSIENT  = 1;
    const OP_CREATE = 1;
    const OP_DELETE = 3;
    const OP_NONE   = 0;
    const OP_UPDATE = 2;
    const TRANSACTION_INDEX = "transaction";

    protected int $dirtyState = 1;

    /**
     * @var array
     */
    protected array $dirtyRelated = [];

    /**
     * @var array
     */
    protected array $errorMessages = [];

    protected ?ManagerInterface $modelsManager;

    protected $modelsMetaData;

    /**
     * @var array
     */
    protected array $related = [];

    protected $operationMade = 0;

    /**
     * @var array
     */
    protected array $oldSnapshot = [];

    protected bool $skipped = false;

    protected $snapshot;

    protected ?TransactionInterface $transaction;

    protected $uniqueKey;

    protected $uniqueParams;

    protected $uniqueTypes;

    /**
     * Phalcon\Mvc\Model constructor
     */
    final public function __construct(
        $data = null,
        DiInterface $container = null,
        ManagerInterface $modelsManager = null
    ) {
        /**
         * We use a default DI if the user doesn't define one
         */
        if (!is_object($container)) {
            $container = Di::getDefault();
        }

        if (!is_object($container)) {
            throw new Exception(
                Exception::containerServiceNotFound(
                    "the services related to the ODM"
                )
            );
        }

        $this->container = $container;

        /**
         * Inject the manager service from the DI
         */
        if (!is_object($modelsManager)) {
            $modelsManager =  $container->getShared("modelsManager");

            if (!is_object($modelsManager)) {
                throw new Exception(
                    "The injected service 'modelsManager' is not valid"
                );
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
            $this->{"onConstruct"}($data);
        }

        if (is_array($data)) {
            $this->assign($data);
        }
    }

    public function getTransaction()
    {
        return $this->transaction;
    }
    /**
     * Handles method calls when a method is not implemented
     *
     * @return mixed
     * @throws \Phalcon\Mvc\Model\Exception If the method doesn't exist
     */
    public function __call(string $method, array $arguments)
    {
        

        $records = self::_invokeFinder($method, $arguments);

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
        $status =  $this->modelsManager->missingMethod($this, $method, $arguments);

        if ($status !== null) {
            return $status;
        }

        /**
         * The method doesn't exist throw an exception
         */
        throw new Exception(
            "The method '" . $method . "' doesn't exist on model '" . $modelName . "'"
        );
    }

    /**
     * Handles method calls when a static method is not implemented
     *
     * @return mixed
     * @throws \Phalcon\Mvc\Model\Exception If the method doesn't exist
     */
    public static function __callStatic(string $method, array $arguments)
    {
        

        $records = self::_invokeFinder($method, $arguments);

        if ($records !== false) {
            return $records;
        }

        $modelName = get_called_class();

        /**
         * The method doesn't exist throw an exception
         */
        throw new Exception(
            "The method '" . $method . "' doesn't exist on model '" . $modelName . "'"
        );
    }


    /**
     * Magic method to get related records using the relation alias as a
     * property
     *
     * @return mixed
     */
    public function __get(string $property)
    {
            $modelName     = get_class($this);
            $manager       = $this->getModelsManager();
            $lowerProperty = strtolower($property);

        /**
         * Check if the property is a relationship
         */
        $relation =  $manager->getRelationByAlias(
            $modelName,
            $lowerProperty
        );

        if (is_object($relation)) {
            /**
             * There might be unsaved related records that can be returned
             */
            if (isset ($this->dirtyRelated[$lowerProperty]) ) {
                return $this->dirtyRelated[$lowerProperty];
            }

            /**
             * Get the related records
             */
            return $this->getRelated($lowerProperty);
        }

        /**
         * Check if the property has getters
         */
        $method = "get" . camelize($property);

        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        /**
         * A notice is shown if the property is not defined and it isn't a
         * relationship
         */
        trigger_error(
            "Access to undefined property " . $modelName . "::" . $property
        );

        return null;
    }

    /**
     * Magic method to check if a property is a valid relation
     */
    public function __isset(string $property) : bool
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
            $method = "get" . camelize($property);

            $result = method_exists($this, $method);
        }

        return $result;
    }

    /**
     * Magic method to assign values to the the model
     *
     * @param mixed value
     */
    public function __set(string $property, $value)
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

                if (($value->getDirtyState() !== $dirtyState)) {
                    $dirtyState = self::DIRTY_STATE_TRANSIENT;
                }

                unset ($this->related[$lowerProperty]);

                $this->dirtyRelated[$lowerProperty] = $value;
                $this->dirtyState                  = $dirtyState;

                return $value;
            }
        }

        /**
         * Check if the value is an array
         */
        elseif (is_array($value)) {
            $lowerProperty = strtolower($property);
                $modelName = get_class($this);
                $manager   = $this->getModelsManager();
                $relation  =  $manager->getRelationByAlias(
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
                            $this->dirtyState = self::DIRTY_STATE_TRANSIENT;

                            return $value;
                        }

                        break;

                    case Relation::HAS_MANY:
                    case Relation::HAS_MANY_THROUGH:
                        $related = [];

                        foreach($value as $item){
                            if (is_object($item)) {
                                if ($item instanceof ModelInterface) {
                                    $related[] = $item;
                                }
                            }
                        }

                        unset($this->related[$lowerProperty]);

                        if (count($related) > 0) {
                            $this->dirtyRelated[$lowerProperty] = $related;
                            $this->dirtyState = self::DIRTY_STATE_TRANSIENT;
                        } else {
                            unset($this->dirtyRelated[$lowerProperty] );
                        }

                        return $value;
                }
            }
        }

        // Use possible setter.
        if ($this->_possibleSetter($property, $value)) {
            return $value;
        }

        /**
         * Throw an exception if there is an attempt to set a non-public
         * property.
         */
        if (property_exists($this, $property)) {
            $manager = $this->getModelsManager();

            if (!$manager->isVisibleModelProperty($this, $property)) {
                throw new Exception(
                    "Cannot access property '" . $property . "' (not public)."
                );
            }
        }

        $this->{$property} = $value;

        return $value;
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
     */
    public function addBehavior(BehaviorInterface $behavior) : void
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
     *         if (($this->name === "Peter")) {
     *             $message = new Message(
     *                 "Sorry, but a robot cannot be named Peter"
     *             );
     *
     *             $this->appendMessage($message);
     *         }
     *     }
     * }
     * ```
     */
    public function appendMessage(MessageInterface $message): ModelInterface
    {
        $this->errorMessages[] = $message;

        return $this;
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
     * // By default assign method will use setters if exist, you can disable it by using ini_set to directly use properties
     *
     * ini_set("phalcon.orm.disable_assign_setters", true);
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
     * @param array $dataColumnMap array $to transform keys of data to another
     * @param array $whiteList
     */
    public function assign(array $data, $whiteList = null, $dataColumnMap = null): ModelInterface
    {
        $disableAssignSetters = \globals_get("orm.disable_assign_setters");

        // apply column map for data, if exist
        if (is_array($dataColumnMap)) {
            $dataMapped = [];

            foreach($data as $key => $value) {
                $keyMapped = $dataColumnMap[$key] ?? null;
		          if ($keyMapped !== null) {
                    $dataMapped[$keyMapped] = $value;
                }
            }
        } else {
            $dataMapped = $data;
        }

        if (count($dataMapped) == 0) {
            return $this;
        }

        $metaData = $this->getModelsMetaData();

        if (\globals_get("orm.column_renaming")) {
            $columnMap = $metaData->getColumnMap($this);
        } else {
            $columnMap = null;
        }

        foreach($metaData->getAttributes($this) as $attribute) {
            // Try to find case-insensitive key variant
            if (!isset ($columnMap[$attribute]) && \globals_get("orm.case_insensitive_column_map")) {
                    $attribute = self::caseInsensitiveColumnMap(
                    $columnMap,
                    $attribute
                );
            }

            // Check if we need to rename the field
            if (is_array($columnMap)) {
                $attributeField = $columnMap[$attribute] ?? null;
		if ($attributeField === null) {
                    if (!\globals_get("orm.ignore_unknown_columns")) {
                        throw new Exception(
                            "Column '" . $attribute. "' doesn't make part of the column map"
                        );
                    }

                    continue;
                }
            } else {
                $attributeField = $attribute;
            }

            // The value in the array $passed
            // Check if we there is data for the field
            $value = $dataMapped[$attributeField] ?? null;
		if ($value !== null) {
                // If white-list exists check if the attribute is on that list
                if (is_array($whiteList)) {
                    if (!in_array($attributeField, $whiteList)) {
                        continue;
                    }
                }

                // Try to find a possible getter
                if ($disableAssignSetters || !$this->_possibleSetter($attributeField, $value)) {
                    $this->{$attributeField} = $value;
                }
            }
        }

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
     * @return double | ResultsetInterface
     */
    public static function average($parameters = null): float | ResultsetInterface
    {
        return self::groupResult("AVG", "average", $parameters);
    }

    /**
     * Assigns values to a model from an array $returning a new model
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
     */
    public static function cloneResult(ModelInterface $base, array $data, int $dirtyState = 0): ModelInterface
    {
        

        /**
         * Clone the base record
         */
        $instance = clone $base;

        /**
         * Mark the object as persistent
         */
        $instance->setDirtyState($dirtyState);

        foreach($data as $key => $value) {
            if (!is_string($key)) {
                throw new Exception(
                    "Invalid key in array $data provided to dumpResult()"
                );
            }

            $instance->{$key} = $value;
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
     * @param \Phalcon\Mvc\ModelInterface|\Phalcon\Mvc\Model\Row base
     * @param array $columnMap
     */
    public static function cloneResultMap($base, array $data, $columnMap, int $dirtyState = 0, bool $keepSnapshots = null): ModelInterface
    {
        

        $instance = clone($base);

        // Change the dirty state to persistent
        $instance->setDirtyState($dirtyState);

        /**
         * Assign the data in the model
         */
        foreach($data as $key => $value) {
            // Only string $keys in the data are valid
            if (!is_string($key)) {
                continue;
            }

            if (!is_array($columnMap)) {
                $instance->{$key} = $value;

                continue;
            }

            // Every field must be part of the column map
            $attribute = $columnMap[$key] ?? null;
		    if ($attribute === null) {
                if (is_array($columnMap) && !empty($columnMap)) {
                    $metaData = $instance->getModelsMetaData();

                    $reverseMap = $metaData->getReverseColumnMap($instance);
                    $attribute = $reverseMap[$key] ?? null;
		  if ($attribute === null) {
                        if (!\globals_get("orm.ignore_unknown_columns")) {
                            throw new Exception(
                                "Column '" . $key . "' doesn't make part of the column map"
                            );
                        }

                        continue;
                    }
                } else {
                    if (!\globals_get("orm.ignore_unknown_columns")) {
                        throw new Exception(
                            "Column '" . $key . "' doesn't make part of the column map"
                        );
                    }

                    continue;
                }
            }

            if (!is_array($attribute)) {
                $instance->{$attribute} = $value;

                continue;
            }

            if (!empty($value)) {
                switch ($attribute[1]) {
                    case Column::TYPE_BIGINTEGER:
                    case Column::TYPE_INTEGER:
                    case Column::TYPE_MEDIUMINTEGER:
                    case Column::TYPE_SMALLINTEGER:
                    case Column::TYPE_TINYINTEGER:
                        $castValue = intval($value, 10);
                        break;

                    case Column::TYPE_DECIMAL:
                    case Column::TYPE_DOUBLE:
                    case Column::TYPE_FLOAT:
                        $castValue = doubleval($value);
                        break;

                    case Column::TYPE_BOOLEAN:
                        $castValue = (bool) $value;
                        break;

                    default:
                        $castValue = $value;
                        break;
                }
            } else {
                switch ($attribute[1]) {
                    case Column::TYPE_BIGINTEGER:
                    case Column::TYPE_BOOLEAN:
                    case Column::TYPE_DECIMAL:
                    case Column::TYPE_DOUBLE:
                    case Column::TYPE_FLOAT:
                    case Column::TYPE_INTEGER:
                    case Column::TYPE_MEDIUMINTEGER:
                    case Column::TYPE_SMALLINTEGER:
                    case Column::TYPE_TINYINTEGER:
                        $castValue = null;
                        break;

                    default:
                        $castValue = $value;
                        break;
                }
            }

            $attributeName = $attribute[0];
                $instance->{$attributeName} = $castValue;
                $data[$key] = $castValue;
        }

        /**
         * Models that keep snapshots store the original data in t
         */
        if ($keepSnapshots) {
            $instance->setSnapshotData($data, $columnMap);
            $instance->setOldSnapshotData($data, $columnMap);
        }

        /**
         * Call afterFetch, this allows the developer to execute actions after a
         * record is fetched from the database
         */
        if (method_exists($instance, "fireEvent")) {
            $instance->{"fireEvent"}("afterFetch");
        }

        return $instance;
    }

    /**
     * Returns an hydrated result based on the data and the column map
     *
     * @param array $columnMap
     * @return mixed
     */
    public static function cloneResultMapHydrate(array $data, $columnMap, int $hydrationMode)
    {
        /**
         * If there is no column map and the hydration mode is arrays return the
         * data as it is
         */
        if (!is_array($columnMap)) {
            if ($hydrationMode === Resultset::HYDRATE_ARRAYS) {
                return $data;
            }
        }

        /**
         * Create the destination object
         */
        $hydrateArray = [];

        foreach($data as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            if (is_array($columnMap)) {
                // Try to find case-insensitive key variant
                if (!isset($columnMap[$key]) && \globals_get("orm.case_insensitive_column_map")) {
                    $key = self::caseInsensitiveColumnMap($columnMap, $key);
                }

                /**
                 * Every field must be part of the column map
                 */
                $attribute = $columnMap[$key] ?? null;
		if ($attribute === null) {
                    if (!\globals_get("orm.ignore_unknown_columns")) {
                        throw new Exception(
                            "Column '" . $key . "' doesn't make part of the column map"
                        );
                    }

                    continue;
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

        if ($hydrationMode !== Resultset::HYDRATE_ARRAYS) {
            return Arr::toObject($hydrateArray);
        }

        return $hydrateArray;
    }

    /**
     * Collects previously queried (belongs-to, has-one and has-one-through)
     * related records along with freshly added one
     *
     * @return array $Related records that should be saved
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

        foreach($related as $name => $record) {
            if (isset($dirtyRelated[$name])) {
                continue;
            }

            if (!is_object($record) || !($record instanceof ModelInterface)) {
                continue;
            }

            $dirtyRelated[$name] = $record;
        }

        return $dirtyRelated;
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
     * @param array $parameters
     */
    public static function count($parameters = null): int | ResultsetInterface
    {
        

        $result = self::groupResult("COUNT", "rowcount", $parameters);

        if (is_string($result)) {
            return (int) $result;
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
     * // Passing an array $to create
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
     */
    public function create() : bool
    {
        

        $metaData = $this->getModelsMetaData();

        /**
         * Get the current connection use write to prevent replica lag
         * If the record already exists we must throw an exception
         */
        if ($this->exists($metaData, $this->getWriteConnection())) {
            $this->errorMessages = [
                new Message(
                    "Record cannot be created because it already exists",
                    null,
                    "InvalidCreateAttempt"
                )
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
     */
    public function delete() : bool
    {
        $metaData = $this->getModelsMetaData();
        $writeConnection = $this->getWriteConnection();

        /**
         * Operation made is OP_DELETE
         */
        $this->operationMade = self::OP_DELETE;
        $this->errorMessages = [];

        /**
         * Check if deleting the record violates a virtual foreign key
         */
        if (\globals_get("orm.virtual_foreign_keys")) {
            if ($this->_checkForeignKeysReverseRestrict() === false) {
                return false;
            }
        }

        $values = [];
        $bindTypes = [];
        $conditions = [];

        $primaryKeys = $metaData->getPrimaryKeyAttributes($this);
        $bindDataTypes = $metaData->getBindTypes($this);

        if (\globals_get("orm.column_renaming")) {
            $columnMap = $metaData->getColumnMap($this);
        } else {
            $columnMap = null;
        }

        /**
         * We can't create dynamic SQL without a primary key
         */
        if (!count($primaryKeys)) {
            throw new Exception(
                "A primary key must be defined in the model in order to perform the operation"
            );
        }

        /**
         * Create a condition from the primary keys
         */
        foreach($primaryKeys as $primaryKey){
            /**
             * Every column part of the primary key must be in the bind data
             * types
             */
        $bindType = $bindDataTypes[$primaryKey] ?? null;
		if ($bindType === null) {
                throw new Exception(
                    "Column '" . $primaryKey . "' have not defined a bind data type"
                );
            }

            /**
             * Take the column values based on the column map if any
             */
            if (is_array($columnMap)) {
                $attributeField = $columnMap[$primaryKey] ?? null;
		if ($attributeField === null) {
                    throw new Exception(
                        "Column '" . $primaryKey . "' isn't part of the column map"
                    );
                }
            } else {
                $attributeField = $primaryKey;
            }

            /**
             * If the attribute is currently set in the object add it to the
             * conditions
             */
            $value =  $this->{$attributeField} ?? null;
            if ($value === null) {
                throw new Exception(
                    "Cannot delete the record because the primary key attribute: '" . 
                    $attributeField . "' wasn't set"
                );
            }

            /**
             * Escape the column identifier
             */
            $values[] = $value;
            $conditions[] = $writeConnection->escapeIdentifier($primaryKey) . " = ?";
            $bindTypes[] = $bindType;
        }

        if (\globals_get("orm.events")) {
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
         * Join the conditions in the array $using an AND operator
         * Do the deletion
         */
        $success = $writeConnection->delete(
            $table,
            join(" AND ", $conditions),
            $values,
            $bindTypes
        );

        /**
         * Check if there is virtual foreign keys with cascade action
         */
        if (\globals_get("orm.virtual_foreign_keys")) {
            if ($this->_checkForeignKeysReverseCascade() === false) {
                return false;
            }
        }

        if (\globals_get("orm.events")) {
            if ($success) {
                $this->fireEvent("afterDelete");
            }
        }

        /**
         * Force perform the record existence checking again
         */
        $this->dirtyState = self::DIRTY_STATE_DETACHED;

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
     * // Get and print $virtual robots ordered by name
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
     * $myTransaction = new Transaction(\Phalcon\Di::getDefault());
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
     * $myTransaction1 = new Transaction(\Phalcon\Di::getDefault());
     * $myTransaction1->begin();
     * $myTransaction2 = new Transaction(\Phalcon\Di::getDefault());
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
     * @param array|string|int|null $parameters = [
     *     'conditions' => ''
     *     'columns' => '',
     *     'bind' => [],
     *     'bindTypes => [],
     *     'order' => '',
     *     'limit' => 10,
     *     'offset' => 5,
     *     'group' => 'name, status',
     *     'for_updated' => false,
     *     'shared_lock' => false,
     *     'cache' => [
     *         'lifetime' => 3600,
     *         'key' => 'my-find-key'
     *     ],
     *     'hydration' => null
     * ]
     */
    public static function find($parameters = null): ResultsetInterface
    {
        

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
        if (is_object($resultset)) {
            $hydration = $params["hydration"] ?? null;
		if ($hydration !== null) {
                $resultset->setHydrateMode($hydration);
            }
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
     * $myTransaction = new Transaction(\Phalcon\Di::getDefault());
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
     * @param array|string|int|null $parameters = [
     *     'conditions' => ''
     *     'columns' => '',
     *     'bind' => [],
     *     'bindTypes => [],
     *     'order' => '',
     *     'limit' => 10,
     *     'offset' => 5,
     *     'group' => 'name, status',
     *     'for_updated' => false,
     *     'shared_lock' => false,
     *     'cache' => [
     *         'lifetime' => 3600,
     *         'key' => 'my-find-key'
     *     ],
     *     'hydration' => null
     * ]
     */
    public static function findFirst($parameters = null): ModelInterface | null
    {
        

        if (null === $parameters) {
            $params = [];
        } elseif (is_array($parameters)) {
            $params = $parameters;
        } elseif (is_string($parameters) || is_numeric($parameters)) {
            $params = [$parameters];
        } else {
            throw new Exception(
                "Parameters passed must be of type array, string, numeric or null"
            );
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
     */
    public function fireEvent(string $eventName) : bool
    {
        /**
         * Check if there is a method with the same name of the event
         */
        if (method_exists($this, $eventName)) {
            $this->{$eventName}();
        }

        /**
         * Send a notification to the events manager
         */
        return ($this->modelsManager)->notifyEvent(
            $eventName,
            $this
        );
    }

    /**
     * Fires an event, implicitly calls behaviors and listeners in the events
     * manager are notified
     * This method stops if one of the callbacks/listeners returns bool false
     */
    public function fireEventCancel(string $eventName) : bool
    {
        /**
         * Check if there is a method with the same name of the event
         */
        if (method_exists($this, $eventName)) {
            if ($this->{$eventName}() === false) {
                return false;
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
     */
    public function getChangedFields(): array
    {
        $snapshot = $this->snapshot;

        if (!is_array($snapshot)) {
            throw new Exception(
                "The 'keepSnapshots' option must be enabled to track changes"
            );
        }

        /**
         * Return the models meta-data
         */
        $metaData = $this->getModelsMetaData();

        /**
         * The reversed column map is an array $if the model has a column map
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

        foreach($allAttributes as $name => $_) {
            /**
             * If some attribute is not present in the snapshot, we assume the
             * record as changed
             */
            if (!isset($snapshot[$name])) {
                $changed[] = $name;

                continue;
            }

            /**
             * If some attribute is not present in the model, we assume the
             * record as changed
             */
            $value = $this->{$name} ?? null;
            if ( $value === null ) {
                $changed[] = $name;

                continue;
            }

            /**
             * Check if the field has changed
             */
            if ($value !== $snapshot[$name]) {
                $changed[] = $name;

                continue;
            }
        }

        return $changed;
    }

    /**
     * Returns one of the DIRTY_STATE_* constants telling if the record exists
     * in the database or not
     */
    public function getDirtyState(): int
    {
        return $this->dirtyState;
    }

    /**
     * Returns the custom events manager or null if there is no custom events manager
     */
    public function getEventsManager(): EventsManagerInterface | null
    {
        return $this->modelsManager->getCustomEventsManager($this);
    }

    /**
     * Returns array $of validation messages
     *
     *```php
     * $robot = new Robots();
     *
     * $robot->type = "mechanical";
     * $robot->name = "Astro Boy";
     * $robot->year = 1952;
     *
     * if (($robot->save() === false)) {
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
     */
    public function getMessages($filter = null) : array
    {
        
        if (is_string($filter) && !empty($filter)) {
            $filtered = [];

            foreach($this->errorMessages as $message) {
                if ($message->getField() == $filter) {
                    $filtered[] = $message;
                }
            }

            return $filtered;
        }

        return $this->errorMessages;
    }

    /**
     * Returns the models manager related to the entity instance
     */
    public function getModelsManager(): ManagerInterface
    {
        return $this->modelsManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getModelsMetaData(): MetaDataInterface
    {
        

        $metaData = $this->modelsMetaData;

        if (!is_object($metaData)) {
            $container = $this->container;

            /**
             * Obtain the models-metadata service from the DI
             */
            $metaData = (MetaDataInterface) ($container->getShared("modelsMetadata"));

            if (!is_object($metaData)) {
                throw new Exception(
                    "The injected service 'modelsMetadata' is not valid"
                );
            }

            /**
             * Update the models-metadata property
             */
            $this->modelsMetaData = $metaData;
        }

        return $metaData;
    }

    /**
     * Returns the type of the latest operation performed by the ORM
     * Returns one of the OP_* class constants
     */
    public function getOperationMade(): int
    {
        return $this->operationMade;
    }

    /**
     * Returns the internal old snapshot data
     */
    public function getOldSnapshotData(): array
    {
        return $this->oldSnapshot;
    }

    /**
     * Gets the connection used to read data for the model
     */
    final public function getReadConnection(): AdapterInterface
    {
        

        $transaction = $this->transaction;

        if (is_object($transaction)) {
            return $transaction->getConnection();
        }

        return $this->modelsManager->getReadConnection($this);
    }

    /**
     * Returns the DependencyInjection connection service name used to read data
     * related the model
     */
    final public function getReadConnectionService(): string
    {
        return $this->modelsManager->getReadConnectionService($this);
    }

    /**
     * Returns related records based on defined relations
     *
     * @param array $arguments
     * @return \Phalcon\Mvc\Model\Resultset\Simple|Phalcon\Mvc\Model\Resultset\Simple|false
     */
    public function getRelated(string $alias, $arguments = null)
    {
        

        /**
         * Query the relation by alias
         */
        $className = get_class($this);
            $manager = $this->modelsManager;
            $lowerAlias = strtolower($alias);

        $relation = $manager->getRelationByAlias(
            $className,
            $lowerAlias
        );

        if (!is_object($relation)) {
            throw new Exception(
                "There is no defined relations for the model '" . $className . "' using alias '" . $alias . "'"
            );
        }

        /**
         * If there are any arguments, Manager with handle the caching of the records
         */
        if ($arguments === null) {
            /**
             * If the related records are already in cache and the relation is reusable,
             * we return the cached records.
             */
            if ($relation->isReusable() && $this->isRelationshipLoaded($lowerAlias)) {
                $result = $this->related[$lowerAlias];
            } else {
                /**
                 * Call the 'getRelationRecords' in the models manager.
                 */
                $result = $manager->getRelationRecords($relation, $this, $arguments);

                /**
                 * We store relationship objects in the related cache if there were no arguments.
                 */
                $this->related[$lowerAlias] = $result;
            }
        } else {
            /**
             * Individually queried related records are handled by Manager.
             * The Manager also checks and stores reusable records.
             */
            $result = $manager->getRelationRecords($relation, $this, $arguments);
        }

        return $result;
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
     * $robot->robotsParts = [$new RobotsParts()];
     * var_dump($robot->isRelationshipLoaded('robotsParts')); // false
     * ```
     */
    public function isRelationshipLoaded(string $relationshipAlias) : bool
    {
        return isset ($this->related[$strtolower($relationshipAlias)]);
    }

    /**
     * Returns schema name where the mapped table is located
     */
    final public function getSchema(): string
    {
        return  $this->modelsManager->getModelSchema($this);
    }

    /**
     * Returns the internal snapshot data
     */
    public function getSnapshotData(): array
    {
        return $this->snapshot;
    }

    /**
     * Returns the table name mapped in the model
     */
    final public function getSource(): string
    {
        return $this->modelsManager->getModelSource($this);
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
     */
    public function getUpdatedFields(): array
    {

        $snapshot = $this->snapshot;
        $oldSnapshot = $this->oldSnapshot;

        if (!\globals_get("orm.update_snapshot_on_save")) {
            throw new Exception(
                "The 'updateSnapshotOnSave' option must be enabled for this method to work properly"
            );
        }

        if (!is_array($snapshot)) {
            throw new Exception(
                "The 'keepSnapshots' option must be enabled to track changes"
            );
        }

        /**
         * Dirty state must be DIRTY_PERSISTENT to make the checking
         */
        if ($this->dirtyState != self::DIRTY_STATE_PERSISTENT) {
            throw new Exception(
                "Change checking cannot be performed because the object has not been persisted or is deleted"
            );
        }

        $updated = [];

        foreach($snapshot as $name => $value) {
            /**
             * If some attribute is not present in the oldSnapshot, we assume
             * the record as changed
             */
            if (!isset ($oldSnapshot[$name]) || $value !== $oldSnapshot[$name]) {
                $updated[] = $name;
            }
        }

        return $updated;
    }

    /**
     * Gets the connection used to write data to the model
     */
    final public function getWriteConnection(): AdapterInterface
    {
        

        $transaction = $this->transaction;

        if (is_object($transaction)) {
            return $transaction->getConnection();
        }

        return $this->modelsManager->getWriteConnection($this);
    }

    /**
     * Returns the DependencyInjection connection service name used to write
     * data related to the model
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
     * @param string|array $fieldName
     * @param boolean allFields
     */
    public function hasChanged($fieldName = null, bool $allFields = false) : bool
    {
        

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
                return ($intersect === $fieldName);
            }

            return count($intersect) > 0;
        }

        return count($changedFields) > 0;
    }

    /**
     * Checks if the object has internal snapshot data
     */
    public function hasSnapshotData() : bool
    {
        return is_array($this->snapshot);
    }

    /**
     * Check if a specific attribute was updated
     * This only works if the model is keeping data snapshots
     *
     * @param string|array $fieldName
     */
    public function hasUpdated($fieldName = null, bool $allFields = false) : bool
    {
        

        $updatedFields = $this->getUpdatedFields();

        /**
         * If a field was specified we only check it
         */
        if (is_string($fieldName)) {
            return in_array($fieldName, $updatedFields);
        }

        if ((is_array($fieldName))) {
            $intersect = array_intersect($fieldName, $updatedFields);
            if ($allFields) {
                return ($intersect === $fieldName);
            }

            return count($intersect) > 0;
        }

        return count($updatedFields) > 0;
    }

    /**
    * Serializes the object for json_encode
    *
    *```php
    * echo json_encode($robot);
    *```
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
     * @param array $parameters
     * @return mixed
     */
    public static function maximum($parameters = null): mixed
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
     * @param array $parameters
     */
    public static function minimum($parameters = null): mixed
    {
        return self::groupResult("MIN", "minimum", $parameters);
    }

    /**
     * Create a criteria for a specific model
     */
    public static function query(DiInterface $container = null): CriteriaInterface
    {
        

        /**
         * Use the global dependency injector if there is no one defined
         */
        if (!is_object($container)) {
            $container = Di::getDefault();
        }

        /**
         * Gets Criteria instance from DI container
         */
        if ($container instanceof DiInterface) {
            $criteria = (CriteriaInterface) ($container->get(
                "Phalcon\\Mvc\\Model\\Criteria"
            ));
        } else {
            $criteria = new Criteria();

            $criteria->setDI($container);
        }

        $criteria->setModelName(
            get_called_class()
        );

        return $criteria;
    }

    /**
     * Reads an attribute value by its name
     *
     * ```php
     * echo $robot->readAttribute("name");
     * ```
     */
    public function readAttribute(string $attribute): mixed
    {
        if (!isset ($this->{$attribute})) {
            return $null;
        }

        return $this->{$attribute};
    }

    /**
     * Refreshes the model attributes re-querying the record from the database
     */
    public function refresh(): ModelInterface
    {
        if ($this->dirtyState != self::DIRTY_STATE_PERSISTENT) {
            throw new Exception(
                "The record cannot be refreshed because it does not exist or is deleted"
            );
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
            if (!$this->exists($metaData, $readConnection)) {
                throw new Exception(
                    "The record cannot be refreshed because it does not exist or is deleted"
                );
            }

            $uniqueKey = $this->uniqueKey;
        }

        $uniqueParams = $this->uniqueParams;

        if (!is_array($uniqueParams)) {
            throw new Exception(
                "The record cannot be refreshed because it does not exist or is deleted"
            );
        }

        /**
         * We only refresh the attributes in the model's metadata
         */
        $fields = [];

        foreach($metaData->getAttributes($this) as $attribute) {
            $fields[] = [$attribute];
        }

        /**
         * We directly build the SELECT to save resources
         */
        $dialect = $readConnection->getDialect();
        $tables = $dialect->select(
            [
                "columns" => $fields,
                "tables" =>  $readConnection->escapeIdentifier($table),
                "where" =>   $uniqueKey
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
         * Assign the resulting array $to the this object
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
     */
    public function save() : bool
    {
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

        if ($hasRelatedToSave) {
            if ($this->preSaveRelatedRecords($writeConnection, $relatedToSave) === false) {
                return false;
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
         * Create/Get the current database connection
         */
        $readConnection = $this->getReadConnection();

        /**
         * We need to check if the record exists
         */
        $exists = $this->exists($metaData, $readConnection);

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

            /**
             * Throw exceptions on failed saves?
             */
            if (\globals_get("orm.exception_on_failed_save")) {
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
        if ($success) {
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
                    $relatedToSave
                );
            }
        }

        /**
         * postSave() invokes after* events if the operation was successful
         */
        if (\globals_get("orm.events")) {
            $success = $this->postSave($success, $exists);
        }

        if ($success === false) {
            $this->cancelOperation();
        } else {
            if ($hasRelatedToSave) {
                /**
                 * Clear unsaved related records storage
                 */
                $this->dirtyRelated = [];
            }

            $this->fireEvent("afterSave");
        }

        return $success;
    }


    /**
     * Serializes the object ignoring connections, services, related objects or
     * static properties
     */
    public function serialize(): string
    {
        /**
         * Use the standard serialize function to serialize the array $data
         */
        

        $attributes = $this->toArray();
        $manager = $this->getModelsManager();

        if ($manager->isKeepingSnapshots($this)) {
            $snapshot = $this->snapshot;

            /**
             * If attributes is not the same as snapshot then save snapshot too
             */
            if ($snapshot != null && $attributes != $snapshot) {
                return serialize(
                    [
                        "_attributes" => $attributes,
                        "snapshot" =>  $snapshot
                    ]
                );
            }
        }

        return serialize($attributes);
    }

    /**
     * Unserializes the object from a serialized string
     */
    public function unserialize($data)
    {
        

        $attributes = unserialize($data);

        if (is_array($attributes)) {
            /**
             * Obtain the default DI
             */
            $container = Di::getDefault();

            if (!is_object($container)) {
                throw new Exception(
                    Exception::containerServiceNotFound(
                        "the services related to the ODM"
                    )
                );
            }

            /**
             * Update the dependency injector
             */
            $this->container = $container;

            /**
             * Gets the default modelsManager service
             */
            $manager = $container->getShared("modelsManager");

            if (!is_object($manager)) {
                throw new Exception(
                    "The injected service 'modelsManager' is not valid"
                );
            }

            /**
             * Update the models manager
             */
            $this->modelsManager = $manager;

            /**
             * Try to initialize the model
             */
            $manager->initialize($this);

            if ($manager->isKeepingSnapshots($this)) {
                $snapshot = $attributes["snapshot"] ?? null;
                if ($snapshot !== null) {
                    $this->snapshot = $snapshot;
                    $attributes = $attributes["_attributes"];
                } else {
                    $this->snapshot = $attributes;
                }
            }

            /**
             * Update the objects attributes
             */
            foreach($attributes as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Sets the DependencyInjection connection service name
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
     */
    public function setDirtyState(int $dirtyState): ModelInterface | bool
    {
        $this->dirtyState = $dirtyState;

        return $this;
    }

    /**
     * Sets a custom events manager
     */
    public function setEventsManager(EventsManagerInterface $eventsManager)
    {
        $this->modelsManager->setCustomEventsManager($this, $eventsManager);
    }

    /**
     * Sets the DependencyInjection connection service name used to read data
     */
    final public function setReadConnectionService(string $connectionService): void
    {
        $this->modelsManager->setReadConnectionService($this, $connectionService);
    }

    /**
     * Sets the record's old snapshot data.
     * This method is used internally to set old snapshot data when the model
     * was set up to keep snapshot data
     *
     * @param array $data
     * @param array $columnMap
     */
    public function setOldSnapshotData(array $data, $columnMap = null)
    {
        /**
         * Build the snapshot based on a column map
         */
        if (is_array($columnMap)) {
            $snapshot = [];

            foreach($data as $key => $value) {
                /**
                 * Use only strings
                 */
                if (!is_string($key)) {
                    continue;
                }

                /**
                 * Every field must be part of the column map
                 */
                $attribute = $columnMap[$key] ?? null;
		if ($attribute === null) {
                    if (!\globals_get("orm.ignore_unknown_columns")) {
                        throw new Exception(
                            "Column '" . $key . "' doesn't make part of the column map"
                        );
                    }

                    continue;
                }

                if (is_array($attribute)) {
                    $attribute = $attribute[0] ?? null;
		if ($attribute === null) {
                        if (!\globals_get("orm.ignore_unknown_columns")) {
                            throw new Exception(
                                "Column '" . $key . "' doesn't make part of the column map"
                            );
                        }

                        continue;
                    }
                }

                $snapshot[$attribute] = $value;
            }
        } else {
            $snapshot = $data;
        }

        $this->oldSnapshot = $snapshot;
    }

    /**
     * Sets the record's snapshot data.
     * This method is used internally to set snapshot data when the model was
     * set up to keep snapshot data
     *
     * @param array $columnMap
     */
    public function setSnapshotData(array $data, $columnMap = null): void
    {
        /**
         * Build the snapshot based on a column map
         */
        if (is_array($columnMap)) {
            $snapshot = [];

            foreach($data as $key => $value) {
                /**
                 * Use only strings
                 */
                if (!is_string($key)) {
                    continue;
                }

                // Try to find case-insensitive key variant
                if (!isset($columnMap[$key]) && \globals_get("orm.case_insensitive_column_map")) {
                    $key = self::caseInsensitiveColumnMap($columnMap, $key);
                }

                /**
                 * Every field must be part of the column map
                 */
                $attribute = $columnMap[$key] ?? null;
		if ($attribute === null) {
                    if (!\globals_get("orm.ignore_unknown_columns")) {
                        throw new Exception(
                            "Column '" . $key . "' doesn't make part of the column map"
                        );
                    }

                    continue;
                }

                if (is_array($attribute)) {
                    $attribute = $attribute[0] ?? null;
		if ($attribute === null) {
                        if (!\globals_get("orm.ignore_unknown_columns")) {
                            throw new Exception(
                                "Column '" . $key . "' doesn't make part of the column map"
                            );
                        }

                        continue;
                    }
                }

                $snapshot[$attribute] = $value;
            }
        } else {
            $snapshot = $data;
        }


        $this->snapshot = $snapshot;
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
     *     if (($robot->save() === false)) {
     *         $transaction->rollback("Can't save robot");
     *     }
     *
     *     $robotPart = new RobotParts();
     *
     *     $robotPart->setTransaction($transaction);
     *
     *     $robotPart->type = "head";
     *
     *     if (($robotPart->save() === false)) {
     *         $transaction->rollback("Robot part cannot be saved");
     *     }
     *
     *     $transaction->commit();
     * } catch (TxFailed $e) {
     *     echo "Failed, reason: ", $e->getMessage();
     * }
     *```
     */
    public function setTransaction(TransactionInterface $transaction): ModelInterface
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * Enables/disables options in the ORM
     */
    public static function setup(array $options): void
    {

        /**
         * Enables/Disables globally the internal events
         */
        $disableEvents = $options["events"] ?? null;
		if ($disableEvents !== null) {
            \globals_set("orm.events", $disableEvents);
        }

        /**
         * Enables/Disables virtual foreign keys
         */
        $virtualForeignKeys = $options["virtualForeignKeys"] ?? null;
        if ($virtualForeignKeys !== null) {
            \globals_set("orm.virtual_foreign_keys", $virtualForeignKeys);
        }

        /**
         * Enables/Disables column renaming
         */
        $columnRenaming = $options["columnRenaming"] ?? null;
        if ($columnRenaming !== null) {
            \globals_set("orm.column_renaming", $columnRenaming);
        }

        /**
         * Enables/Disables automatic not null validation
         */
        $notNullValidations = $options["notNullValidations"] ?? null;
        if ($notNullValidations !== null) {
            \globals_set("orm.not_null_validations", $notNullValidations);
        }

        /**
         * Enables/Disables throws an exception if the saving process fails
         */
        $exceptionOnFailedSave = $options["exceptionOnFailedSave"] ?? null;
		if ($exceptionOnFailedSave !== null) {
            \globals_set("orm.exception_on_failed_save", $exceptionOnFailedSave);
        }

        /**
         * Enables/Disables throws an exception if the saving process fails
         */
        $exceptionOnFailedMetaDataSave = $options["exceptionOnFailedMetaDataSave"] ?? null;
		if ($exceptionOnFailedMetaDataSave !== null) {
            \globals_set("orm.exception_on_failed_metadata_save", $exceptionOnFailedMetaDataSave);
        }

        /**
         * Enables/Disables literals in PHQL this improves the security of
         * applications
         */
        $phqlLiterals = $options["phqlLiterals"] ?? null;
		if ($phqlLiterals !== null) {
            \globals_set("orm.enable_literals", $phqlLiterals);
        }

        /**
         * Enables/Disables late state binding on model hydration
         */
        $lateStateBinding = $options["lateStateBinding"] ?? null;
		if ($lateStateBinding !== null) {
            \globals_set("orm.late_state_binding", $lateStateBinding);
        }

        /**
         * Enables/Disables automatic cast to original types on hydration
         */
        $castOnHydrate = $options["castOnHydrate"] ?? null;
		if ($castOnHydrate !== null) {
            \globals_set("orm.cast_on_hydrate", $castOnHydrate);
        }

        /**
         * Allows to ignore unknown columns when hydrating objects
         */
        $ignoreUnknownColumns = $options["ignoreUnknownColumns"] ?? null;
		if ($ignoreUnknownColumns !== null) {
            \globals_set("orm.ignore_unknown_columns", $ignoreUnknownColumns);
        }

        $caseInsensitiveColumnMap = $options["caseInsensitiveColumnMap"] ?? null;
		if ($caseInsensitiveColumnMap !== null) {
            \globals_set(
                "orm.case_insensitive_column_map",
                $caseInsensitiveColumnMap
            );
        }

        $updateSnapshotOnSave = $options["updateSnapshotOnSave"] ?? null;
		if ($updateSnapshotOnSave !== null) {
            \globals_set("orm.update_snapshot_on_save", $updateSnapshotOnSave);
        }

        $disableAssignSetters = $options["disableAssignSetters"] ?? null;
		if ($disableAssignSetters !== null) {
            \globals_set("orm.disable_assign_setters", $disableAssignSetters);
        }

        $prefetchRecords = $options["prefetchRecords"] ?? null;
		if ($prefetchRecords !== null) {
            \globals_set("orm.resultset_prefetch_records", $prefetchRecords);
        }

        $lastInsertId = $options["castLastInsertIdToInt"] ?? null;
		if ($lastInsertId !== null) {
            \globals_set("orm.cast_last_insert_id_to_int", $lastInsertId);
        }
    }

    /**
     * Sets the DependencyInjection connection service name used to write data
     */
    final public function setWriteConnectionService(string $connectionService): void
    {
         $this->modelsManager->setWriteConnectionService($this, $connectionService);
    }


    /**
     * Skips the current operation forcing a success state
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
     * @param array $parameters
     * @return double | ResultsetInterface
     */
    public static function sum($parameters = null): float | ResultsetInterface
    {
        return self::groupResult("SUM", "sumatory", $parameters);
    }

    /**
     * Returns the instance as an array $representation
     *
     *```php
     * print_r(
     *     $robot->toArray()
     * );
     *```
     *
     * @param array $columns
     */
    public function toArray($columns = null): array
    {
        $data = [];
        $metaData = $this->getModelsMetaData();
        $columnMap = $metaData->getColumnMap($this);

        foreach($metaData->getAttributes($this) as $attribute) {
            /**
             * Check if the columns must be renamed
             */
            if (is_array($columnMap)) {
                // Try to find case-insensitive key variant
                if (!isset($columnMap[$attribute]) && \globals_get("orm.case_insensitive_column_map")) {
                    $attribute = self::caseInsensitiveColumnMap($columnMap, $attribute);
                }

                $attributeField = $columnMap[$attribute] ?? null;
		if ($attributeField === null) {
                    if (!\globals_get("orm.ignore_unknown_columns")) {
                        throw new Exception(
                            "Column '" . $attribute . "' doesn't make part of the column map"
                        );
                    }

                    continue;
                }
            } else {
                $attributeField = $attribute;
            }

            if (is_array($columns)) {
                if (!in_array($attributeField, $columns)) {
                    continue;
                }
            }

            $value = $this->{$attributeField} ?? null;
		if ($value !== null) {
                $data[$attributeField] = $value;
            } else {
                $data[$attributeField] = null;
            }
        }

        return $data;
    }

    /**
     * Updates a model instance. If the instance doesn't exist in the
     * persistence it will throw an exception. Returning true on success or
     * false otherwise.
     *
     *```php
     * // Updating a robot name
     * $robot = Robots::findFirst("id = 100");
     *
     * $robot->name = "Biomass";
     *
     * $robot->update();
     *```
     */
    public function update() : bool
    {
        

        /**
         * We don't check if the record exists if the record is already checked
         */
        if ($this->dirtyState) {
            $metaData = $this->getModelsMetaData();

            if (!$this->exists($metaData, $this->getReadConnection())) {
                $this->errorMessages = [
                    new Message(
                        "Record cannot be updated because it does not exist",
                        null,
                        "InvalidUpdateAttempt"
                    )
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
     * Writes an attribute value by its name
     *
     *```php
     * $robot->writeAttribute("name", "Rosey");
     *```
     */
    public function writeAttribute(string $attribute, $value): void
    {
        $this->{$attribute} = $value;
    }

    /**
     * Reads "belongs to" relations and check the virtual foreign keys when
     * inserting or updating records to verify that inserted/updated values are
     * present in the related entity
     */
    final protected function _checkForeignKeysRestrict() : bool
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

        foreach($belongsTo as $relation){
            $validateWithNulls = false;
            $foreignKey = $relation->getForeignKey();

            if ($foreignKey === false) {
                continue;
            }

            /**
             * Try to find a different action in the foreign key's options
             */
            $action = $foreignKey["action"] ?? Relation::ACTION_RESTRICT;

            /**
             * Check only if the operation is restrict
             */
            if ($action !== Relation::ACTION_RESTRICT) {
                continue;
            }

            /**
             * Load the referenced model if needed
             */
            $referencedModel = $manager->load(
                $relation->getReferencedModel()
            );

            /**
             * Since relations can have multiple columns or a single one, we
             * need to build a condition for each of these cases
             */
            $conditions = [];
            $bindParams = [];

            $numberNull = 0;
            $fields = $relation->getFields();
            $referencedFields = $relation->getReferencedFields();

            if (is_array($fields)) {
                /**
                 * Create a compound condition
                 */
                foreach($fields as $position => $field) {
                    $value = $this->{$field};

                    $conditions[] = "[" . $referencedFields[$position] . "] = ?" . $position;
                    $bindParams[] = $value;

                    if ($value === null) {
                        $numberNull++;
                    }
                }

                $validateWithNulls = ($numberNull === count($fields));
            } else {
                $value = $this->{$fields};

                $conditions[] = "[" . $referencedFields . "] = ?0";
                $bindParams[] = $value;

                if ($value === null) {
                    $validateWithNulls = true;
                }
            }

            /**
             * Check if the virtual foreign key has extra conditions
             */
            $extraConditions = $foreignKey["conditions"] ?? null;
		    if ($extraConditions !== null) {
                $conditions[] = $extraConditions;
            }

            /**
             * Check if the relation definition allows nulls
             */
            if ($validateWithNulls) {
                $allowNulls = $foreignKey["allowNulls"] ?? null;
		      if ($allowNulls !== null) {
                    $validateWithNulls = (bool) $allowNulls;
                } else {
                    $validateWithNulls = false;
                }
            }

            /**
             * We don't trust the actual values in the object and pass the
             * values using bound parameters. Let's check
             */
            if (!$validateWithNulls && !$referencedModel->count([$join(" AND ", $conditions), "bind" => $bindParams])) {
                /**
                 * Get the user message or produce a new one
                 */
                $message = $foreignKey["message"] ?? null;
		      if ($message === null) {
                    if (is_array($fields)) {
                        $message = "Value of fields \"" . join(", ", $fields) . "\" does not exist on referenced table";
                    } else {
                        $message = "Value of field \"" . $fields . "\" does not exist on referenced table";
                    }
                }

                /**
                 * Create a message
                 */
                $this->appendMessage(
                    new Message(
                        $message,
                        $fields,
                        "ConstraintViolation"
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
            if (\globals_get("orm.events")) {
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
     */
    final protected function _checkForeignKeysReverseCascade() : bool
    {
    
        /**
         * Get the models manager
         */
        $manager = $this->modelsManager;

        /**
         * We check if some of the hasOne/hasMany relations is a foreign key
         */
        $relations = $manager->getHasOneAndHasMany($this);

        foreach($relations as $relation){
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
            if (is_array($foreignKey)){
                $action = (int) $foreignKey["action"] ?? Relation::NO_ACTION;
            } else {
                $action = Relation::NO_ACTION;
            }

            /**
             * Check only if the operation is restrict
             */
            if ($action != Relation::ACTION_CASCADE) {
                continue;
            }

            $related = $manager->getRelationRecords($relation, $this);

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
     */
    final protected function _checkForeignKeysReverseRestrict() : bool
    {
        /**
         * Get the models manager
         */
        $manager = $this->modelsManager;

        /**
         * We check if some of the hasOne/hasMany relations is a foreign key
         */
        $relations = $manager->getHasOneAndHasMany($this);

        $error = false;

        foreach($relations as $relation){
            /**
             * Check if the relation has a virtual foreign key
             */
            $foreignKey = $relation->getForeignKey();

            if ($foreignKey === false) {
                continue;
            }

            /**
             * By default action is restrict
             * Try to find a different action in the foreign key's options
             */
            if (is_array($foreignKey)) {
                $action = $foreignKey["action"] ?? Relation::ACTION_RESTRICT;
            } else {
                $action = Relation::ACTION_RESTRICT;
            }

            /**
             * Check only if the operation is restrict
             */
            if ($action !== Relation::ACTION_RESTRICT) {
                continue;
            }

            $relationClass = $relation->getReferencedModel();
            $fields = $relation->getFields();

            if ($manager->getRelationRecords($relation, $this, null, "count")) {
                /**
                 * Create a new message
                 */
            $message = $foreignKey["message"] ?? null;
		    if ($message === null) {
                    $message = "Record is referenced by model " . $relationClass;
                }

                /**
                 * Create a message
                 */
                $this->appendMessage(
                    new Message(
                        $message,
                        $fields,
                        "ConstraintViolation"
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
            if (\globals_get("orm.events")) {
                $this->fireEvent("onValidationFails");
                $this->cancelOperation();
            }

            return false;
        }

        return true;
    }

    /**
     * Sends a pre-build INSERT SQL statement to the relational database system
     *
     * @todo Remove in v5.0
     * @deprecated Use doLowInsert()
     *
     * @param string|array $table
     * @param bool|string $identityField
     */
    protected function _doLowInsert(MetaDataInterface $metaData, AdapterInterface $onnection,
        $table, $identityField) : bool
    {
        return $this->doLowInsert($metaData, $connection, $table, $identityField);
    }

    /**
     * Sends a pre-build INSERT SQL statement to the relational database system
     *
     * @param string|array $table
     * @param bool|string $identityField
     */
    protected function doLowInsert(MetaDataInterface $metaData, AdapterInterface $connection,
        $table, $identityField) : bool
    {
        $bindSkip            = Column::BIND_SKIP;
        $manager             = $this->modelsManager;
        $fields              = [];
        $values              = [];
        $snapshot            = [];
        $bindTypes           = [];
        $unsetDefaultValues  = [];
        $attributes          = $metaData->getAttributes($this);
        $bindDataTypes       = $metaData->getBindTypes($this);
        $automaticAttributes = $metaData->getAutomaticCreateAttributes($this);
        $defaultValues       = $metaData->getDefaultValues($this);

        if (\globals_get("orm.column_renaming")) {
            $columnMap = $metaData->getColumnMap($this);
        } else {
            $columnMap = null;
        }

        /**
         * All fields in the model makes part or the INSERT
         */
        foreach($attributes as $field){
            /**
             * Check if the model has a column map
             */
            if (is_array($columnMap)) {
                $attributeField = $columnMap[$field] ?? null;
		if ($attributeField === null) {
                    throw new Exception(
                        "Column '" . $field . "' isn't part of the column map"
                    );
                }
            } else {
                $attributeField = $field;
            }

            if (!isset($automaticAttributes[$attributeField])) {
                /**
                 * Check every attribute in the model except identity field
                 */
                if ($field !== $identityField) {
                    /**
                     * This isset checks that the property be defined in the
                     * model
                     */
                    if (property_exists($this, $attributeField)) {
                        $value = $this->{$attributeField} ?? null;
                        if ($value === null && isset($defaultValues[$field])) {
                            $defaultval = $defaultValues[$field];
                            $snapshot[$attributeField]           = $defaultval;
                            $unsetDefaultValues[$attributeField] = $defaultval;

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
                        $bindType = $bindDataTypes[$field] ?? null;
		              if ($bindType === null) {
                            throw new Exception(
                                "Column '" . $field . "' have not defined a bind data type"
                            );
                        }

                        $fields[]    = $field;
                        $values[]    = $value;
                        $bindTypes[] = $bindType;
                    } else {
                        if (isset($defaultValues[$field])) {
                            $defaultval = $defaultValues[$field];
                            $snapshot[$attributeField]           = $defaultval;
                            $unsetDefaultValues[$attributeField] = $defaultval;

                            if (false === $connection->supportsDefaultValue()) {
                                continue;
                            }

                            $values[] = $connection->getDefaultValue();
                        } else {
                            $values[]  = $value;
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
            $useExplicitIdentity = (bool) $connection->useExplicitIdValue();

            if ($useExplicitIdentity) {
                $fields[] = $identityField;
            }

            /**
             * Check if the model has a column map
             */
            if (is_array($columnMap)) {
                $attributeField = $columnMap[$identityField] ?? null;
		if ($attributeField === null) {
                    throw new Exception(
                        "Identity column '" . $identityField . "' isn't part of the column map"
                    );
                }
            } else {
                $attributeField = $identityField;
            }

            /**
             * Check if the developer set an explicit value for the column
             */
            if (property_exists($this, $attributeField)) {
                $value = $this->{$attributeField} ?? null;
                if ($value === null || $value === "") {
                    if ($useExplicitIdentity) {
                        $values[] = $defaultValue;
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
                    $bindType = $bindDataTypes[$identityField] ?? null;
		            if ($bindType === null) {
                        throw new Exception(
                            "Identity column '" . $identityField . "' isn\'t part of the table columns"
                        );
                    }

                    $values[]    = $value;
                    $bindTypes[] = $bindType;
                }
            } else {
                if ($useExplicitIdentity) {
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
                    $sequenceName = $this->{"getSequenceName"}();
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
             * Recover the last "insert id" and assign it to the object
             */
            $lastInsertedId = $connection->lastInsertId($sequenceName);

            /**
             * If we want auto casting
             */
            if (\globals_get("orm.cast_last_insert_id_to_int")) {
                $lastInsertedId = intval($lastInsertedId, 10);
            }

            $this->{$attributeField}   = $lastInsertedId;
            $snapshot[$attributeField] = $lastInsertedId;

            /**
             * Since the primary key was modified, we delete the uniqueParams
             * to force any future update to re-build the primary key
             */
            $this->uniqueParams = null;
        }

        if ($success) {
            /**
             * Default values from the database should be
             * written to the model attributes upon successful
             * insert.
             */
            foreach($unsetDefaultValues as $attributeField => $defaultValue) {
                $this->{$attributeField} = $defaultValue;
            }

            if ($manager->isKeepingSnapshots($this) && \globals_get("orm.update_snapshot_on_save")) {
                $this->snapshot = $snapshot;
            }
        }

        return $success;
    }

    /**
     * Sends a pre-build UPDATE SQL statement to the relational database system
     *
     * @todo Remove in v5.0
     * @deprecated Use doLowUpdate()
     *
     * @param string|array $table
     */
     protected function _doLowUpdate(MetaDataInterface $metaData, AdapterInterface $connection, $table) : bool
     {
         return $this->doLowUpdate($metaData, $connection, $table);
     }

    /**
     * Sends a pre-build UPDATE SQL statement to the relational database system
     *
     * @param string|array $table
     */
     protected function doLowUpdate(MetaDataInterface $metaData, AdapterInterface $connection, $table) : bool
     {
        $bindSkip    = Column::BIND_SKIP;
            $fields      = [];
            $values      = [];
            $bindTypes   = [];
            $newSnapshot = [];
            $manager     = $this->modelsManager;

        /**
         * Check if the model must use dynamic update
         */
        $useDynamicUpdate = (bool) $manager->isUsingDynamicUpdate($this);
        $snapshot         = $this->snapshot;

        if ($useDynamicUpdate) {
            if (!is_array($snapshot)) {
                $useDynamicUpdate = false;
            }
        }

        $dataTypes           = $metaData->getDataTypes($this);
        $bindDataTypes       = $metaData->getBindTypes($this);
        $nonPrimary          = $metaData->getNonPrimaryKeyAttributes($this);
        $automaticAttributes = $metaData->getAutomaticUpdateAttributes($this);

        if (\globals_get("orm.column_renaming")) {
            $columnMap = $metaData->getColumnMap($this);
        } else {
            $columnMap = null;
        }

        /**
         * We only make the update based on the non-primary attributes, values
         * in primary key attributes are ignored
         */
        foreach($nonPrimary as $field){
            /**
             * Check if the model has a column map
             */
            if (is_array($columnMap)) {
                $attributeField = $columnMap[$field] ?? null;
		if ($attributeField === null) {
                    if (!\globals_get("orm.ignore_unknown_columns")) {
                        throw new Exception(
                            "Column '" . $field . "' isn't part of the column map"
                        );
                    }
                }
            } else {
                $attributeField = $field;
            }

            if (!isset($automaticAttributes[$attributeField])) {
                /**
                 * Check a bind type for field to update
                 */
                $bindType = $bindDataTypes[$field] ?? null;
		if ($bindType === null) {
                    throw new Exception(
                        "Column '" . $field . "' have not defined a bind data type"
                    );
                }

                /**
                 * Get the field's value
                 * If a field isn't set we pass a null value
                 */
                if (property_exists($this, $attributeField)) {
                    $value = $this->{$attributeField};
                    
                    /**
                     * When dynamic update is not used we pass every field to the update
                     */
                    if (!$useDynamicUpdate) {
                        $fields[] = $field;
                        $values[] = $value;
                        $bindTypes[] = $bindType;
                    } else {
                        /**
                         * If the field is not part of the snapshot we add them as changed
                         */
                        $snapshotValue = $snapshot[$attributeField] ?? null;
		        if ($snapshotValue === null) {
                            $changed = true;
                        } else {
                            /**
                             * See https://github.com/phalcon/cphalcon/issues/3247
                             * Take a TEXT column with value '4' and replace it by
                             * the value '4.0'. For PHP '4' and '4.0' are the same.
                             * We can't use simple comparison...
                             *
                             * We must use the type of snapshotValue.
                             */
                            if ($value === null) {
                                $changed = ($snapshotValue !== null);
                            } else {
                                if ($snapshotValue === null) {
                                    $changed = true;
                                } else {
                                    $dataType = $dataTypes[$field] ?? null;
		                              if ($dataType === null) {
                                        throw new Exception(
                                           "Column '" . $field . "' have not defined a data type"
                                        );
                                    }

                                    switch ($dataType) {

                                        case Column::TYPE_BOOLEAN:
                                            $changed = (bool) $snapshotValue !== (bool) $value;
                                            break;

                                        case Column::TYPE_DECIMAL:
                                        case Column::TYPE_FLOAT:
                                            $changed = floatval($snapshotValue) !== floatval($value);
                                            break;

                                        case Column::TYPE_INTEGER:
                                        case Column::TYPE_DATE:
                                        case Column::TYPE_VARCHAR:
                                        case Column::TYPE_DATETIME:
                                        case Column::TYPE_CHAR:
                                        case Column::TYPE_TEXT:
                                        case Column::TYPE_VARCHAR:
                                        case Column::TYPE_BIGINTEGER:
                                            $changed = (string) $snapshotValue !== (string) $value;
                                            break;

                                        /**
                                         * Any other type is not really supported...
                                         */
                                        default:
                                            $changed = ($value !== $snapshotValue);
                                    }
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
                    }
                    $newSnapshot[$attributeField] = $value;

                } else {
                    $newSnapshot[$attributeField] = null;

                    $fields[]    = $field;
                    $values[]    = null;
                    $bindTypes[] = $bindSkip;
                }
            }
        }

        /**
         * If there is no fields to update we return true
         */
        if (!count($fields)) {
            if ($useDynamicUpdate) {
                $this->oldSnapshot = $snapshot;
            }

            return true;
        }

        $uniqueKey    = $this->uniqueKey;
        $uniqueParams = $this->uniqueParams;
        $uniqueTypes  = $this->uniqueTypes;

        /**
         * When unique params is null we need to rebuild the bind params
         */
        if (!is_array($uniqueParams)) {
            $primaryKeys = $metaData->getPrimaryKeyAttributes($this);

            /**
             * We can't create dynamic SQL without a primary key
             */
            if (!count($primaryKeys)) {
                throw new Exception(
                    "A primary key must be defined in the model in order to perform the operation"
                );
            }

            $uniqueParams = [];

            foreach($primaryKeys as $field){
                /**
                 * Check if the model has a column map
                 */
                if (is_array($columnMap)) {
                    $attributeField = $columnMap[$field] ?? null;
		           if ($attributeField === null) {
                        throw new Exception(
                           "Column '" . $field . "' isn't part of the column map"
                        );
                    }
                } else {
                    $attributeField = $field;
                }

                if (property_exists($this, $attributeField)) {
                    $value = $this->{$attributeField};
                    $newSnapshot[$attributeField] = $value;
                    $uniqueParams[] = $value;
                } else {
                    $newSnapshot[$attributeField] = null;
                    $uniqueParams[] = null;
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
                    "bind" => $uniqueParams,
                    "bindTypes" => $uniqueTypes
                ],
                $bindTypes
            );

        if ($success && $manager->isKeepingSnapshots($this) && \globals_get("orm.update_snapshot_on_save")) {
            if (is_array($snapshot)) {
                $this->oldSnapshot = $snapshot;
                $this->snapshot = array_merge($snapshot, $newSnapshot);
            } else {
                $this->oldSnapshot = [];
                $this->snapshot = $newSnapshot;
            }
        }

        return $success;
    }

    /**
     * Checks whether the current record already exists
     *
     * @todo Remove in v5.0
     * @deprecated Use exists()
     *
     * @return bool
     */
    protected function _exists(MetaDataInterface $metaData, AdapterInterface $connection) : bool
    {
        return $this->exists($metaData, $connection);
    }

    /**
     * Checks whether the current record already exists
     *
     * @return bool
     */
    protected function exists(MetaDataInterface $metaData, AdapterInterface $connection) : bool
    {
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
            if (\globals_get("orm.column_renaming")) {
                $columnMap = $metaData->getColumnMap($this);
            } else {
                $columnMap = null;
            }

            $numberEmpty  = 0;
            $wherePk      = [];
            $uniqueParams = [];
            $uniqueTypes  = [];

            /**
             * We need to create a primary key based on the current data
             */
            foreach($primaryKeys as $field){
                if (is_array($columnMap)) {
                    $attributeField = $columnMap[$field] ?? null;
                    if ($attributeField === null) {
                        throw new Exception(
                            "Column '" . $field . "' isn't part of the column map"
                        );
                    }
                } else {
                    $attributeField = $field;
                }

                /**
                 * If the primary key attribute is set append it to the
                 * conditions
                 */
                $value = null;
                if (property_exists($this, $attributeField)) {
                    $value = $this->{$attributeField};
                    if ($value !== null) {
                    /**
                     * We count how many fields are empty, if all fields are
                     * empty we don't perform an 'exist' check
                     */
                    if ($value === null || $value === "") {
                        $numberEmpty++;
                    }

                    $uniqueParams[] = $value;
                    }
                } else {
                    $uniqueParams[] = null;
                    $numberEmpty++;
                }

                $type = $bindDataTypes[$field] ?? null;
		if ($type === null) {
                    throw new Exception(
                        "Column '" . $field . "' isn't part of the table columns"
                    );
                }

                $uniqueTypes[] = $type;
                $wherePk[]     = $connection->escapeIdentifier($field) . " = ?";
            }

            /**
             * There are no primary key fields defined, assume the record does
             * not exist
             */
            if ($numberPrimary === $numberEmpty) {
                return false;
            }

            $joinWhere = join(" AND ", $wherePk);

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
            "SELECT COUNT(*) \"rowcount\" FROM " . $connection->escapeIdentifier($table) . " WHERE " . $uniqueKey,
            null,
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
     * Returns related records defined relations depending on the method name.
     * Returns false if the relation is non-existent.
     *
     * @todo Remove in v5.0
     * @deprecated Use getRelatedRecords()
     *
     * @param string $modelName
     * @param string $method
     * @param array  arguments
     *
     * @return ResultsetInterface|ModelInterface|bool|null
     */
    protected function _getRelatedRecords(string $modelName, string $method, array $arguments)
    {
        return $this->getRelatedRecords($modelName, $method, $arguments);
    }

    /**
     * Returns related records defined relations depending on the method name.
     * Returns false if the relation is non-existent.
     *
     * @param string $modelName
     * @param string $method
     * @param array  arguments
     *
     * @return ResultsetInterface|ModelInterface|bool|null
     */
    protected function getRelatedRecords(string $modelName, string $method, array $arguments)
    {
        

        $manager = $this->modelsManager;

        $relation = false;
        $queryMethod = null;

        $extraArgs = $arguments[0];

        /**
         * Calling find/findFirst if the method starts with "get"
         */
        if (str_starts_with($method, "get")) {
            $alias = substr($method, 3);
            $relation = $manager->getRelationByAlias($modelName, $alias);

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
         * Calling count if the $method starts with "count"
         */
        if (str_starts_with($method, "count")) {
            $queryMethod = "count";

            $relation = $manager->getRelationByAlias(
                $modelName,
                substr($method, 5)
            );

            /**
             * If the relation was found perform the query via the models manager
             */
            if (!is_object($relation)) {
                return false;
            }

            return $manager->getRelationRecords(
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
     * @todo Remove in v5.0
     * @deprecated Use groupResult()
     *
     * @param array $parameters
     * @return ResultsetInterface
     */
    protected static function _groupResult(string $functionName, string $alias, $parameters): ResultsetInterface
    {
        return static::groupResult($functionName, $alias, $parameters);
    }

    /**
     * Generate a PHQL SELECT statement for an aggregate
     *
     * @param array $parameters
     * @return ResultsetInterface
     */
    protected static function groupResult(string $functionName, string $alias, $parameters): ResultsetInterface
    {
        $container = Di::getDefault();
        $manager = $container->getShared("modelsManager");

        if (!is_array($parameters)) {
            $params = [];

            if ($parameters !== null) {
                $params[] = $parameters;
            }
        } else {
            $params = $parameters;
        }

        $groupColumn = $params["column"] ?? '*';

        /**
         * Builds the columns to query according to the received parameters
         */
        $distinctColumn = $params["distinct"] ?? null;
        if ($distinctColumn !== null) {
            $columns = $functionName . "(DISTINCT " . $distinctColumn . ") AS " . $alias;
        } else {
            $groupColumns = $params["group"] ?? null;
		if ($groupColumns !== null) {
                $columns = $groupColumns . ", " . $functionName . "(" . $groupColumn . ") AS " . $alias;
            } else {
                $columns = $functionName . "(" . $groupColumn . ") AS " . $alias;
            }
        }

        /**
         * Builds a query with the passed parameters
         */
        $builder = $manager->createBuilder($params);

        $builder->columns($columns);

        $builder->from(
            get_called_class()
        );

        $query = $builder->getQuery();

        $transaction = $params[$self::TRANSACTION_INDEX] ?? null;
        if ($transaction !== null) {
            if (transaction instanceof TransactionInterface) {
                $query->setTransaction($transaction);
            }
        }

        /**
         * Check for bind parameters
         */
        $bindParams = null;

        $bindParams = $params["bind"] ?? null;
	if ($bindParams !== null) {
            $bindTypes = $params["bindTypes"] ?? null;
        }
        else {
            $bindTypes = null;
        }
        /**
         * Pass the cache options to the query
         */
        $cache = $params["cache"] ?? null;
        if ($cache !== null) {
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

        return $firstRow->{$alias};
    }

    /**
     * Try to check if the query must invoke a finder
     *
     * @return \Phalcon\Mvc\ModelInterface[]|\Phalcon\Mvc\ModelInterface|bool
     */
    protected final static function _invokeFinder(string $method, array $arguments)
    {

        $extraMethod = null;

        /**
         * Check if the method starts with "findFirst"
         */
        if (str_starts_with($method, "findFirstBy")) {
            $type = "findFirst";
            $extraMethod = substr($method, 11);
        }

        /**
         * Check if the method starts with "find"
         */
        elseif (starts_with($method, "findBy")) {
            $type = "find";
            $extraMethod = substr($method, 6);
        }

        /**
         * Check if the $method starts with "count"
         */
        elseif (str_starts_with($method, "countBy")) {
            $type = "count";
            $extraMethod = substr($method, 7);
        }

        /**
         * The called class is the model
         */
        $modelName = get_called_class();

        if (!$extraMethod) {
            return false;
        }

        if (!isset($arguments[0])) {
            throw new Exception(
                "The static method '" . $method . "' requires one argument"
            );
        }

        $model    = create_instance($modelName);
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
                $field = uncamelize($extraMethod);

                if (!isset($attributes[$field])) {
                    throw new Exception(
                        "Cannot resolve attribute '" . $extraMethod . "' in the model"
                    );
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
                 "bind" => [$value]
            ];

        } else {
            $params = [
                 "conditions" => "[" . $field . "] IS NULL"
            ];
        }

        /**
         * Just in case remove 'conditions' and 'bind'
         */
        unset($arguments[0] );
        unset($arguments["conditions"] );
        unset($arguments["bind"] );

        $params = array_merge($params, $arguments);

        /**
         * Execute the query
         */
        return $modelName::$type($params);
    }

    /**
     * Check for, and attempt to use, possible setter.
     */
    final protected function _possibleSetter(string $property, $value) : bool
    {
        $localMethods = [
            "setConnectionService" => 1,
            "setDirtyState" => 1,
            "setEventsManager" => 1,
            "setReadConnectionService" => 1,
            "setOldSnapshotData" => 1,
            "setSchema" => 1,
            "setSnapshotData" => 1,
            "setSource" => 1,
            "setTransaction" => 1,
            "setWriteConnectionService" => 1
        ];


        $possibleSetter = "set" . camelize($property);

        if (!method_exists($this, $possibleSetter)) {
            return false;
        }

        if (!isset($localMethods[$possibleSetter])) {
            $this->{$possibleSetter}($value);
        }

        return true;
    }

    /**
     * Executes internal hooks before save a record
     *
     * @todo Remove in v5.0
     * @deprecated Use preSave()
     *
     * @return bool
     */
    protected function _preSave(MetaDataInterface $metaData, bool $exists, $identityField) : bool
    {
        return $this->preSave($metaData, $exists, $identityField);
    }

    /**
     * Executes internal hooks before save a record
     *
     * @return bool
     */
    protected function preSave(MetaDataInterface $metaData, bool $exists, $identityField) : bool
    {
        /**
         * Run Validation Callbacks Before
         */
        if (\globals_get("orm.events")) {
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
        if (\globals_get("orm.virtual_foreign_keys")) {
            if ($this->_checkForeignKeysRestrict() === false) {
                return false;
            }
        }

        /**
         * Columns marked as not null are automatically validated by the ORM
         */
        if (\globals_get("orm.not_null_validations")) {
            $notNull = $metaData->getNotNullAttributes($this);

            if (is_array($notNull)) {
                /**
                 * Gets the fields that are numeric, these are validated in a
                 * different way
                 */
                $dataTypeNumeric = $metaData->getDataTypesNumeric($this);

                if (\globals_get("orm.column_renaming")) {
                    $columnMap = $metaData->getColumnMap($this);
                } else {
                    $columnMap = null;
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
                 * Get string $attributes that allow empty strings as defaults
                 */
                $emptyStringValues = $metaData->getEmptyStringAttributes($this);

                $error = false;

                foreach($notNull as $field){
                    if (is_array($columnMap)) {
                        $attributeField = $columnMap[$field] ?? null;
		                if ($attributeField === null) {
                            if (!\globals_get("orm.ignore_unknown_columns")) {
                                throw new Exception(
                                    "Column '" . $field . "' isn't part of the column map"
                                );
                            }
                        }
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
                      if (property_exists($this,$attributeField)) {
                          $value = $this->{$attributeField};
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
                                        if ($value === null || 
                                            ($value === "" && (!isset($defaultValues[$field]) 
                                                    || ($value !== $defaultValues[$field])))) {
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
                                if ($field === $identityField) {
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
                                "PresenceOf"
                            );

                            $error = true;
                        }
                    }
                }

                if ($error) {
                    if (\globals_get("orm.events")) {
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
            if (\globals_get("orm.events")) {
                $this->fireEvent("onValidationFails");
            }

            return false;
        }

        /**
         * Run Validation
         */
        if (\globals_get("orm.events")) {
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
     * @todo Remove in v5.0
     * @deprecated Use preSaveRelatedRecords()
     *
     * @param \Phalcon\Mvc\ModelInterface[] related
     */
    protected function _preSaveRelatedRecords(AdapterInterface $connection, $related) : bool
    {
        return $this->preSaveRelatedRecords($connection, $related);
    }

    /**
     * Saves related records that must be stored prior to save the master record
     *
     * @param \Phalcon\Mvc\ModelInterface[] related
     * @return bool
     */
    protected function preSaveRelatedRecords(AdapterInterface $connection, $related) : bool
    {

        $nesting = false;

        /**
         * Start an implicit transaction
         */
        $connection->begin($nesting);

        $className = get_class($this);
        $manager = $this->getModelsManager();

        foreach($related as $name => $record) {
            /**
             * Try to get a relation with the same name
             */
            $relation = $manager->getRelationByAlias($className, $name);

            if (is_object($relation)) {
                /**
                 * Get the relation type
                 */
                $type = $relation->getType();

                /**
                 * Only belongsTo are stored before save the master record
                 */
                if ($type === Relation::BELONGS_TO) {
                    if (!is_object($record)) {
                        $connection->rollback($nesting);

                        throw new Exception(
                            "Only objects can be stored as part of belongs-to relations"
                        );
                    }

                    $columns = $relation->getFields();
                    $referencedModel = $relation->getReferencedModel();
                    $referencedFields = $relation->getReferencedFields();

                    if (is_array($columns)) {
                        $connection->rollback($nesting);

                        throw new Exception("Not implemented");
                    }

                    /**
                     * If dynamic update is enabled, saving the record must not take any action
                     * Only save if the model is dirty to prevent circular relations causing an infinite loop
                     */
                    if ($record->dirtyState !== Model::DIRTY_STATE_PERSISTENT 
                        && !$record->save()) {
                        /**
                         * Get the validation messages generated by the
                         * referenced model
                         */
                        foreach($record->getMessages() as $message) {
                            /**
                             * Set the related model
                             */
                            if (is_object($message)) {
                                $message->setMetaData(
                                    [
                                        "model" => $record
                                    ]
                                );
                            }

                            /**
                             * Appends the messages to the current model
                             */
                            $this->appendMessage($message);
                        }

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
                    $this->{$columns} = $record->readAttribute($referencedFields);
                }
            }
        }

        return true;
    }

    /**
     * Executes internal events after save a record
     *
     * @todo Remove in v5.0
     * @deprecated Use postSave()
     *
     * @return bool
     */
    protected function _postSave(bool $success, bool $exists) : bool
    {
        return $this->postSave($success, $exists);
    }

    /**
     * Executes internal events after save a record
     *
     * @return bool
     */
    protected function postSave(bool $success, bool $exists) : bool
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
     * @todo Remove in v5.0
     * @deprecated Use postSaveRelatedRecords()
     *
     * @param Phalcon\Mvc\ModelInterface[] related
     * @return bool
     */
    protected function _postSaveRelatedRecords(AdapterInterface $connection, $related) : bool
    {
        return $this->postSaveRelatedRecords($connection, $related);
    }

    /**
     * Save the related records assigned in the has-one/has-many relations
     *
     * @param Phalcon\Mvc\ModelInterface[] related
     * @return bool
     */
    protected function postSaveRelatedRecords(AdapterInterface $connection, $related) : bool
    {
        $nesting = false;
        $className = get_class($this);
        $manager = $this->getModelsManager();

        foreach($related as $name => $record) {
            /**
             * Try to get a relation with the same name
             */
            $relation = $manager->getRelationByAlias($className, $name);

            if (is_object($relation)) {
                /**
                 * Discard belongsTo relations
                 */
                if ($relation->getType() === Relation::BELONGS_TO) {
                    continue;
                }

                if ((!is_object($record) && !is_array($record))) {
                    $connection->rollback($nesting);

                    throw new Exception(
                        "Only objects/arrays can be stored as part of has-many/has-one/has-one-through/has-many-to-many relations"
                    );
                }

                $columns = $relation->getFields();
                $referencedModel = $relation->getReferencedModel();
                $referencedFields = $relation->getReferencedFields();

                if (is_array($columns)) {
                    $connection->rollback($nesting);

                    throw new Exception("Not implemented");
                }

                /**
                 * Create an implicit array $for has-many/has-one records
                 */
                if (is_object($record)) {
                    $relatedRecords = [$record];
                } else {
                    $relatedRecords = $record;
                }

                if (!property_exists($this,$columns )) {
                    $connection->rollback($nesting);

                    throw new Exception(
                        "The column '" . $columns . "' needs to be present in the model"
                    );
                }
                $value = $this->{$columns};
                /**
                 * Get the value of the field from the current model
                 * Check if the relation is a has-many-to-many
                 */
                $isThrough = (bool) $relation->isThrough();

                /**
                 * Get the rest of intermediate model info
                 */
                if ($isThrough) {
                    $intermediateModelName = $relation->getIntermediateModel();
                    $intermediateFields = $relation->getIntermediateFields();
                    $intermediateReferencedFields = $relation->getIntermediateReferencedFields();
                }

                foreach($relatedRecords as $recordAfter){
                    /**
                     * For non has-many-to-many relations just assign the local
                     * value in the referenced model
                     */
                    if (!$isThrough) {
                        /**
                         * Assign the value to the
                         */
                        $recordAfter->writeAttribute($referencedFields, $value);
                    }

                    /**
                     * Save the record and get messages
                     */
                    if (!$recordAfter->save()) {
                        /**
                         * Get the validation messages generated by the
                         * referenced model
                         */
                        foreach($recordAfter->getMessages() as $message) {
                            /**
                             * Set the related model
                             */
                            if (is_object($message)) {
                                $message->setMetaData(
                                    [
                                        "model" => $recordAfter
                                    ]
                                );
                            }

                            /**
                             * Appends the messages to the current model
                             */
                            $this->appendMessage($message);
                        }

                        /**
                         * Rollback the implicit transaction
                         */
                        $connection->rollback($nesting);

                        return false;
                    }

                    if ($isThrough) {
                        /**
                         * Create a new instance of the intermediate model
                         */
                        $intermediateModel = $manager->load(
                            $intermediateModelName
                        );

                        /**
                         *  Has-one-through relations can only use one intermediate model.
                         *  If it already exist, it can be updated with the new referenced key.
                         */
                        if ($relation->getType() === Relation::HAS_ONE_THROUGH) {
                            $existingIntermediateModel = $intermediateModel->findFirst(
                                [
                                    "[" . $intermediateFields . "] = ?0",
                                    "bind" => [$value]
                                ]
                            );

                            if ($existingIntermediateModel) {
                                $intermediateModel = $existingIntermediateModel;
                            }
                        }

                        /**
                         * Write value in the intermediate model
                         */
                        $intermediateModel->writeAttribute($intermediateFields, $value);

                        /**
                         * Get the value from the referenced model
                         */
                        $intermediateValue = $recordAfter->readAttribute(
                            $referencedFields
                        );

                        /**
                         * Write the intermediate value in the intermediate model
                         */
                        $intermediateModel->writeAttribute($intermediateReferencedFields, $intermediateValue);

                        /**
                         * Save the record and get messages
                         */
                        if (!$intermediateModel->save()) {
                            /**
                             * Get the validation messages generated by the referenced model
                             */
                            foreach($intermediateModel->getMessages() as $message) {
                                /**
                                 * Set the related model
                                 */
                                if (is_object($message)) {
                                    $message->setMetaData(
                                        [
                                            "model" => $intermediateModel
                                        ]
                                    );
                                }

                                /**
                                 * Appends the messages to the current model
                                 */
                                $this->appendMessage($message);
                            }

                            /**
                             * Rollback the implicit transaction
                             */
                            $connection->rollback($nesting);

                            return false;
                        }
                    }
                }
            } else {
                if (!is_array($record)) {
                    $connection->rollback($nesting);

                    throw new Exception(
                        "There are no defined relations for the model '" . $className . "' using alias '" . $name . "'"
                    );
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
     */
    protected function allowEmptyStringValues(array $attributes): void
    {
        

        $keysAttributes = [];

        foreach($attributes as $attribute){
            $keysAttributes[$attribute] = true;
        }

        $this->getModelsMetaData()->setEmptyStringAttributes($this, $keysAttributes);
    }

    /**
     * Cancel the current operation
     *
     * @todo Remove in v5.0
     * @deprecated Use cancelOperation()
     */
    protected function _cancelOperation()
    {
        return $this->cancelOperation();
    }

    /**
     * Cancel the current operation
     */
    protected function cancelOperation()
    {
        if ($this->operationMade == self::OP_DELETE) {
            $this->fireEvent("notDeleted");
        } else {
            $this->fireEvent("notSaved");
        }
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
     * @param array|null $options = [
     *     'reusable' => false,
     *     'alias' => 'someAlias',
     *     'foreignKey' => [
     *         'message' => null,
     *         'allowNulls' => false,
     *         'action' => null
     *     ],
     *     'params' => [
     *         'conditions' => ''
     *         'columns' => '',
     *         'bind' => [],
     *         'bindTypes => [],
     *         'order' => '',
     *         'limit' => 10,
     *         'offset' => 5,
     *         'group' => 'name, status',
     *         'for_updated' => false,
     *         'shared_lock' => false,
     *         'cache' => [
     *             'lifetime' => 3600,
     *             'key' => 'my-find-key'
     *         ],
     *         'hydration' => null
     *     ]
     * ]
     */
    protected function belongsTo($fields, string $referenceModel, $referencedFields, $options = null): Relation
    {
        return $this->modelsManager->addBelongsTo(
            $this,
            $fields,
            $referenceModel,
            $referencedFields,
            $options
        );
    }

    /**
     * shared prepare query logic for find and findFirst method
     */
    private static function getPreparedQuery($params, $limit = null): Query
    {
        $container = Di::getDefault();
        $manager = $container->getShared("modelsManager");

        /**
         * Builds a query with the passed parameters
         */
        $builder = $manager->createBuilder($params);

        $builder->from(
            get_called_class()
        );

        if ($limit != null) {
            $builder->limit($limit);
        }

        $query = $builder->getQuery();

        /**
         * Check for bind parameters
         */
        $bindParams = $params["bind"] ?? null;
		if ($bindParams !== null) {
            if (is_array($bindParams)) {
                $query->setBindParams($bindParams, true);
            }

            $bindTypes = $params["bindTypes"] ?? null;
		if ($bindTypes !== null) {
                if (is_array($bindTypes)) {
                    $query->setBindTypes($bindTypes, true);
                }
            }
        }

        $transaction = $params[$self::TRANSACTION_INDEX] ?? null;
		if ($transaction !== null) {
            if ($transaction instanceof TransactionInterface) {
                $query->setTransaction($transaction);
            }
        }

        /**
         * Pass the cache options to the query
         */
        $cache = $params["cache"] ?? null;
		if ($cache !== null) {
            $query->cache($cache);
        }

        return $query;
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
     * @param array|null $options = [
     *     'reusable' => false,
     *     'alias' => 'someAlias',
     *     'foreignKey' => [
     *         'message' => null,
     *         'allowNulls' => false,
     *         'action' => null
     *     ],
     *     'params' => [
     *         'conditions' => ''
     *         'columns' => '',
     *         'bind' => [],
     *         'bindTypes => [],
     *         'order' => '',
     *         'limit' => 10,
     *         'offset' => 5,
     *         'group' => 'name, status',
     *         'for_updated' => false,
     *         'shared_lock' => false,
     *         'cache' => [
     *             'lifetime' => 3600,
     *             'key' => 'my-find-key'
     *         ],
     *         'hydration' => null
     *     ]
     * ]
     */
    protected function hasMany($fields, string $referenceModel, $referencedFields, $options = null): Relation
    {
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
     * @param    string|array $fields
     * @param    string|array $intermediateFields
     * @param    string|array $intermediateReferencedFields
     * @param    string|array $referencedFields
     * @param    array $options
     *
     * @param array|null $options = [
     *     'reusable' => false,
     *     'alias' => 'someAlias',
     *     'foreignKey' => [
     *         'message' => null,
     *         'allowNulls' => false,
     *         'action' => null
     *     ],
     *     'params' => [
     *         'conditions' => ''
     *         'columns' => '',
     *         'bind' => [],
     *         'bindTypes => [],
     *         'order' => '',
     *         'limit' => 10,
     *         'offset' => 5,
     *         'group' => 'name, status',
     *         'for_updated' => false,
     *         'shared_lock' => false,
     *         'cache' => [
     *             'lifetime' => 3600,
     *             'key' => 'my-find-key'
     *         ],
     *         'hydration' => null
     *     ]
     * ]
     */
    protected function hasManyToMany($fields, 
        string $intermediateModel, $intermediateFields, $intermediateReferencedFields,
        string $referenceModel, $referencedFields, $options = null): Relation
    {
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
     * @param array|null $options = [
     *     'reusable' => false,
     *     'alias' => 'someAlias',
     *     'foreignKey' => [
     *         'message' => null,
     *         'allowNulls' => false,
     *         'action' => null
     *     ],
     *     'params' => [
     *         'conditions' => ''
     *         'columns' => '',
     *         'bind' => [],
     *         'bindTypes => [],
     *         'order' => '',
     *         'limit' => 10,
     *         'offset' => 5,
     *         'group' => 'name, status',
     *         'for_updated' => false,
     *         'shared_lock' => false,
     *         'cache' => [
     *             'lifetime' => 3600,
     *             'key' => 'my-find-key'
     *         ],
     *         'hydration' => null
     *     ]
     * ]
     */
    protected function hasOne($fields, string $referenceModel, $referencedFields, $options = null): Relation
    {
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
     * @param    string|array $fields
     * @param    string|array $intermediateFields
     * @param    string|array $intermediateReferencedFields
     * @param    string|array $referencedFields
     * @param    array $options
     */
    protected function hasOneThrough($fields, 
        string $intermediateModel, $intermediateFields, $intermediateReferencedFields,
        string $referenceModel, $referencedFields, $options = null): Relation
    {
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
     */
    protected function keepSnapshots(bool $keepSnapshot): void
    {
        $this->modelsManager->keepSnapshots($this, $keepSnapshot);
    }

    /**
     * Sets schema name where the mapped table is located
     */
    final protected function setSchema(string $schema): ModelInterface
    {
        $this->modelsManager->setModelSchema($this, $schema);

        return $this;
    }

    /**
     * Sets the table name to which model should be mapped
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
     */
    protected function skipAttributes(array $attributes)
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
     */
    protected function skipAttributesOnCreate(array $attributes): void
    {
        
        $keysAttributes = [];

        foreach($attributes as $attribute){
            $keysAttributes[$attribute] = null;
        }

        $this->getModelsMetaData()->setAutomaticCreateAttributes($this, $keysAttributes);
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
     */
    protected function skipAttributesOnUpdate(array $attributes): void
    {
    
        $keysAttributes = [];

        foreach($attributes as $attribute){
            $keysAttributes[$attribute] = null;
        }

        $this->getModelsMetaData()->setAutomaticUpdateAttributes($this, $keysAttributes);
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
     */
    protected function useDynamicUpdate(bool $dynamicUpdate): void
    {
        $this->modelsManager->useDynamicUpdate($this, $dynamicUpdate);
    }

    /**
     * Executes validators on every validation call
     *
     *```php
     * use Phalcon\Mvc\Model;
     * use Phalcon\Validation;
     * use Phalcon\Validation\Validator\ExclusionIn;
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
     */
    protected function validate(ValidationInterface $validator) : bool
    {
        

        $messages = $validator->validate(null, $this);

        // Call the validation, if it returns not the bool
        // we append the messages to the current object
        if (is_bool($messages)) {
            return $messages;
        }

        foreach(iterator($messages) as $message) {
            $this->appendMessage(
                new Message(
                    $message->getMessage(),
                    $message->getField(),
                    $message->getType(),
                    $message->getCode()
                )
            );
        }

        // If there is a message, it returns false otherwise true
        return !count($messages);
    }

    /**
     * Check whether validation process has generated any messages
     *
     *```php
     * use Phalcon\Mvc\Model;
     * use Phalcon\Validation;
     * use Phalcon\Validation\Validator\ExclusionIn;
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
     */
    public function validationHasFailed() : bool
    {
        return count($this->errorMessages) > 0;
    }

    /**
     * Attempts to find key case-insensitively
     */
    private static function caseInsensitiveColumnMap($columnMap, $key): string
    {
        

        foreach(array_keys($columnMap) as $cmKey) {
            if (strtolower($cmKey) == strtolower($key)) {
                return $cmKey;
            }
        }

        return $key;
    }
}
