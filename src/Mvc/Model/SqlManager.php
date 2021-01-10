<?php
namespace Phiz\Mvc\Model;

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */


use Phiz\Db\Adapter\AdapterInterface;
use Phiz\Di\DiInterface;
use Phiz\Di\InjectionAwareInterface;
use Phiz\Events\EventsAwareInterface;
use Phiz\Events\ManagerInterface as EventsManagerInterface;
use Phiz\Mvc\ModelInterface;
use Phiz\Mvc\Model\Query\Builder;
use Phiz\Mvc\Model\Query\BuilderInterface;
use Phiz\Mvc\Model\Query\StatusInterface;
use Phiz\Mvc\Model\ManagerInterface;
use Phiz\Support\Str\Uncamelize;
use Pnalcon\Reflect\Create;

/**
 * Phiz\Mvc\Model\Manager
 *
 * This components controls the initialization of models, keeping record of
 * relations between the different models of the application.
 *
 * A ModelsManager is injected to a model via a Dependency Injector/Services
 * Container such as Phiz\Di.
 *
 * ```php
 * use Phiz\Di;
 * use Phiz\Mvc\Model\Manager as ModelsManager;
 *
 * $di = new Di();
 *
 * $di->set(
 *     "modelsManager",
 *     function() {
 *         return new ModelsManager();
 *     }
 * );
 *
 * $robot = new Robots($di);
 * ```
 */

class SqlManager implements ManagerInterface, InjectionAwareInterface, EventsAwareInterface
{
    protected array $aliases = [];

    /**
     * Models' behaviors
     */
    protected array $behaviors = [];

    /**
     * Belongs to relations
     */
    protected array $belongsTo = [];

    /**
     * All the relationships by model
     */
    protected array $belongsToSingle = [];

    protected $container;

    protected array $customEventsManager = [];

    /**
     * Does the model use dynamic update, instead of updating all rows?
     */
    protected array $dynamicUpdate = [];

    protected $eventsManager;

    /**
     * Has many relations
     */
    protected array $hasMany = [];

    /**
     * Has many relations by model
     */
    protected array $hasManySingle = [];

    /**
     * Has many-Through relations
     */
    protected array $hasManyToMany = [];

    /**
     * Has many-Through relations by model
     */
    protected array $hasManyToManySingle = [];

    /**
     * Has one relations
     */
    protected array $hasOne = [];

    /**
     * Has one relations by model
     */
    protected array $hasOneSingle = [];

    /**
     * Has one through relations
     */
    protected array $hasOneThrough = [];

    /**
     * Has one through relations by model
     */
    protected array $hasOneThroughSingle = [];

    /**
     * Mark initialized models
     */
    protected array $initialized = [];

    protected array $keepSnapshots = [];

    /**
     * Last model initialized
     */
    protected $lastInitialized;

    /**
     * Last query created/executed
     */
    protected $lastQuery;

    protected array $modelVisibility = [];

    protected string $prefix = "";

    protected array $readConnectionServices = [];

    protected array $sources = [];

    protected array $schemas = [];

    protected array $writeConnectionServices = [];

    /**
     * Stores a list of reusable instances
     */
    protected array $reusable = [];

    /**
     * Sets the DependencyInjector container
     */
    public function setDI(DiInterface $container) : void
    { 
        $this->container = $container;
    }

    /**
     * Returns the DependencyInjector container
     */
    public function getDI() : DiInterface
    {
        return $this->container;
    }

    /**
     * Sets a global events manager
     */
    public function setEventsManager(EventsManagerInterface $eventsManager) : void
    { 
        $this->eventsManager = $eventsManager;
    }

    /**
     * Returns the internal event manager
     */
    public function getEventsManager() : EventsManagerInterface
    {
        return $this->eventsManager;
    }

    /**
     * Sets a custom events manager for a specific model
     */
    public function setCustomEventsManager(ModelInterface $model, 
        EventsManagerInterface $eventsManager) : void
    { 
        $this->customEventsManager[\get_class_lower($model)] = $eventsManager;
    }

    /**
     * Returns a custom events manager related to a model or null if there is no related events manager
     */
    public function getCustomEventsManager(ModelInterface $model): ?EventsManagerInterface
    {
        return $this->customEventsManager[\get_class_lower($model)] ?? null;
    }

    /**
     * Initializes a model in the model manager
     */
    public function initialize(ModelInterface $model) : bool
    {
        $className = \get_class_lower($model);

        /**
         * Models are just initialized once per request
         */
        if (isset($this->initialized[$className])) {
            return false;
        }

        /**
         * Update the model as initialized, this avoid cyclic initializations
         */ $this->initialized[$className] = true;

        /**
         * Call the 'initialize' method if it's implemented
         */
        if (method_exists($model, "initialize")) {
            $model->{"initialize"}();
        }

        /**
         * Update the last initialized model, so it can be used in
         * modelsManager:afterInitialize
         */ $this->lastInitialized = $model;

        /**
         * If an EventsManager is available we pass to it every initialized
         * model
         */ 
        $eventsManager = $this->eventsManager;

        if (is_object($eventsManager)) {
            $eventsManager->fire("modelsManager:afterInitialize",$this, $model);
        }

        return true;
    }

    /**
     * Check whether a model is already initialized
     */
    public function isInitialized(string $className) : bool
    {
        return isset($this->initialized[strtolower($className)]);
    }

    /**
     * Get last initialized model
     */
    public function getLastInitialized() : ModelInterface
    {
        return $this->lastInitialized;
    }

