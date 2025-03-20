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

use Phalcon\Db\Adapter\AdapterInterface;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Di\Traits\InjectionAwareTrait;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\Exception as EventsException;
use Phalcon\Events\ManagerInterface as EventsManagerInterface;
use Phalcon\Events\Traits\EventsAwareTrait;
use Phalcon\Mvc\Model\Query\BuilderInterface;
use Phalcon\Mvc\Model\Query\StatusInterface;
use Phalcon\Mvc\Model\Resultset\Simple;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Parsers\Parser;
use Phalcon\Support\Settings;
use Phalcon\Traits\Helper\Str\UncamelizeTrait;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

use function array_key_exists;
use function array_merge;
use function array_pop;
use function call_user_func_array;
use function class_exists;
use function explode;
use function get_class;
use function implode;
use function is_array;
use function is_object;
use function is_string;
use function method_exists;
use function strtolower;

/**
 * This components controls the initialization of models, keeping record of
 * relations between the different models of the application.
 *
 * A ModelsManager is injected to a model via a Dependency Injector/Services
 * Container such as Phalcon\Di\Di.
 *
 * ```php
 * use Phalcon\Di\Di;
 * use Phalcon\Mvc\Model\Manager as ModelsManager;
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
 * $invoice = new Invoices($di);
 * ```
 */
class Manager implements ManagerInterface, InjectionAwareInterface, EventsAwareInterface
{
    use EventsAwareTrait;
    use InjectionAwareTrait;
    use UncamelizeTrait;

    /**
     * @var array
     */
    protected array $aliases = [];

    /**
     * Models' behaviors
     *
     * @var array
     */
    protected array $behaviors = [];

    /**
     * Belongs to relations
     *
     * @var array
     */
    protected array $belongsTo = [];

    /**
     * All the relationships by model
     *
     * @var array
     */
    protected array $belongsToSingle = [];

    /**
     * @var BuilderInterface|null
     */
    protected BuilderInterface | null $builder = null;

    /**
     * @var array
     */
    protected array $customEventsManager = [];

    /**
     * Does the model use dynamic update, instead of updating all rows?
     *
     * @var array
     */
    protected array $dynamicUpdate = [];

    /**
     * Has many relations
     *
     * @var array
     */
    protected array $hasMany = [];

    /**
     * Has many relations by model
     *
     * @var array
     */
    protected array $hasManySingle = [];

    /**
     * Has many-Through relations
     *
     * @var array
     */
    protected array $hasManyToMany = [];

    /**
     * Has many-Through relations by model
     *
     * @var array
     */
    protected array $hasManyToManySingle = [];

    /**
     * Has one relations
     *
     * @var array
     */
    protected array $hasOne = [];

    /**
     * Has one relations by model
     *
     * @var array
     */
    protected array $hasOneSingle = [];

    /**
     * Has one through relations
     *
     * @var array
     */
    protected array $hasOneThrough = [];

    /**
     * Has one through relations by model
     *
     * @var array
     */
    protected array $hasOneThroughSingle = [];

    /**
     * Mark initialized models
     *
     * @var array
     */
    protected array $initialized = [];

    /**
     * @var array
     */
    protected array $keepSnapshots = [];

    /**
     * Last model initialized
     *
     * @var ModelInterface|null
     */
    protected ModelInterface | null $lastInitialized = null;

    /**
     * Last query created/executed
     *
     * @var QueryInterface|null
     */
    protected QueryInterface | null $lastQuery = null;

    /**
     * @var array
     */
    protected array $modelVisibility = [];

    /**
     * @var string
     */
    protected string $prefix = "";

    /**
     * @var array
     */
    protected array $readConnectionServices = [];
    /**
     * Stores a list of reusable instances
     *
     * @var array
     */
    protected array $reusable = [];
    /**
     * @var array
     */
    protected array $schemas = [];
    /**
     * @var array
     */
    protected array $sources = [];
    /**
     * @var array
     */
    protected array $writeConnectionServices = [];

    /**
     * Destroys the current PHQL cache
     */
    public function __destruct()
    {
        Parser::ormDestroyCache();

        Query::clean();
    }

    /**
     * Binds a behavior to a model
     *
     * @param ModelInterface    $model
     * @param BehaviorInterface $behavior
     */
    public function addBehavior(
        ModelInterface $model,
        BehaviorInterface $behavior
    ): void {
        $entityName = mb_strtolower(get_class($model));

        if (!isset($this->behaviors[$entityName])) {
            $this->behaviors[$entityName] = [];
        }

        /**
         * Append the behavior to the list of behaviors
         */
        $this->behaviors[$entityName][] = $behavior;
    }