    /**
     * Loads a model throwing an exception if it doesn't exist
     */
    public function load(string $modelName) : ModelInterface
    {
         /**
         * The model doesn't exist throw an exception
         */
        if (!class_exists($modelName)) {
            throw new Exception(
                "Model '" . $modelName . "' could not be loaded"
            );
        }

        /**
         * Load it using an autoloader
         */ $model = Create::instance_params(
            $modelName,
            [
                null,
                $this->container,
                this
            ]
        );

        return $model;
    }

    /**
     * Sets the prefix for all $model sources.
     *
     * ```php
     * use Phiz\Mvc\Model\Manager;
     *
     * $di->set(
     *     "modelsManager",
     *     function () {
     *         $modelsManager = new Manager();
     *
     *         $modelsManager->setModelPrefix("wp_");
     *
     *         return $modelsManager;
     *     }
     * );
     *
     * $robots = new Robots();
     *
     * echo $robots->getSource(); // wp_robots
     * ```
     */
    public function setModelPrefix(string $prefix) : void
    { $this->prefix = prefix;
    }

    /**
     * Returns the prefix for all model sources.
     */
    public function getModelPrefix() : string
    {
        return $this->prefix;
    }

    /**
     * Sets the mapped source for a model
     */
    public function setModelSource(ModelInterface $model, string $source) : void
    { 
        $this->sources[\get_class_lower($model)] = $source;
    }

    /**
     * Check whether a model property is declared as public.
     *
     * ```php
     * $isPublic = $manager->isVisibleModelProperty(
     *     new Robots(),
     *     "name"
     * );
     * ```
     */
    final public function isVisibleModelProperty(ModelInterface $model, string $property) : bool
    {
        $className = get_class($model);

        if (!isset($this->modelVisibility[$className])) { $this->modelVisibility[$className] = get_object_vars($model);
        } $properties = $this->modelVisibility[$className];

        return array_key_exists($property, $properties);
    }

    /**
     * Returns the mapped source for a model
     */
    public function getModelSource(ModelInterface $model) : string
    {
        $entityName = \get_class_lower($model);

        if (!isset($this->sources[$entityName])) {
            $this->setModelSource(
                $model,
                Uncamelize::fn(
                    \get_class_ns($model)
                )
            );
        }

        return $this->prefix . $this->sources[$entityName];
    }

    /**
     * Sets the mapped schema for a model
     */
    public function setModelSchema(ModelInterface $model, string $schema) : void
    { $this->schemas[\get_class_lower($model)] = $schema;
    }

    /**
     * Returns the mapped schema for a model
     */
    public function getModelSchema(ModelInterface $model) : string
    {
        return $this->schemas[\get_class_lower($model)] ?? "";
    }

    /**
     * Sets both write and read connection service for a model
     */
    public function setConnectionService(ModelInterface $model, string $connectionService) : void
    {
        $this->setReadConnectionService($model, $connectionService);
        $this->setWriteConnectionService($model, $connectionService);
    }

    /**
     * Sets write connection service for a model
     */
    public function setWriteConnectionService(ModelInterface $model, string $connectionService) : void
    { $this->writeConnectionServices[\get_class_lower($model)] = $connectionService;
    }

    /**
     * Sets read connection service for a model
     */
    public function setReadConnectionService(ModelInterface $model, string $connectionService) : void
    { $this->readConnectionServices[\get_class_lower($model)] = $connectionService;
    }

    /**
     * Returns the connection to read data related to a model
     */
    public function getReadConnection(ModelInterface $model) : AdapterInterface
    {
        return $this->getConnection($model, $this->readConnectionServices);
    }

    /**
     * Returns the connection to write data related to a model
     */
    public function getWriteConnection(ModelInterface $model) : AdapterInterface
    {
        return $this->getConnection($model, $this->writeConnectionServices);
    }

    /**
     * Returns the connection to read or write data related to a model depending on the connection services.
     *
     * @todo Remove in v5.0
     * @deprecated Use getConnection()
     *
     * @return AdapterInterface
     */
    protected function _getConnection(ModelInterface $model, $connectionServices) : AdapterInterface
    {
        return $this->getConnection($model, $connectionServices);
    }

    /**
     * Returns the connection to read or write data related to a model depending on the connection services.
     *
     * @return AdapterInterface
     */
    protected function getConnection(ModelInterface $model, $connectionServices) : AdapterInterface
    {
        $service = $this->getConnectionService($model, $connectionServices); 
        $container = $this->container;

        if (!is_object($container)) {
            throw new Exception(
                Exception::containerServiceNotFound(
                    "the services related to the ORM"
                )
            );
        }

        /**
         * Request the connection service from the DI
         */ 
        $connection = $container->getShared($service);

        if (!is_object($connection)) {
            throw new Exception("Invalid injected connection service");
        }

        return $connection;
    }

    /**
     * Returns the connection service name used to read data related to a model
     */
    public function getReadConnectionService(ModelInterface $model) : string
    {
        return $this->getConnectionService(
            $model,
            $this->readConnectionServices
        );
    }

    /**
     * Returns the connection service name used to write data related to a model
     */
    public function getWriteConnectionService(ModelInterface $model) : string
    {
        return $this->getConnectionService(
            $model,
            $this->writeConnectionServices
        );
    }

    /**
     * Returns the connection service name used to read or write data related to
     * a model depending on the connection services
     *
     * @todo Remove in v5.0
     * @deprecated Use getConnectionService()
     *
     * @return string
     */
    public function _getConnectionService(ModelInterface $model, $connectionServices) : string
    {
        return $this->getConnectionService($model, $connectionServices);
    }

    /**
     * Returns the connection service name used to read or write data related to
     * a model depending on the connection services
     *
     * @return string
     */
    public function getConnectionService(ModelInterface $model, $connectionServices) : string
    {
        return $connectionServices[\get_class_lower($model)] ?? "db";
    }

    /**
     * Receives events generated in the models and dispatches them to an
     * events-manager if available. Notify the behaviors that are listening in
     * the model
     */
    public function notifyEvent(string $eventName, ModelInterface $model) : bool
    {
        $status = true;

        /**
         * Dispatch events to the global events manager
         */
        $modelsBehaviors = $this->behaviors[\get_class_lower($model)] ?? null;
		if ($modelsBehaviors !== null) {
            /**
             * Notify all the events on the behavior
             */
            foreach($modelsBehaviors as $behavior) {
                $status = $behavior->notify($eventName, $model);

                if ($status === false) {
                    return false;
                }
            }
        }

        /**
         * Dispatch events to the global events manager
         */ $eventsManager = $this->eventsManager;

        if (is_object($eventsManager)) { $status = $eventsManager->fire(
                "model:" . $eventName,
                $model
            );

            if ($status === false) {
                return false;
            }
        }

        /**
         * A model can has a specific events manager for it
         */
        $customEventsManager = $this->customEventsManager[\get_class_lower($model)] ?? null;
		if ($customEventsManager !== null) { $status =$customEventsManager->fire(
                "model:" . $eventName,
                $model
            );

            if ($status === false) {
                return false;
            }
        }

        return $status;
    }

    /**
     * Dispatch an event to the listeners and behaviors
     * This method expects that the endpoint listeners/behaviors returns true
     * meaning that a least one was implemented
     */
    public function missingMethod(ModelInterface $model, string $eventName, array $data = []) : bool
    {
        /**
         * Dispatch events to the global events manager
         */
        $modelsBehaviors = $this->behaviors[\get_class_lower($model)] ?? null;
        if ($modelsBehaviors !== null) {
            /**
             * Notify all the events on the behavior
             */
            foreach ($modelsBehaviors as $behavior) {

                $result = $behavior->missingMethod($model, $eventName, $data);

                if ($result !== null) {
                    return $result;
                }
            }
            return false;
        }

        /**
         * Dispatch events to the global events manager
         */ $eventsManager = $this->eventsManager;

        if (is_object($eventsManager)) {
            return $eventsManager->fire(
                "model:" . $eventName, $model, $data );
        }

        return null;
    }

    /**
     * Binds a behavior to a model
     */
    public function addBehavior(ModelInterface $model, BehaviorInterface $behavior) : void
    {
        $entityName = \get_class_lower($model);

        if (!isset($this->behaviors[$entityName])) { 
            $this->behaviors[$entityName] = [];
        }

        /**
         * Append the behavior to the list of behaviors
         */ 
        $this->behaviors[$entityName][] = $behavior;
    }

    /**
     * Sets if a model must keep snapshots
     */
    public function keepSnapshots(ModelInterface $model, bool $keepSnapshots) : void
    { 
        $this->keepSnapshots[\get_class_lower($model)] = $keepSnapshots;
    }

    /**
     * Checks if a model is keeping snapshots for the queried records
     */
    public function isKeepingSnapshots(ModelInterface $model) : bool
    {
        return $this->keepSnapshots[\get_class_lower($model)] ?? false; 
    }

    /**
     * Sets if a model must use dynamic update instead of the all-field update
     */
    public function useDynamicUpdate(ModelInterface $model, bool $dynamicUpdate) : void
    {
        $entityName = get_class_lower($model);
        $this->dynamicUpdate[$entityName] = $dynamicUpdate;
        $this->keepSnapshots[$entityName] = $dynamicUpdate;
    }

    /**
     * Checks if a model is using dynamic update instead of all-field update
     */
    public function isUsingDynamicUpdate(ModelInterface $model) : bool
    {
        return $this->dynamicUpdate[\get_class_lower($model)] ?? null;
    }

    /**
     * Setup a 1-1 relation between two models
     *
     * @param array options
     */
    public function addHasOne(ModelInterface $model, $fields, string $referencedModel,
        $referencedFields, $options = null) : RelationInterface
    {
        
        $entityName = \get_class_lower($model);
        $referencedEntity = strtolower($referencedModel); 
        $keyRelation = $entityName . "$" . $referencedEntity;
        $relations = $this->hasOne[$keyRelation] ?? [];

        /**
         * Check if the number of fields are the same
         */
        if (is_array($referencedFields)) {
            if (count($fields) !== count($referencedFields)) {
                throw new Exception(
                    "Number of referenced fields are not the same"
                );
            }
        }

        /**
         * Create a relationship instance
         */ $relation = new Relation(
            Relation::HAS_ONE,
            $referencedModel,
            $fields,
            $referencedFields,
            $options
        );

        /**
         * Check an alias for the relation
         */
        $alias = $options["alias"] ?? null;
		if ($alias !== null) {
            if (!is_string($alias)) {
                throw new Exception("Relation alias must be a string");
            } 
            $lowerAlias = strtolower($alias);
        } else { 
            $lowerAlias = $referencedEntity;
        }

        /**
         * Append a new relationship
         * Update the global alias
         * Update the relations
         */ $relations[] = $relation;
            $this->aliases[$entityName . "$" . $lowerAlias] = $relation;
            $this->hasOne[$keyRelation] = $relations;

        /**
         * Get existing relations by model
         */
        $singleRelations = $this->hasOneSingle[$entityName] ?? [];
        

        /**
         * Append a new relationship
         */ 
        $singleRelations[] = $relation;

        /**
         * Update relations by model
         */ 
        $this->hasOneSingle[$entityName] = $singleRelations;

        return $relation;
    }