    /**
     * Setup a relation reverse many to one between two models
     *
     * @param ModelInterface $model
     * @param mixed          $fields
     * @param string         $referencedModel
     * @param mixed          $referencedFields
     * @param array          $options
     *
     * @return RelationInterface
     */
    public function addBelongsTo(
        ModelInterface $model,
        mixed $fields,
        string $referencedModel,
        mixed $referencedFields,
        array $options = []
    ): RelationInterface {
        $entityName       = mb_strtolower(get_class($model));
        $referencedEntity = mb_strtolower($referencedModel);

        $keyRelation = $entityName . "$" . $referencedEntity;

        $relations = $this->belongsTo[$keyRelation] ?? [];

        /**
         * Check if the number of fields are the same
         */
        if (is_array($referencedFields) && count($fields) != count($referencedFields)) {
            throw new Exception(
                "Number of referenced fields are not the same"
            );
        }

        /**
         * Create a relationship instance
         */
        $relation = new Relation(
            Relation::BELONGS_TO,
            $referencedModel,
            $fields,
            $referencedFields,
            $options
        );

        /**
         * Check an alias for the relation
         */
        if (isset($options["alias"])) {
            $alias = $options["alias"];
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
        $relations[]                                    = $relation;
        $this->aliases[$entityName . "$" . $lowerAlias] = $relation;
        $this->belongsTo[$keyRelation]                  = $relations;

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
     * @param ModelInterface $model
     * @param mixed          $fields
     * @param string         $referencedModel
     * @param mixed          $referencedFields
     * @param array          $options
     *
     * @return RelationInterface
     */
    public function addHasMany(
        ModelInterface $model,
        mixed $fields,
        string $referencedModel,
        mixed $referencedFields,
        array $options = []
    ): RelationInterface {
        $entityName       = mb_strtolower(get_class($model));
        $referencedEntity = mb_strtolower($referencedModel);
        $keyRelation      = $entityName . "$" . $referencedEntity;

        $relations = $this->hasMany[$keyRelation] ?? [];

        /**
         * Check if the number of fields are the same
         */
        if (is_array($referencedFields) && count($fields) != count($referencedFields)) {
            throw new Exception(
                "Number of referenced fields are not the same"
            );
        }

        /**
         * Create a relationship instance
         */
        $relation = new Relation(
            Relation::HAS_MANY,
            $referencedModel,
            $fields,
            $referencedFields,
            $options
        );

        /**
         * Check an alias for the relation
         */
        if (isset($options["alias"])) {
            $alias = $options["alias"];
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
        $relations[]                                    = $relation;
        $this->aliases[$entityName . "$" . $lowerAlias] = $relation;
        $this->hasMany[$keyRelation]                    = $relations;

        /**
         * Get existing relations by model
         */
        $singleRelations = $this->hasManySingle[$entityName] ?? [];

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
     * @param ModelInterface $model
     * @param mixed          $fields
     * @param string         $intermediateModel
     * @param mixed          $intermediateFields
     * @param mixed          $intermediateReferencedFields
     * @param string         $referencedModel
     * @param mixed          $referencedFields
     * @param array          $options
     *
     * @return RelationInterface
     */
    public function addHasManyToMany(
        ModelInterface $model,
        mixed $fields,
        string $intermediateModel,
        mixed $intermediateFields,
        mixed $intermediateReferencedFields,
        string $referencedModel,
        mixed $referencedFields,
        array $options = []
    ): RelationInterface {
        $entityName         = mb_strtolower(get_class($model));
        $intermediateEntity = mb_strtolower($intermediateModel);
        $referencedEntity   = mb_strtolower($referencedModel);
        $keyRelation        = $entityName . "$" . $referencedEntity;

        $hasManyToMany = $this->hasManyToMany;

        $relations = $this->hasManyToMany[$keyRelation] ?? [];

        /**
         * Check if the number of fields are the same from the model to the
         * intermediate model
         */
        if (is_array($intermediateFields) && count($fields) != count($intermediateFields)) {
            throw new Exception(
                "Number of referenced fields are not the same"
            );
        }

        /**
         * Check if the number of fields are the same from the intermediate
         * model to the referenced model
         */
        if (is_array($intermediateReferencedFields) && count($fields) != count($intermediateFields)) {
            throw new Exception(
                "Number of referenced fields are not the same"
            );
        }

        /**
         * Create a relationship instance
         */
        $relation = new Relation(
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
        if (isset($options["alias"])) {
            $alias = $options["alias"];
            if (!is_string($alias)) {
                throw new Exception("Relation alias must be a string");
            }

            $lowerAlias = strtolower($alias);
        } else {
            $lowerAlias = $referencedEntity;
        }

        /**
         * Append a new relationship
         */
        $relations[] = $relation;

        /**
         * Update the global alias
         */
        $this->aliases[$entityName . "$" . $lowerAlias] = $relation;

        /**
         * Update the relations
         */
        $this->hasManyToMany[$keyRelation] = $relations;

        /**
         * Get existing relations by model
         */
        $singleRelations = $this->hasManyToManySingle[$entityName] ?? [];

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
     * Setup a 1-1 relation between two models
     *
     * @param ModelInterface $model
     * @param mixed          $fields
     * @param string         $referencedModel
     * @param mixed          $referencedFields
     * @param array          $options
     *
     * @return RelationInterface
     */
    public function addHasOne(
        ModelInterface $model,
        mixed $fields,
        string $referencedModel,
        mixed $referencedFields,
        array $options = []
    ): RelationInterface {
        $entityName       = mb_strtolower(get_class($model));
        $referencedEntity = mb_strtolower($referencedModel);
        $keyRelation      = $entityName . "$" . $referencedEntity;

        $relations = $this->hasOne[$keyRelation] ?? [];

        /**
         * Check if the number of fields are the same
         */
        if (is_array($referencedFields) && count($fields) != count($referencedFields)) {
            throw new Exception(
                "Number of referenced fields are not the same"
            );
        }

        /**
         * Create a relationship instance
         */
        $relation = new Relation(
            Relation::HAS_ONE,
            $referencedModel,
            $fields,
            $referencedFields,
            $options
        );

        /**
         * Check an alias for the relation
         */
        if (isset($options["alias"])) {
            $alias = $options["alias"];
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
        $relations[]                                    = $relation;
        $this->aliases[$entityName . "$" . $lowerAlias] = $relation;
        $this->hasOne[$keyRelation]                     = $relations;

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
     * @param ModelInterface $model
     * @param mixed          $fields
     * @param string         $intermediateModel
     * @param mixed          $intermediateFields
     * @param mixed          $intermediateReferencedFields
     * @param string         $referencedModel
     * @param mixed          $referencedFields
     * @param array          $options
     *
     * @return RelationInterface
     */
    public function addHasOneThrough(
        ModelInterface $model,
        mixed $fields,
        string $intermediateModel,
        mixed $intermediateFields,
        mixed $intermediateReferencedFields,
        string $referencedModel,
        mixed $referencedFields,
        array $options = []
    ): RelationInterface {
        $entityName         = mb_strtolower(get_class($model));
        $intermediateEntity = mb_strtolower($intermediateModel);
        $referencedEntity   = mb_strtolower($referencedModel);
        $keyRelation        = $entityName . "$" . $referencedEntity;

        $hasOneThrough = $this->hasOneThrough;

        $relations = $this->hasOneThrough[$keyRelation] ?? [];

        /**
         * Check if the number of fields are the same from the model to the
         * intermediate model
         */
        if (is_array($intermediateFields) && count($fields) != count($intermediateFields)) {
            throw new Exception(
                "Number of referenced fields are not the same"
            );
        }

        /**
         * Check if the number of fields are the same from the intermediate
         * model to the referenced model
         */
        if (is_array($intermediateReferencedFields) && count($fields) != count($intermediateFields)) {
            throw new Exception(
                "Number of referenced fields are not the same"
            );
        }

        /**
         * Create a relationship instance
         */
        $relation = new Relation(
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
        if (isset($options["alias"])) {
            $alias = $options["alias"];
            if (!is_string($alias)) {
                throw new Exception("Relation alias must be a string");
            }

            $lowerAlias = strtolower($alias);
        } else {
            $lowerAlias = $referencedEntity;
        }

        /**
         * Append a new relationship
         */
        $relations[] = $relation;

        /**
         * Update the global alias
         */
        $this->aliases[$entityName . "$" . $lowerAlias] = $relation;

        /**
         * Update the relations
         */
        $this->hasOneThrough[$keyRelation] = $relations;

        /**
         * Get existing relations by model
         */
        $singleRelations = $this->hasOneThroughSingle[$entityName] ?? [];

        /**
         * Append a new relationship
         */
        $singleRelations[] = $relation;

        /**
         * Update relations by model
         */
        $this->hasOneThroughSingle[$entityName] = $singleRelations;

        return $relation;
    }

    /**
     * Clears the internal reusable list
     */
    public function clearReusableObjects(): void
    {
        $this->reusable = [];
    }

    /**
     * Creates a Phalcon\Mvc\Model\Query\Builder
     *
     * @param array|string|null $params
     *
     * @return BuilderInterface
     * @throws Exception
     */
    public function createBuilder(array | string | null $params = null): BuilderInterface
    {
        $this->checkContainer(
            Exception::class,
            'the services related to the ORM'
        );

        /**
         * Gets Builder instance from DI container
         */
        $this->builder = $this->container->get(
            "Phalcon\\Mvc\\Model\\Query\\Builder",
            [
                $params,
                $this->container,
            ]
        );

        return $this->builder;
    }

    /**
     * Creates a Phalcon\Mvc\Model\Query without execute it
     *
     * @param string $phql
     *
     * @return QueryInterface
     * @throws Exception
     */
    public function createQuery(string $phql): QueryInterface
    {
        $this->checkContainer(
            Exception::class,
            'the services related to the ORM'
        );

        /**
         * Create a query
         */
        $query = $this->container->get(
            "Phalcon\\Mvc\\Model\\Query",
            [$phql, $this->container]
        );

        $this->lastQuery = $query;

        return $query;
    }

    /**
     * Creates a Phalcon\Mvc\Model\Query and execute it
     *
     * ```php
     * $model = new Robots();
     * $manager = $model->getModelsManager();
     *
     * // \Phalcon\Mvc\Model\Resultset\Simple
     * $manager->executeQuery('SELECT * FROM Robots');
     *
     * // \Phalcon\Mvc\Model\Resultset\Complex
     * $manager->executeQuery('SELECT COUNT(type) FROM Robots GROUP BY type');
     *
     * // \Phalcon\Mvc\Model\Query\StatusInterface
     * $manager->executeQuery('INSERT INTO Robots (id) VALUES (1)');
     *
     * // \Phalcon\Mvc\Model\Query\StatusInterface
     * $manager->executeQuery('UPDATE Robots SET id = 0 WHERE id = :id:', ['id' => 1]);
     *
     * // \Phalcon\Mvc\Model\Query\StatusInterface
     * $manager->executeQuery('DELETE FROM Robots WHERE id = :id:', ['id' => 1]);
     * ```
     *
     * @param string     $phql
     * @param array|null $placeholders
     * @param array|null $types
     *
     * @return ResultsetInterface|StatusInterface
     * @throws Exception
     */
    public function executeQuery(
        string $phql,
        array | null $placeholders = null,
        array | null $types = null
    ): mixed {
        $query = $this->createQuery($phql);

        if (null !== $placeholders) {
            $query->setBindParams($placeholders);
        }

        if (null !== $types) {
            $query->setBindTypes($types);
        }

        /**
         * Execute the query
         */
        return $query->execute();
    }

    /**
     * Gets all the belongsTo relations defined in a model
     *
     *```php
     * $relations = $modelsManager->getBelongsTo(
     *     new Robots()
     * );
     *```
     *
     * @param ModelInterface $model
     *
     * @return RelationInterface[] | array
     */
    public function getBelongsTo(ModelInterface $model): array
    {
        return $this->belongsToSingle[mb_strtolower(get_class($model))] ?? [];
    }

    /**
     * Gets belongsTo related records from a model
     *
     * @param string         $modelName
     * @param string         $modelRelation
     * @param ModelInterface $record
     * @param mixed|null     $parameters
     * @param string|null    $method
     *
     * @return ResultsetInterface|bool
     * @throws Exception
     */
    public function getBelongsToRecords(
        string $modelName,
        string $modelRelation,
        ModelInterface $record,
        mixed $parameters = null,
        string | null $method = null
    ): ResultsetInterface | bool {
        /**
         * Check if there is a relation between them
         */
        $keyRelation = strtolower($modelName) . "$" . strtolower($modelRelation);

        if (!isset($this->hasMany[$keyRelation])) {
            return false;
        }

        $relations = $this->hasMany[$keyRelation];

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
     * Returns the newly created Phalcon\Mvc\Model\Query\Builder or null
     *
     * @return BuilderInterface | null
     */
    public function getBuilder(): BuilderInterface | null
    {
        return $this->builder;
    }

    /**
     * Returns the connection service name used to read or write data related to
     * a model depending on the connection services
     *
     * @param ModelInterface $model
     * @param array          $connectionServices
     *
     * @return string
     */
    public function getConnectionService(
        ModelInterface $model,
        array $connectionServices
    ): string {
        return $connectionServices[mb_strtolower(get_class($model))] ?? 'db';
    }

    /**
     * Returns a custom events manager related to a model or null if there is
     * no related events manager
     *
     * @param ModelInterface $model
     *
     * @return EventsManagerInterface | null
     */
    public function getCustomEventsManager(
        ModelInterface $model
    ): EventsManagerInterface | null {
        return $this->customEventsManager[mb_strtolower(get_class($model))] ?? null;
    }

    /**
     * Gets hasMany relations defined on a model
     *
     * @param ModelInterface $model
     *
     * @return array|RelationInterface[]
     */
    public function getHasMany(ModelInterface $model): array
    {
        return $this->hasManySingle[mb_strtolower(get_class($model))] ?? [];
    }

    /**
     * Gets hasMany related records from a model
     *
     * @param string         $modelName
     * @param string         $modelRelation
     * @param ModelInterface $record
     * @param mixed|null     $parameters
     * @param string|null    $method
     *
     * @return ResultsetInterface|bool
     * @throws Exception
     */
    public function getHasManyRecords(
        string $modelName,
        string $modelRelation,
        ModelInterface $record,
        mixed $parameters = null,
        string | null $method = null
    ): ResultsetInterface | bool {
        /**
         * Check if there is a relation between them
         */
        $keyRelation = strtolower($modelName) . "$" . strtolower($modelRelation);

        if (!isset($this->hasMany[$keyRelation])) {
            return false;
        }

        $relations = $this->hasMany[$keyRelation];

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
     * Gets hasManyToMany relations defined on a model
     *
     * @param ModelInterface $model
     *
     * @return array|RelationInterface[]
     */
    public function getHasManyToMany(ModelInterface $model): array
    {
        return $this->hasManyToManySingle[mb_strtolower(get_class($model))] ?? [];
    }

    /**
     * Gets hasOne relations defined on a model
     *
     * @param ModelInterface $model
     *
     * @return array|RelationInterface[]
     */
    public function getHasOne(ModelInterface $model): array
    {
        return $this->hasOneSingle[mb_strtolower(get_class($model))] ?? [];
    }

    /**
     * Gets hasOne relations defined on a model
     *
     * @param ModelInterface $model
     *
     * @return array|RelationInterface[]
     */
    public function getHasOneAndHasMany(ModelInterface $model): array
    {
        return array_merge(
            $this->getHasOne($model),
            $this->getHasMany($model)
        );
    }

    /**
     * Gets belongsTo related records from a model
     *
     * @param string         $modelName
     * @param string         $modelRelation
     * @param ModelInterface $record
     * @param mixed|null     $parameters
     * @param string|null    $method
     *
     * @return ModelInterface|bool
     * @throws Exception
     */
    public function getHasOneRecords(
        string $modelName,
        string $modelRelation,
        ModelInterface $record,
        mixed $parameters = null,
        string | null $method = null
    ): ModelInterface | bool {
        /**
         * Check if there is a relation between them
         */
        $keyRelation = strtolower($modelName) . "$" . strtolower($modelRelation);

        if (!isset($this->hasOne[$keyRelation])) {
            return false;
        }

        $relations = $this->hasOne[$keyRelation];

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
     * Gets hasOneThrough relations defined on a model
     *
     * @param ModelInterface $model
     *
     * @return array|RelationInterface[]
     */
    public function getHasOneThrough(ModelInterface $model): array
    {
        return $this->hasOneThroughSingle[mb_strtolower(get_class($model))] ?? [];
    }

    /**
     * Get last initialized model
     *
     * @return ModelInterface
     */
    public function getLastInitialized(): ModelInterface
    {
        return $this->lastInitialized;
    }

    /**
     * Returns the last query created or executed in the models manager
     *
     * @return QueryInterface
     */
    public function getLastQuery(): QueryInterface
    {
        return $this->lastQuery;
    }

    /**
     * Returns the prefix for all model sources.
     *
     * @return string
     */
    public function getModelPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Returns the mapped schema for a model
     *
     * @param ModelInterface $model
     *
     * @return string|null
     */
    public function getModelSchema(ModelInterface $model): string | null
    {
        return $this->schemas[mb_strtolower(get_class($model))] ?? null;
    }

    /**
     * Returns the mapped source for a model
     *
     * @param ModelInterface $model
     *
     * @return string
     */
    public function getModelSource(ModelInterface $model): string
    {
        $entityName = mb_strtolower(get_class($model));
        $modelArray = explode("\\", get_class($model));

        // Extract the real class name from the namespaced class
        $modelName = array_pop($modelArray);

        // Extract the namespace from the namespaced class
        $namespaceName = implode("\\", $modelArray);

        if (!isset($this->sources[$entityName])) {
            $this->setModelSource(
                $model,
                $this->toUncamelize($modelName)
            );
        }

        return $this->prefix . $this->sources[$entityName];
    }

    /**
     * Returns the connection to read data related to a model
     *
     * @param ModelInterface $model
     *
     * @return AdapterInterface
     */
    public function getReadConnection(ModelInterface $model): AdapterInterface
    {
        return $this->getConnection($model, $this->readConnectionServices);
    }

    /**
     * Returns the connection service name used to read data related to a model
     *
     * @param ModelInterface $model
     *
     * @return string
     */
    public function getReadConnectionService(ModelInterface $model): string
    {
        return $this->getConnectionService(
            $model,
            $this->readConnectionServices
        );
    }

    /**
     * Returns a relation by its alias
     *
     * @param string $modelName
     * @param string $alias
     *
     * @return RelationInterface|bool
     */
    public function getRelationByAlias(
        string $modelName,
        string $alias
    ): RelationInterface | bool {
        return $this->aliases[strtolower($modelName . "$" . $alias)] ?? false;
    }

    /**
     * Helper method to query records based on a relation definition
     *
     * @param RelationInterface $relation
     * @param ModelInterface    $record
     * @param array|string|null $parameters
     * @param string|null       $method
     *
     * @return Simple|ModelInterface|int|false
     * @throws Exception
     */
    public function getRelationRecords(
        RelationInterface $relation,
        ModelInterface $record,
        array | string | null $parameters = null,
        string | null $method = null
    ): Simple | ModelInterface | int | false {
        /**
         * Re-use bound parameters
         */
        $placeholders = [];

        /**
         * Returns parameters that must be always used when the related records
         * are obtained
         */
        $extraParameters = $relation->getParams();

        /**
         * Perform the query on the referenced model
         */
        $referencedModel = $relation->getReferencedModel();

        /**
         * Check if the relation is direct or through an intermediate model
         */
        if ($relation->isThrough()) {
            $conditions = [];

            $intermediateModel  = $relation->getIntermediateModel();
            $intermediateFields = $relation->getIntermediateFields();

            /**
             * Appends conditions created from the fields defined in the
             * relation
             */
            $fields = $relation->getFields();

            if (is_array($fields)) {
                throw new Exception("Not supported");
            }

            $conditions[]         = "[" . $intermediateModel . "].[" . $intermediateFields . "] = :APR0:";
            $placeholders["APR0"] = $record->readAttribute($fields);
            $joinConditions       = [];

            /**
             * Create the join conditions
             */
            $intermediateFields = $relation->getIntermediateReferencedFields();

            if (is_array($intermediateFields)) {
                throw new Exception("Not supported");
            }

            $joinConditions[] = "[" . $intermediateModel
                . "].[" . $intermediateFields
                . "] = [" . $referencedModel
                . "].[" . $relation->getReferencedFields() . "]";

            /**
             * We don't trust the user or the database so we use bound parameters
             * Create a query builder
             */
            $builder = $this->createBuilder(
                $this->mergeFindParameters($extraParameters, $parameters)
            );

            $builder->from($referencedModel);

            $builder->innerJoin(
                $intermediateModel,
                implode(" AND ", $joinConditions)
            );

            $builder->andWhere(
                implode(" AND ", $conditions),
                $placeholders
            );

            if ($method == "count") {
                $builder->columns("COUNT(*) AS rowcount");

                $rows = $builder->getQuery()->execute();

                $firstRow = $rows->getFirst();

                return (int)$firstRow->readAttribute("rowcount");
            }

            /**
             * Get the query
             */
            $query = $builder->getQuery();

            return match ($relation->getType()) {
                Relation::HAS_MANY_THROUGH => $query->execute(),
                Relation::HAS_ONE_THROUGH  => $query->setUniqueRow(true)->execute(),
                default                    => throw new Exception("Unknown relation type"),
            };
        }

        $conditions = [];

        /**
         * Appends conditions created from the fields defined in the relation
         */
        $fields = $relation->getFields();

        /**
         * Compound relation
         */
        $referencedFields = $relation->getReferencedFields();

        if (!is_array($fields)) {
            $conditions[]         = "[" . $referencedFields . "] = :APR0:";
            $placeholders["APR0"] = $record->readAttribute($fields);
        } else {
            foreach ($relation->getFields() as $refPosition => $field) {
                $conditions[] = "["
                    . $referencedFields[$refPosition]
                    . "] = :APR"
                    . $refPosition . ":";

                $placeholders["APR" . $refPosition] = $record->readAttribute($field);
            }
        }

        /**
         * We don't trust the user or data in the database so we use bound parameters
         * Create a valid params array to pass to the find/findFirst method
         */
        $findParams = [
            implode(" AND ", $conditions),
            "bind" => $placeholders,
            "di"   => $record->getDi(),
        ];

        $findArguments = $this->mergeFindParameters($findParams, $parameters);
        if (is_array($extraParameters)) {
            $findParams = $this->mergeFindParameters(
                $extraParameters,
                $findArguments
            );
        } else {
            $findParams = $findArguments;
        }

        /**
         * Check the right method to get the data
         */
        if (null === $method) {
            $retrieveMethod = match ($relation->getType()) {
                Relation::BELONGS_TO, Relation::HAS_ONE => "findFirst",
                Relation::HAS_MANY                      => "find",
                default                                 => throw new Exception("Unknown relation type"),
            };
        } else {
            $retrieveMethod = $method;
        }

        /**
         * Find first results could be reusable
         */
        $reusable = $relation->isReusable();

        if ($reusable) {
            $uniqueKey = $this->getUniqueKey($referencedModel, [$findParams, $retrieveMethod]);
            $records   = $this->getReusableRecords($referencedModel, $uniqueKey);

            if (is_array($records) || is_object($records)) {
                return $records;
            }
        }

        $arguments = [$findParams];

        /**
         * Load the referenced model
         * Call the function in the model
         */
        $records = call_user_func_array(
            [
                $this->load($referencedModel),
                $retrieveMethod,
            ],
            $arguments
        );

        /**
         * Store the result in the cache if it's reusable
         */
        if ($reusable) {
            $this->setReusableRecords($referencedModel, $uniqueKey, $records);
        }

        return null === $records ? false : $records;
    }

    /**
     * Query all the relationships defined on a model
     *
     * @param string $modelName
     *
     * @return RelationInterface[]
     */
    public function getRelations(string $modelName): array
    {
        $entityName   = strtolower($modelName);
        $allRelations = [];

        /**
         * Get belongs-to relations
         */
        if (isset($this->belongsToSingle[$entityName])) {
            foreach ($this->belongsToSingle[$entityName] as $relation) {
                $allRelations[] = $relation;
            }
        }

        /**
         * Get has-many relations
         */
        if (isset($this->hasManySingle[$entityName])) {
            foreach ($this->hasManySingle[$entityName] as $relation) {
                $allRelations[] = $relation;
            }
        }

        /**
         * Get has-one relations
         */
        if (isset($this->hasOneSingle[$entityName])) {
            foreach ($this->hasOneSingle[$entityName] as $relation) {
                $allRelations[] = $relation;
            }
        }

        /**
         * Get has-one-through relations
         */
        if (isset($this->hasOneThroughSingle[$entityName])) {
            foreach ($this->hasOneThroughSingle[$entityName] as $relation) {
                $allRelations[] = $relation;
            }
        }

        /**
         * Get many-to-many relations
         */
        if (isset($this->hasManyToManySingle[$entityName])) {
            foreach ($this->hasManyToManySingle[$entityName] as $relation) {
                $allRelations[] = $relation;
            }
        }

        return $allRelations;
    }

    /**
     * Query the first relationship defined between two models
     *
     * @param string $first
     * @param string $second
     *
     * @return RelationInterface[]|bool
     */
    public function getRelationsBetween(string $first, string $second): array | bool
    {
        $keyRelation = strtolower($first) . "$" . strtolower($second);

        /**
         * Check if it's a belongs-to relationship
         */
        if (isset($this->belongsTo[$keyRelation])) {
            return $this->belongsTo[$keyRelation];
        }

        /**
         * Check if it's a has-many relationship
         */
        if (isset($this->hasMany[$keyRelation])) {
            return $this->hasMany[$keyRelation];
        }

        /**
         * Check whether it's a has-one relationship
         */
        if (isset($this->hasOne[$keyRelation])) {
            return $this->hasOne[$keyRelation];
        }

        /**
         * Check whether it's a has-one-through relationship
         */
        if (isset($this->hasOneThrough[$keyRelation])) {
            return $this->hasOneThrough[$keyRelation];
        }

        /**
         * Check whether it's a has-many-to-many relationship
         */
        if (isset($this->hasManyToMany[$keyRelation])) {
            return $this->hasManyToMany[$keyRelation];
        }

        return false;
    }

    /**
     * Returns a reusable object from the internal list
     *
     * @param string $modelName
     * @param string $key
     *
     * @return mixed
     */
    public function getReusableRecords(string $modelName, string $key)
    {
        return $this->reusable[$key] ?? null;
    }

    /**
     * Returns the connection to write data related to a model
     *
     * @param ModelInterface $model
     *
     * @return AdapterInterface
     */
    public function getWriteConnection(ModelInterface $model): AdapterInterface
    {
        return $this->getConnection($model, $this->writeConnectionServices);
    }

    /**
     * Returns the connection service name used to write data related to a model
     *
     * @param ModelInterface $model
     *
     * @return string
     */
    public function getWriteConnectionService(ModelInterface $model): string
    {
        return $this->getConnectionService(
            $model,
            $this->writeConnectionServices
        );
    }

    /**
     * Checks whether a model has a belongsTo relation with another model
     *
     * @param string $modelName
     * @param string $modelRelation
     *
     * @return bool
     */
    public function hasBelongsTo(string $modelName, string $modelRelation): bool
    {
        return $this->checkHasRelationship("belongsTo", $modelName, $modelRelation);
    }

    /**
     * Checks whether a model has a hasMany relation with another model
     *
     * @param string $modelName
     * @param string $modelRelation
     *
     * @return bool
     */
    public function hasHasMany(string $modelName, string $modelRelation): bool
    {
        return $this->checkHasRelationship("hasMany", $modelName, $modelRelation);
    }

    /**
     * Checks whether a model has a hasManyToMany relation with another model
     *
     * @param string $modelName
     * @param string $modelRelation
     *
     * @return bool
     */
    public function hasHasManyToMany(string $modelName, string $modelRelation): bool
    {
        return $this->checkHasRelationship("hasManyToMany", $modelName, $modelRelation);
    }

    /**
     * Checks whether a model has a hasOne relation with another model
     *
     * @param string $modelName
     * @param string $modelRelation
     *
     * @return bool
     */
    public function hasHasOne(string $modelName, string $modelRelation): bool
    {
        return $this->checkHasRelationship("hasOne", $modelName, $modelRelation);
    }

    /**
     * Checks whether a model has a hasOneThrough relation with another model
     *
     * @param string $modelName
     * @param string $modelRelation
     *
     * @return bool
     */
    public function hasHasOneThrough(string $modelName, string $modelRelation): bool
    {
        return $this->checkHasRelationship("hasOneThrough", $modelName, $modelRelation);
    }

    /**
     * Initializes a model in the model manager
     *
     * @param ModelInterface $model
     *
     * @return bool
     * @throws EventsException
     */
    public function initialize(ModelInterface $model): bool
    {
        $className = mb_strtolower(get_class($model));

        /**
         * Models are just initialized once per request
         */
        if (isset($this->initialized[$className])) {
            return false;
        }

        /**
         * Update the model as initialized, this avoid cyclic initializations
         */
        $this->initialized[$className] = true;

        /**
         * Call the 'initialize' method if it's implemented
         */
        if (method_exists($model, "initialize")) {
            $model->initialize();
        }

        /**
         * Update the last initialized model, so it can be used in
         * modelsManager:afterInitialize
         */
        $this->lastInitialized = $model;

        /**
         * If an EventsManager is available we pass to it every initialized
         * model
         */
        $this->fireManagerEvent("modelsManager:afterInitialize", $model);

        return true;
    }

    /**
     * Check whether a model is already initialized
     *
     * @param string $className
     *
     * @return bool
     */
    public function isInitialized(string $className): bool
    {
        return isset($this->initialized[strtolower($className)]);
    }

    /**
     * Checks if a model is keeping snapshots for the queried records
     *
     * @param ModelInterface $model
     *
     * @return bool
     */
    public function isKeepingSnapshots(ModelInterface $model): bool
    {
        if (Settings::get("orm.dynamic_update")) {
            return true;
        }

        return $this->keepSnapshots[mb_strtolower(get_class($model))] ?? false;
    }

    /**
     * Checks if a model is using dynamic update instead of all-field update
     *
     * @param ModelInterface $model
     *
     * @return bool
     */
    public function isUsingDynamicUpdate(ModelInterface $model): bool
    {
        if (Settings::get("orm.dynamic_update")) {
            return true;
        }

        return $this->dynamicUpdate[mb_strtolower(get_class($model))] ?? false;
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
     *
     * @param ModelInterface $model
     * @param string         $property
     *
     * @return bool
     * @throws ReflectionException
     */
    final public function isVisibleModelProperty(
        ModelInterface $model,
        string $property
    ): bool {
        $className = get_class($model);

        if (!isset($this->modelVisibility[$className])) {
            $publicProperties     = [];
            $classReflection      = new ReflectionClass($className);
            $reflectionProperties = $classReflection->getProperties(ReflectionProperty::IS_PUBLIC);
            foreach ($reflectionProperties as $reflectionProperty) {
                $publicProperties[$reflectionProperty->name] = true;
            }
            $this->modelVisibility[$className] = $publicProperties;
        }

        $properties = $this->modelVisibility[$className];

        return array_key_exists($property, $properties);
    }

    /**
     * Sets if a model must keep snapshots
     *
     * @param ModelInterface $model
     * @param bool           $keepSnapshots
     *
     * @return void
     */
    public function keepSnapshots(ModelInterface $model, bool $keepSnapshots): void
    {
        $this->keepSnapshots[mb_strtolower(get_class($model))] = $keepSnapshots;
    }

    /**
     * Loads a model throwing an exception if it doesn't exist
     *
     * @param string $modelName
     *
     * @return ModelInterface
     * @throws Exception
     */
    public function load(string $modelName): ModelInterface
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
         */
        return new $modelName(null, $this->container, $this);
    }

    /**
     * Dispatch an event to the listeners and behaviors
     * This method expects that the endpoint listeners/behaviors returns true
     * meaning that a least one was implemented
     *
     * @param ModelInterface $model
     * @param string         $eventName
     * @param mixed          $data
     *
     * @return mixed
     */
    public function missingMethod(
        ModelInterface $model,
        string $eventName,
        mixed $data
    ): mixed {
        /**
         * Dispatch events to the global events manager
         */
        if (isset($this->behaviors[mb_strtolower(get_class($model))])) {
            $modelsBehaviors = $this->behaviors[mb_strtolower(get_class($model))];
            /**
             * Notify all the events on the behavior
             */
            foreach ($modelsBehaviors as $behavior) {
                $result = $behavior->missingMethod($model, $eventName, $data);

                if ($result !== null) {
                    return $result;
                }
            }
        }

        /**
         * Dispatch events to the global events manager
         */
        if (null !== $this->eventsManager) {
            return $this->eventsManager->fire(
                "model:" . $eventName,
                $model,
                $data
            );
        }

        return null;
    }

    /**
     * Receives events generated in the models and dispatches them to an
     * events-manager if available. Notify the behaviors that are listening in
     * the model
     *
     * @param string         $eventName
     * @param ModelInterface $model
     *
     * @return bool|mixed|null
     * @throws EventsException
     */
    public function notifyEvent(string $eventName, ModelInterface $model)
    {
        $status = true;

        /**
         * Dispatch events to the global events manager
         */
        if (isset($this->behaviors[mb_strtolower(get_class($model))])) {
            $modelsBehaviors = $this->behaviors[mb_strtolower(get_class($model))];

            /**
             * Notify all the events on the behavior
             */
            foreach ($modelsBehaviors as $behavior) {
                $status = $behavior->notify($eventName, $model);

                if ($status === false) {
                    return false;
                }
            }
        }

        /**
         * Dispatch events to the global events manager
         */
        if (null !== $this->eventsManager) {
            $status = $this->fireManagerEvent("model:" . $eventName, $model);

            if ($status === false) {
                return $status;
            }
        }

        /**
         * A model can has a specific events manager for it
         */
        if (isset($this->customEventsManager[mb_strtolower(get_class($model))])) {
            $customEventsManager = $this->customEventsManager[mb_strtolower(get_class($model))];
            $status              = $customEventsManager->fire(
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
     * Removes a behavior from a model
     *
     * @param ModelInterface $model
     * @param string         $behaviorClass
     */
    public function removeBehavior(
        ModelInterface $model,
        string $behaviorClass
    ): void {
        $entityName = mb_strtolower(get_class($model));

        if (isset($this->behaviors[$entityName])) {
            foreach ($this->behaviors[$entityName] as $key => $behavior) {
                if (get_class($behavior) === $behaviorClass) {
                    unset($this->behaviors[$entityName][$key]);
                }
            }

            // Reindex the array to remove gaps
            $this->behaviors[$entityName] = array_values($this->behaviors[$entityName]);
        }
    }

    /**
     * Sets both write and read connection service for a model
     *
     * @param ModelInterface $model
     * @param string         $connectionService
     *
     * @return void
     */
    public function setConnectionService(ModelInterface $model, string $connectionService): void
    {
        $this->setReadConnectionService($model, $connectionService);
        $this->setWriteConnectionService($model, $connectionService);
    }

    /**
     * Sets a custom events manager for a specific model
     *
     * @param ModelInterface         $model
     * @param EventsManagerInterface $eventsManager
     *
     * @return void
     */
    public function setCustomEventsManager(
        ModelInterface $model,
        EventsManagerInterface $eventsManager
    ): void {
        $this->customEventsManager[mb_strtolower(get_class($model))] = $eventsManager;
    }

    /**
     * Sets the prefix for all model sources.
     *
     * ```php
     * use Phalcon\Mvc\Model\Manager;
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
     *
     * $param string $prefix
     *
     * @return void
     */
    public function setModelPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    /**
     * Sets the mapped schema for a model
     *
     * @param ModelInterface $model
     * @param string         $schema
     *
     * @return void
     */
    public function setModelSchema(ModelInterface $model, string $schema): void
    {
        $this->schemas[mb_strtolower(get_class($model))] = $schema;
    }

    /**
     * Sets the mapped source for a model
     *
     * @param ModelInterface $model
     * @param string         $source
     *
     * @return void
     */
    public function setModelSource(ModelInterface $model, string $source): void
    {
        $this->sources[mb_strtolower(get_class($model))] = $source;
    }

    /**
     * Sets read connection service for a model
     *
     * @param ModelInterface $model
     * @param string         $connectionService
     *
     * @return void
     */
    public function setReadConnectionService(ModelInterface $model, string $connectionService): void
    {
        $this->readConnectionServices[mb_strtolower(get_class($model))] = $connectionService;
    }

    /**
     * Stores a reusable record in the internal list
     *
     * @param string $modelName
     * @param string $key
     * @param mixed  $records
     *
     * @return void
     */
    public function setReusableRecords(string $modelName, string $key, mixed $records): void
    {
        $this->reusable[$key] = $records;
    }

    /**
     * Sets write connection service for a model
     *
     * @param ModelInterface $model
     * @param string         $connectionService
     *
     * @return void
     */
    public function setWriteConnectionService(ModelInterface $model, string $connectionService): void
    {
        $this->writeConnectionServices[mb_strtolower(get_class($model))] = $connectionService;
    }

    /**
     * Sets if a model must use dynamic update instead of the all-field update
     *
     * @param ModelInterface $model
     * @param bool           $dynamicUpdate
     *
     * @return void
     */
    public function useDynamicUpdate(ModelInterface $model, bool $dynamicUpdate): void
    {
        $entityName                       = mb_strtolower(get_class($model));
        $this->dynamicUpdate[$entityName] = $dynamicUpdate;
        $this->keepSnapshots[$entityName] = $dynamicUpdate;
    }

    /**
     * Returns the connection to read or write data related to a model
     * depending on the connection services.
     *
     * @param ModelInterface $model
     * @param array          $connectionServices
     *
     * @return AdapterInterface
     * @throws Exception
     */
    protected function getConnection(
        ModelInterface $model,
        array $connectionServices
    ): AdapterInterface {
        $service = $this->getConnectionService($model, $connectionServices);

        $this->checkContainer(
            Exception::class,
            'the services related to the ORM'
        );

        /**
         * Request the connection service from the DI
         */
        $connection = $this->container->getShared($service);

        if (!is_object($connection)) {
            throw new Exception("Invalid injected connection service");
        }

        return $connection;
    }

    /**
     * Merge two arrays of find parameters
     *
     * @param mixed $findParamsOne
     * @param mixed $findParamsTwo
     *
     * @return array
     */
    final protected function mergeFindParameters(
        mixed $findParamsOne,
        mixed $findParamsTwo
    ): array {
        $findParams = [];

        if (is_string($findParamsOne)) {
            $findParamsOne = [
                "conditions" => $findParamsOne,
            ];
        }

        if (is_string($findParamsTwo)) {
            $findParamsTwo = [
                "conditions" => $findParamsTwo,
            ];
        }

        if (is_array($findParamsOne)) {
            foreach ($findParamsOne as $key => $value) {
                if ($key === 0 || $key === "conditions") {
                    if (!isset($findParams[0])) {
                        $findParams[0] = $value;
                    } else {
                        $findParams[0] = "(" . $findParams[0] . ") AND (" . $value . ")";
                    }
                } else {
                    $findParams[$key] = $value;
                }
            }
        }

        if (is_array($findParamsTwo)) {
            foreach ($findParamsTwo as $key => $value) {
                if ($key === 0 || $key === "conditions") {
                    if (!isset($findParams[0])) {
                        $findParams[0] = $value;
                    } else {
                        $findParams[0] = "(" . $findParams[0] . ") AND (" . $value . ")";
                    }
                } elseif ($key === "bind" || $key === "bindTypes") {
                    if (is_array($value)) {
                        if (!isset($findParams[$key])) {
                            $findParams[$key] = $value;
                        } else {
                            $findParams[$key] = array_merge(
                                $findParams[$key],
                                $value
                            );
                        }
                    }
                } else {
                    $findParams[$key] = $value;
                }
            }
        }

        return $findParams;
    }

    /**
     * @param string $collection
     * @param string $modelName
     * @param string $modelRelation
     *
     * @return bool
     */
    private function checkHasRelationship(
        string $collection,
        string $modelName,
        string $modelRelation
    ): bool {
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

        return isset($this->$collection[$keyRelation]);
    }

    /**
     * Creates a unique key to be used as index in a hash
     *
     * @param mixed $prefix
     * @param mixed $value
     *
     * @return string|null
     */
    private function getUniqueKey(mixed $prefix, mixed $value): string | null
    {
        $result = '';

        if (is_string($prefix)) {
            $result .= $prefix;
        }

        if (is_array($value)) {
            $result .= $this->getUniqueKeyArray($value);
        } else {
            $result .= $this->getUniqueKeyVal($value);
        }

        return ($result !== '') ? $result : null;
    }

    /**
     * @param array $value
     *
     * @return string
     */
    private function getUniqueKeyArray(array $value): string
    {
        $return  = '[';
        $length  = count($value);
        $counter = 0;

        foreach ($value as $item) {
            if (!is_object($item)) {
                if (is_array($item)) {
                    $return .= $this->getUniqueKeyArray($item);
                } else {
                    $return .= $this->getUniqueKeyVal($item);
                }
            }

            if (++$counter !== $length) {
                $return .= ',';
            }
        }

        $return .= ']';

        return $return;
    }

    private function getUniqueKeyVal(mixed $value): string
    {
        return is_string($value) ? $value : strval($value);
    }
}