    /**
     * Setups a relation 1-1 between two models using an intermediate model
     *
     * @param    string fields
     * @param    string $intermediateFields
     * @param    string intermediateReferencedFields
     * @param    string $referencedFields
     * @param   array options
     */
    public function addHasOneThrough(ModelInterface $model, $fields, string $intermediateModel,
        $intermediateFields, $intermediateReferencedFields, 
        string $referencedModel, $referencedFields, $options = null) : RelationInterface
    {
        $entityName = \get_class_lower($model);
        $intermediateEntity = strtolower($intermediateModel);
        $referencedEntity = strtolower($referencedModel);
        $keyRelation = $entityName . "$" . $referencedEntity; 
        $hasOneThrough = $this->hasOneThrough;

        $relations = $hasOneThrough[$keyRelation] ?? [];

        /**
         * Check if the number of fields are the same from the model to the
         * intermediate model
         */
        if (is_array($intermediateFields)) {
            if  (count($fields) !== count($intermediateFields)) {
                throw new Exception(
                    "Number of referenced fields are not the same"
                );
            }
        }

        /**
         * Check if the number of fields are the same from the intermediate
         * model to the referenced model
         */
        if (is_array($intermediateReferencedFields)) {
            if  (count($fields) != count($intermediateFields)) {
                throw new Exception(
                    "Number of referenced fields are not the same"
                );
            }
        }

        /**
         * Create a relationship instance
         */ $relation = new Relation(
            Relation::HAS_ONE_THROUGH,
            $referencedModel,
            $fields,
            $referencedFields,
            $options
        );

        /**
         * Set extended intermediate relation data
         */
       $relation->setIntermediateRelation(
            $intermediateFields,
            $intermediateModel,
            $intermediateReferencedFields
        );

        /**
         * Check an alias for the relation
         */
        $alias = $options["alias"] ?? null;
		if ($alias !== null) {
            if (!is_string($alias)) {
                throw new Exception("Relation alias must be a string");
            } 
            $lowerAlias = strtolower($alias);
        } else { 
            $lowerAlias = $referencedEntity;
        }

        /**
         * Append a new relationship
         */ $relations[] = $relation;

        /**
         * Update the global alias
         */ $this->aliases[$entityName . "$" . $lowerAlias] = $relation;

        /**
         * Update the relations
         */ $this->hasOneThrough[$keyRelation] = $relations;

        /**
         * Get existing relations by model
         */
        $singleRelations =  $this->hasOneThroughSingle[$entityName] ?? [];

        /**
         * Append a new relationship
         */ $singleRelations[] = $relation;

        /**
         * Update relations by model
         */ $this->hasOneThroughSingle[$entityName] = $singleRelations;

        return $relation;
    }

    /**
     * Setup a relation reverse many to one between two models
     *
     * @param    array options
     */
    public function addBelongsTo(ModelInterface $model, $fields, string $referencedModel,
        $referencedFields, $options = null) : RelationInterface
    {

        $entityName = \get_class_lower($model);
        $referencedEntity = strtolower($referencedModel); 
        $keyRelation = $entityName . "$" . $referencedEntity;
        $relations = $this->belongsTo[$keyRelation] ?? [];

        /**
         * Check if the number of fields are the same
         */
        if (is_array($referencedFields)) {
            if  (count($fields) !== count($referencedFields)) {
                throw new Exception(
                    "Number of referenced fields are not the same"
                );
            }
        }

        /**
         * Create a relationship instance
         */ $relation = new Relation(
            Relation::BELONGS_TO,
            $referencedModel,
            $fields,
            $referencedFields,
            $options
        );

        /**
         * Check an alias for the relation
         */
        $alias = $options["alias"] ?? null;
		if ($alias !== null) {
            if (!is_string($alias)) {
                throw new Exception("Relation alias must be a string");
            } 
            $lowerAlias = strtolower($alias);
        } else {
            $lowerAlias = $referencedEntity;
        }

        /**
         * Append a new relationship
         * Update the global alias
         * Update the relations
         */ $relations[] = $relation;
            $this->aliases[$entityName . "$" . $lowerAlias] = $relation;
            $this->belongsTo[$keyRelation] = $relations;

        /**
         * Get existing relations by model
         */
        $singleRelations = $this->belongsToSingle[$entityName] ?? [];


        /**
         * Append a new relationship
         */ 
        $singleRelations[] = $relation;

        /**
         * Update relations by model
         */ 
        $this->belongsToSingle[$entityName] = $singleRelations;

        return $relation;
    }

    /**
     * Setup a relation 1-n between two models
     *
     * @param    mixed $referencedFields
     * @param    array options
     */
    public function addHasMany(ModelInterface $model, $fields, string $referencedModel,
        $referencedFields, $options = null) : RelationInterface
    {
        $entityName = \get_class_lower($model);
        $referencedEntity = strtolower($referencedModel);
        $keyRelation = $entityName . "$" . $referencedEntity;
        $hasMany = $this->hasMany;
        $relations = $hasMany[$keyRelation] ?? [];

        /**
         * Check if the number of fields are the same
         */
        if (is_array($referencedFields)) {
            if (count($fields) != count($referencedFields)) {
                throw new Exception(
                    "Number of referenced fields are not the same"
                );
            }
        }

        /**
         * Create a relationship instance
         */ $relation = new Relation(
            Relation::HAS_MANY,
            $referencedModel,
            $fields,
            $referencedFields,
            $options
        );

        /**
         * Check an alias for the relation
         */
        $alias = $options["alias"] ?? null;
		if ($alias !== null) {
            if (!is_string($alias)) {
                throw new Exception("Relation alias must be a string");
            } 
            $lowerAlias = strtolower($alias);
        } else { 
            $lowerAlias = $referencedEntity;
        }

        /**
         * Append a new relationship
         * Update the global alias
         * Update the relations
         */ 
        $relations[] = $relation;
        $this->aliases[ $entityName . "$" . $lowerAlias] = $relation;
        $this->hasMany[$keyRelation] = $relations;

        /**
         * Get existing relations by model
         */
        $singleRelations = $this->hasManySingle[$entityName]  ?? [];

        /**
         * Append a new relationship
         */ 
        $singleRelations[] = $relation;

        /**
         * Update relations by model
         */ 
        $this->hasManySingle[$entityName] = $singleRelations;

        return $relation;
    }

    /**
     * Setups a relation n-m between two models
     *
     * @param    string fields
     * @param    string $intermediateFields
     * @param    string intermediateReferencedFields
     * @param    string $referencedFields
     * @param   array options
     */
    public function addHasManyToMany(ModelInterface $model, $fields, string $intermediateModel,
        $intermediateFields, $intermediateReferencedFields, string $referencedModel, $referencedFields, $options = null) : RelationInterface
    {
        $entityName = \get_class_lower($model);
        $intermediateEntity = strtolower($intermediateModel);
        $referencedEntity = strtolower($referencedModel);
        $keyRelation = $entityName . "$" . $referencedEntity; 
        $hasManyToMany = $this->hasManyToMany;
        $relations = $hasManyToMany[$keyRelation] ?? [];

        /**
         * Check if the number of fields are the same from the model to the
         * intermediate model
         */
        if (is_array($intermediateFields)) {
            if (count($fields) !== count($intermediateFields)) {
                throw new Exception(
                    "Number of referenced fields are not the same"
                );
            }
        }

        /**
         * Check if the number of fields are the same from the intermediate
         * model to the referenced model
         */
        if (is_array($intermediateReferencedFields)) {
            if (count($fields) !== count($intermediateFields)) {
                throw new Exception(
                    "Number of referenced fields are not the same"
                );
            }
        }

        /**
         * Create a relationship instance
         */ $relation = new Relation(
            Relation::HAS_MANY_THROUGH,
            $referencedModel,
            $fields,
            $referencedFields,
            $options
        );

        /**
         * Set extended intermediate relation data
         */
       $relation->setIntermediateRelation(
            $intermediateFields,
            $intermediateModel,
            $intermediateReferencedFields
        );

        /**
         * Check an alias for the relation
         */
        $alias = $options["alias"] ?? null;
		if ($alias !== null) {
            if (!is_string($alias)) {
                throw new Exception("Relation alias must be a string");
            } $lowerAlias = strtolower($alias);
        } else { $lowerAlias = $referencedEntity;
        }

        /**
         * Append a new relationship
         */ $relations[] = $relation;

        /**
         * Update the global alias
         */ $this->aliases[$entityName . "$" . $lowerAlias] = $relation;

        /**
         * Update the relations
         */ $this->hasManyToMany[$keyRelation] = $relations;

        $singleRelations = $this->hasManyToManySingle[$entityName] ?? [];
        /**
         * Get existing relations by model
         */

        /**
         * Append a new relationship
         */ 
        $singleRelations[] = $relation;

        /**
         * Update relations by model
         */ 
        $this->hasManyToManySingle[$entityName] = $singleRelations;

        return $relation;
    }

    /**
     * Checks whether a model has a belongsTo relation with another model
     */
    public function existsBelongsTo(string $modelName, string $modelRelation) : bool
    {
        $entityName = strtolower($modelName);

        /**
         * Relationship unique key
         */ 
        $keyRelation = $entityName . "$" . strtolower($modelRelation);

        /**
         * Initialize the model first
         */
        if (!isset($this->initialized[$entityName])) {
            $this->load($modelName);
        }

        return isset($this->belongsTo[$keyRelation]);
    }

    /**
     * Checks whether a model has a hasMany relation with another model
     */
    public function existsHasMany(string $modelName, string $modelRelation) : bool
    {
        $entityName = strtolower($modelName);

        /**
         * Relationship unique key
         */ $keyRelation = $entityName . "$" . strtolower($modelRelation);

        /**
         * Initialize the model first
         */
        if (!isset($this->initialized[$entityName])) {
            $this->load($modelName);
        }

        return isset($this->hasMany[$keyRelation]);
    }

    /**
     * Checks whether a model has a hasOne relation with another model
     */
    public function existsHasOne(string $modelName, string $modelRelation) : bool
    {
        $entityName = strtolower($modelName);

        /**
         * Relationship unique key
         */ $keyRelation = $entityName . "$" . strtolower($modelRelation);

        /**
         * Initialize the model first
         */
        if (!isset($this->initialized[$entityName])) {
            $this->load($modelName);
        }

        return isset($this->hasOne[$keyRelation]);
    }

    /**
     * Checks whether a model has a hasOneThrough relation with another model
     */
    public function existsHasOneThrough(string $modelName, string $modelRelation) : bool
    {
        $entityName = strtolower($modelName);

        /**
         * Relationship unique key
         */ $keyRelation = $entityName . "$" . strtolower($modelRelation);

        /**
         * Initialize the model first
         */
        if (!isset($this->initialized[$entityName])) {
            $this->load($modelName);
        }

        return isset($this->hasOneThrough[$keyRelation]);
    }

    /**
     * Checks whether a model has a hasManyToMany relation with another model
     */
    public function existsHasManyToMany(string $modelName, string $modelRelation) : bool
    {
        $entityName = strtolower($modelName);

        /**
         * Relationship unique key
         */ 
        $keyRelation = $entityName . "$" . strtolower($modelRelation);

        /**
         * Initialize the model first
         */
        if (!isset($this->initialized[$entityName])) {
            $this->load($modelName);
        }

        return isset($this->hasManyToMany[$keyRelation]);
    }

    /**
     * Returns a relation by its alias
     */
    public function getRelationByAlias(string $modelName, string $alias) : ?RelationInterface
    {
        return  $this->aliases[strtolower($modelName . "$" . $alias)] ?? null;
    }

    /**
     * Merge two arrays of find parameters
     */
    final protected function _mergeFindParameters($findParamsOne, $findParamsTwo) : array
    {
        $findParams = [];

        if (is_string($findParamsOne)) { 
            $findParamsOne = [
                "conditions" => $findParamsOne
            ];
        }

        if (is_string($findParamsTwo)) { $findParamsTwo = [
                "conditions" => $findParamsTwo
            ];
        }

        if (is_array($findParamsOne))  {
            foreach ($findParamsOne as $key => $value) {
                if ($key === 0 || $key === "conditions") {
                    if (!isset($findParams[0])) { 
                        $findParams[0] = $key;
                    } else { 
                        $findParams[0] = "(" . $findParams[0] . ") AND (" . $value . ")";
                    }
                } 
                else { 
                    $findParams[$key] = $value;
                }
            }
        }

        if (is_array($findParamsTwo))  {
            foreach ($findParamsTwo as $key => $value) {
                if ($key === 0 || $key === "conditions") {
                    if (!isset($findParams[0])) { $findParams[0] = $value;
                    } 
                    else { 
                        $findParams[0] = "(" . $findParams[0] . ") AND (" . $value . ")";
                    }
                } 
                elseif ($key === "bind" || $key === "bindTypes") {
                    if (is_array($value)) {
                        if (!isset($findParams[$key])) { 
                            $findParams[$key] = $value;
                        } 
                        else { 
                            $findParams[$key] = array_merge( $findParams[$key],$value);
                        }
                    }
                } 
                else { 
                    $findParams[$key] = $value;
                }
            }
        }

        return $findParams;
    }

    /**
     * Helper method to query records based on a relation definition
     *
     * @return \Phiz\Mvc\Model\Resultset\Simple|Phiz\Mvc\Model\Resultset\Simple|int|false
     */
    public function getRelationRecords(RelationInterface $relation, ModelInterface $record, 
        $parameters = null, string $method = null)
    {

        /**
         * Re-use bound parameters
         */ $placeholders = [];

        /**
         * Returns parameters that must be always used when the related records
         * are obtained
         */ $extraParameters =$relation->getParams();

        /**
         * Perform the query on the referenced model
         */ $referencedModel =$relation->getReferencedModel();

        /**
         * Check if the relation is direct or through an intermediate model
         */
        if ($relation->isThrough()) { 
            $conditions = []; 
            $intermediateModel =$relation->getIntermediateModel();
            $intermediateFields =$relation->getIntermediateFields();

            /**
             * Appends conditions created from the fields defined in the
             * relation
             */ 
            $fields =$relation->getFields();

            if (is_array($fields)) {
                throw new Exception("Not supported");
            } 
            $conditions[] = "[" . $intermediateModel . "].[" . $intermediateFields . "] = :APR0:";
            $placeholders["APR0"] =$record->readAttribute($fields); $joinConditions = [];

            /**
             * Create the join conditions
             */ $intermediateFields =$relation->getIntermediateReferencedFields();

            if (is_array($intermediateFields)) {
                throw new Exception("Not supported");
            } $joinConditions[] = "[" . $intermediateModel . "].[" . $intermediateFields . "] = [" . $referencedModel . "].[" .$relation->getReferencedFields() . "]";

            /**
             * We don't trust the user or the database so we use bound parameters
             * Create a query builder
             */ $builder = $this->createBuilder(
                $this->_mergeFindParameters($extraParameters, $parameters)
            );

           $builder->from($referencedModel);

           $builder->innerJoin(
                $intermediateModel,
                join(" AND ", $joinConditions)
            );

           $builder->andWhere(
                join(" AND ", $conditions),
                $placeholders
            );

            if ($method === "count") {
               $builder->columns("COUNT(*) AS rowcount"); $rows = $builder->getQuery()->execute(); $firstRow =$rows->getFirst();

                return (int)$firstRow->readAttribute("rowcount");
            }

            /**
             * Get the query
             */ 
            $query = $builder->getQuery();

            switch ($relation->getType()) {
                case Relation::HAS_MANY_THROUGH:
                    return$query->execute();

                case Relation::HAS_ONE_THROUGH:
                    return$query->setUniqueRow($true)->execute();

                default:
                    throw new Exception("Unknown relation type");
            }
        } 
        $conditions = [];

        /**
         * Appends conditions created from the fields defined in the relation
         */ $fields =$relation->getFields();

        /**
         * Compound relation
         */ $referencedFields =$relation->getReferencedFields();

        if (!is_array($fields)) { 
            $conditions[] = "[". $referencedFields . "] = :APR0:";
            $placeholders["APR0"] = $record->readAttribute($fields);
        } 
        else {
            foreach($relation->getFields() as $refPosition => $field)  { 
                $conditions[] = "[". $referencedFields[$refPosition] . "] = :APR" 
                    . $refPosition . ":";
                $placeholders["APR" . $refPosition] = $record->readAttribute($field);
            }
        }

        /**
         * We don't trust the user or data in the database so we use bound parameters
         * Create a valid params array to pass to the find/findFirst method
         */ 
        $findParams = [
            join(" AND ", $conditions) . "bind" => $placeholders, 
            "di" => $record->{"getDi"}()]; 
            $findArguments = $this->_mergeFindParameters($findParams, $parameters);

        if (is_array($extraParameters)) { 
            $findParams = $this->_mergeFindParameters($extraParameters, $findArguments );
        } 
        else { 
            $findParams = $findArguments;
        }

        /**
         * Check the right method to get the data
         */
        if ($method === null) {
            switch ($relation->getType()) {
                case Relation::BELONGS_TO:
                case Relation::HAS_ONE: $retrieveMethod = "findFirst";
                    break;

                case Relation::HAS_MANY: $retrieveMethod = "find";
                    break;

                default:
                    throw new Exception("Unknown relation type");
            }
        } 
        else { 
            $retrieveMethod = $method;
        }

        /**
         * Find first results could be reusable
         */ $reusable = (bool) $relation->isReusable();

        if ($reusable) { 
            $uniqueKey = unique_key($referencedModel, [$findParams, $retrieveMethod]);
            $records = $this->getReusableRecords($referencedModel, $uniqueKey);

            if (is_array($records) || is_object($records)){
                return $records;
            }
        } $arguments = [$findParams];

        /**
         * Load the referenced model
         * Call the function in the model
         */ $records = call_user_func_array(
            [
                $this->load($referencedModel),
                $retrieveMethod
            ],
            $arguments
        );

        /**
         * Store the result in the cache if it's reusable
         */
        if ($reusable) {
            $this->setReusableRecords($referencedModel, $uniqueKey, $records);
        }

        return $records;
    }

    /**
     * Returns a reusable object from the internal list
     */
    public function getReusableRecords(string $modelName, string $key)
    {
        $records = $this->reusable[$key] ?? null;
    }

    /**
     * Stores a reusable record in the internal list
     */
    public function setReusableRecords(string $modelName, string $key, $records) : void
    { 
        $this->reusable[$key] = $records;
    }

    /**
     * Clears the internal reusable list
     */
    public function clearReusableObjects() : void
    { 
        $this->reusable = [];
    }

    /**
     * Gets belongsTo related records from a model
     */
    public function getBelongsToRecords(string $modelName, string $modelRelation, 
        ModelInterface $record, $parameters = null, string $method = null)
        : ?ResultsetInterface
    {
        /**
         * Check if there is a relation between them
         */ 
        $keyRelation = strtolower($modelName) . "$" . strtolower($modelRelation);
        $relations = $this->hasMany[$keyRelation] ?? null;

        if (empty($relations)) {
            return null;
        }

        /**
         * "relations" is an array with all the belongsTo relationships to that model
         * Perform the query
         */
        return $this->getRelationRecords(
            $relations[0],
            $record,
            $parameters,
            $method
        );
    }

    /**
     * Gets hasMany related records from a model
     */
    public function getHasManyRecords(string $modelName, string $modelRelation, 
        ModelInterface $record, $parameters = null, string $method = null)
        : ?ResultsetInterface
    {
        /**
         * Check if there is a relation between them
         */ 
        $keyRelation = strtolower($modelName) . "$" . strtolower($modelRelation);
        $relations =  $this->hasMany[$keyRelation] ?? false;
        if (empty($relations)) {
            return null;
        }

        /**
         * "relations" is an array with all the hasMany relationships to that model
         * Perform the query
         */
        return $this->getRelationRecords(
            $relations[0],
            $record,
            $parameters,
            $method
        );
    }

    /**
     * Gets belongsTo related records from a model
     */
    public function getHasOneRecords(string $modelName, string $modelRelation, 
        ModelInterface $record, $parameters = null, 
        string $method = null): ?ModelInterface
    {
        /**
         * Check if there is a relation between them
         */ 
        $keyRelation = strtolower($modelName) . "$" . strtolower($modelRelation);
        $relations = $this->hasOne[$keyRelation] ?? false;
        if (empty($relations))  {
            return null;
        }

        /**
         * "relations" is an array with all the belongsTo relationships to that model
         * Perform the query
         */
        return $this->getRelationRecords(
            $relations[0],
            $record,
            $parameters,
            $method
        );
    }

    /**
     * Gets all the belongsTo relations defined in a model
     *
     *```php
     * $relations = $modelsManager->getBelongsTo(
     *     new Robots()
     * );
     *```
     */
    public function getBelongsTo(ModelInterface $model) :  array
    {
        return $this->belongsToSingle[\get_class_lower($model)] ?? [];
    }

    /**
     * Gets hasMany relations defined on a model
     */
    public function getHasMany(ModelInterface $model) :  array
    {
        return $this->hasManySingle[\get_class_lower($model)] ?? [];
    }

    /**
     * Gets hasOne relations defined on a model
     */
    public function getHasOne(ModelInterface $model) : array
    {
        return $this->hasOneSingle[\get_class_lower($model)] ?? [];
    }

    /**
     * Gets hasOneThrough relations defined on a model
     */
    public function getHasOneThrough(ModelInterface $model) :  array
    {
        return $this->hasOneThroughSingle[\get_class_lower($model)] ?? [];
    }

    /**
     * Gets hasManyToMany relations defined on a model
     */
    public function getHasManyToMany(ModelInterface $model) :  array
    {
        return $this->hasManyToManySingle[\get_class_lower($model)] ?? [];
    }

    /**
     * Gets hasOne relations defined on a model
     */
    public function getHasOneAndHasMany(ModelInterface $model) : array
    {
        return array_merge(
            $this->getHasOne($model),
            $this->getHasMany($model)
        );
    }

    /**
     * Query all the relationships defined on a model
     */
    public function getRelations(string $modelName) : array
    {
        $entityName = strtolower($modelName);
        $allRelations = [];

        /**
         * Get belongs-to relations
         */
        $relations = $this->belongsToSingle[$entityName] ?? null;
		if ($relations !== null) {
            foreach($relations as $relation) {
                $allRelations[] = $relation;
            }
        }

        /**
         * Get has-many relations
         */
        $relations = $this->hasManySingle[$entityName] ?? null;
		if ($relations !== null) {
            foreach($relations as $relation) {
                $allRelations[] = $relation;
            }
        }

        /**
         * Get has-one relations
         */
        $relations = $this->hasOneSingle[$entityName] ?? null;
		if ($relations !== null) {
            foreach($relations as $relation) {
                $allRelations[] = $relation;
            }
        }

        /**
         * Get has-one-through relations
         */
        $relations = $this->hasOneThroughSingle[$entityName] ?? null;
		if ($relations !== null) {
            foreach($relations as $relation) {
                $allRelations[] = $relation;
            }
        }

        /**
         * Get many-to-many relations
         */
        $relations = $this->hasManyToManySingle[$entityName] ?? null;
		if ($relations !== null) {
            foreach($relations as $relation) {
                $allRelations[] = $relation;
            }
        }

        return allRelations;
    }

    /**
     * Query the first relationship defined between two models
     */
    public function getRelationsBetween(string $first, string $second) : ?array
    {
        $keyRelation = strtolower($first) . "$" . strtolower($second);

        /**
         * Check if it's a belongs-to relationship
         */
        $relations = $this->belongsTo[$keyRelation] ?? null;
		if ($relations !== null) {
            return $relations;
        }

        /**
         * Check if it's a has-many relationship
         */
        $relations = $this->hasMany[$keyRelation] ?? null;
		if ($relations !== null) {
            return $relations;
        }

        /**
         * Check whether it's a has-one relationship
         */
        $relations = $this->hasOne[$keyRelation] ?? null;
		if ($relations !== null) {
            return $relations;
        }

        /**
         * Check whether it's a has-one-through relationship
         */
        $relations = $this->hasOneThrough[$keyRelation] ?? null;
		if ($relations !== null) {
            return $relations;
        }

        /**
        * Check whether it's a has-many-to-many relationship
        */
        $relations = $this->hasManyToMany[$keyRelation] ?? null;
		if ($relations !== null) {
            return $relations;
        }

        return false;
    }

    /**
     * Creates a Phiz\Mvc\Model\Query without execute it
     */
    public function createQuery(string $phql) : QueryInterface
    {
        $container = $this->container;

        if (!is_object($container)) {
            throw new Exception(
                Exception::containerServiceNotFound(
                    "the services related to the ORM"
                )
            );
        }

        /**
         * Create a query
         */ 
        $query = $container->get( "Phiz\\Mvc\\Model\\SqlQuery",[$phql, $container] ); 
        $this->lastQuery = $query;

        return $query;
    }

    /**
     * Creates a Phiz\Mvc\Model\Query and execute it
     *
     * ```php
     * $model = new Robots();
     * $manager = $model->getModelsManager();
     *
     * // \Phiz\Mvc\Model\Resultset\Simple
     * $manager->executeQuery('SELECT * FROM Robots');
     *
     * // \Phiz\Mvc\Model\Resultset\Complex
     * $manager->executeQuery('SELECT COUNT($type) FROM Robots GROUP BY type');
     *
     * // \Phiz\Mvc\Model\Query\StatusInterface
     * $manager->executeQuery('INSERT INTO Robots (id) VALUES (1)');
     *
     * // \Phiz\Mvc\Model\Query\StatusInterface
     * $manager->executeQuery('UPDATE Robots SET id = 0 WHERE id = :id:', ['id' => 1]);
     *
     * // \Phiz\Mvc\Model\Query\StatusInterface
     * $manager->executeQuery('DELETE FROM Robots WHERE id = :id:', ['id' => 1]);
     * ```
     *
     * @param array|null $placeholders
     * @param array|null $types
     * @return ResultsetInterface|StatusInterface
     */
    public function executeQuery(string $phql, $placeholders = null, $types = null) : mixed
    {
        $query = $this->createQuery($phql);

        if (is_array($placeholders)) {
            $query->setBindParams($placeholders);
        }

        if (is_array($types)) {
            $query->setBindTypes($types);
        }

        /**
         * Execute the query
         */
        return $query->execute();
    }

    /**
     * Creates a Phiz\Mvc\Model\Query\Builder
     */
    public function createBuilder($params = null) : BuilderInterface
    {
        $container = $this->container;

        if (!is_object($container)) {
            throw new Exception(
                Exception::containerServiceNotFound(
                    "the services related to the ORM"
                )
            );
        }

        /**
         * Gets Builder instance from DI container
         */
        return $container->get(
            "Phiz\\Mvc\\Model\\Query\\SqlBuilder",
            [ $params, $container ]
        );
    }

    /**
     * Returns the last query created or executed in the models manager
     */
    public function getLastQuery() : QueryInterface
    {
        return $this->lastQuery;
    }

    /**
     * Destroys the current PHQL cache
     */
    public function __destruct()
    {
        //TODO: What is stored and When is this needed?
        //phalcon_orm_destroy_cache();

        Query::clean();
    }
}
