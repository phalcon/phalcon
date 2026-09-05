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

use Phalcon\Contracts\Mvc\MvcTypes;
use Phalcon\Db\Adapter\AdapterInterface;
use Phalcon\Events\ManagerInterface as EvManagerInterface;
use Phalcon\Mvc\Model\Query\BuilderInterface;
use Phalcon\Mvc\Model\Query\StatusInterface;
use Phalcon\Mvc\Model\Resultset\Simple;
use Phalcon\Mvc\ModelInterface;

/**
 * Interface for Phalcon\Mvc\Model\Manager
 *
 * @phpstan-import-type mvc_model_bind_params from MvcTypes
 * @phpstan-import-type mvc_model_bind_types from MvcTypes
 * @phpstan-import-type mvc_model_parameters from MvcTypes
 * @phpstan-import-type mvc_relation_options from MvcTypes
 *
 * The manager class carries these members and the framework calls them on
 * the interface. They join the interface in the next major; until then the
 * tags below record what all implementations provide.
 *
 * @method EvManagerInterface|null getCustomEventsManager(ModelInterface $model)
 * @method void                    setCustomEventsManager(ModelInterface $model, EvManagerInterface $eventsManager)
 */
interface ManagerInterface
{
    /**
     * Binds a behavior to a model
     */
    public function addBehavior(
        ModelInterface $model,
        BehaviorInterface $behavior
    ): void;

    /**
     * Setup a relation reverse 1-1  between two models
     *
     * @phpstan-param mvc_relation_options $options
     */
    public function addBelongsTo(
        ModelInterface $model,
        mixed $fields,
        string $referencedModel,
        mixed $referencedFields,
        array $options = []
    ): RelationInterface;

    /**
     * Setup a relation 1-n between two models
     *
     * @phpstan-param mvc_relation_options $options
     */
    public function addHasMany(
        ModelInterface $model,
        mixed $fields,
        string $referencedModel,
        mixed $referencedFields,
        array $options = []
    ): RelationInterface;

    /**
     * Setups a relation n-m between two models
     *
     * @phpstan-param mvc_relation_options $options
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
    ): RelationInterface;

    /**
     * Setup a 1-1 relation between two models
     *
     * @phpstan-param mvc_relation_options $options
     */
    public function addHasOne(
        ModelInterface $model,
        mixed $fields,
        string $referencedModel,
        mixed $referencedFields,
        array $options = []
    ): RelationInterface;

    /**
     * Setups a 1-1 relation between two models using an intermediate table
     *
     * @phpstan-param mvc_relation_options $options
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
    ): RelationInterface;

    /**
     * Clears the internal reusable list
     */
    public function clearReusableObjects(): void;

    /**
     * Creates a Phalcon\Mvc\Model\Query\Builder
     */
    public function createBuilder(
        mixed $params = null
    ): BuilderInterface;

    /**
     * Creates a Phalcon\Mvc\Model\Query without execute it
     */
    public function createQuery(string $phql): QueryInterface;

    /**
     * Creates a Phalcon\Mvc\Model\Query and execute it
     *
     * @return mixed
     * @return ResultsetInterface|StatusInterface
     */
    public function executeQuery(
        string $phql,
        mixed $placeholders = null,
        mixed $types = null
    );

    /**
     * Gets belongsTo relations defined on a model
     *
     * @return RelationInterface[]
     */
    public function getBelongsTo(ModelInterface $model): array;

    /**
     * Gets belongsTo related records from a model
     */
    public function getBelongsToRecords(
        string $modelName,
        string $modelRelation,
        ModelInterface $record,
        mixed $parameters = null,
        string | null $method = null
    ): bool | ResultsetInterface;

    /**
     * Returns the newly created Phalcon\Mvc\Model\Query\Builder or null
     */
    public function getBuilder(): BuilderInterface | null;

    /**
     * Gets hasMany relations defined on a model
     *
     * @return RelationInterface[]
     */
    public function getHasMany(ModelInterface $model): array;

    /**
     * Gets hasMany related records from a model
     */
    public function getHasManyRecords(
        string $modelName,
        string $modelRelation,
        ModelInterface $record,
        mixed $parameters = null,
        string | null $method = null
    ): bool | ResultsetInterface;

    /**
     * Gets hasManyToMany relations defined on a model
     *
     * @return RelationInterface[]
     */
    public function getHasManyToMany(ModelInterface $model): array;

    /**
     * Gets hasOne relations defined on a model
     *
     * @return RelationInterface[]
     */
    public function getHasOne(ModelInterface $model): array;

    /**
     * Gets hasOne relations defined on a model
     *
     * @return RelationInterface[]
     */
    public function getHasOneAndHasMany(ModelInterface $model): array;

    /**
     * Gets hasOne related records from a model
     */
    public function getHasOneRecords(
        string $modelName,
        string $modelRelation,
        ModelInterface $record,
        mixed $parameters = null,
        string | null $method = null
    ): bool | ModelInterface;

    /**
     * Gets hasOneThrough relations defined on a model
     *
     * @return RelationInterface[]
     */
    public function getHasOneThrough(ModelInterface $model): array;

    /**
     * Get last initialized model
     */
    public function getLastInitialized(): ModelInterface | null;

    /**
     * Returns the last query created or executed in the models manager
     */
    public function getLastQuery(): QueryInterface;

    /**
     * Returns the mapped schema for a model
     */
    public function getModelSchema(ModelInterface $model): string | null;

    /**
     * Returns the mapped source for a model
     */
    public function getModelSource(ModelInterface $model): string;

    /**
     * Returns the connection to read data related to a model
     */
    public function getReadConnection(ModelInterface $model): AdapterInterface;

    /**
     * Returns the connection service name used to read data related to a model
     */
    public function getReadConnectionService(ModelInterface $model): string;

    /**
     * Returns a relation by its alias
     */
    public function getRelationByAlias(
        string $modelName,
        string $alias
    ): bool | RelationInterface;

    /**
     * Helper method to query records based on a relation definition
     *
     * @return false|int|ModelInterface|Simple
     */
    public function getRelationRecords(
        RelationInterface $relation,
        ModelInterface $record,
        mixed $parameters = null,
        string | null $method = null
    );

    /**
     * Query all the relationships defined on a model
     *
     * @return RelationInterface[]
     */
    public function getRelations(string $modelName): array;

    /**
     * Query the relations between two models
     *
     * @return bool|RelationInterface[]
     */
    public function getRelationsBetween(
        string $first,
        string $second
    ): array | bool;

    /**
     * Returns a reusable object from the internal list
     *
     * @return mixed
     */
    public function getReusableRecords(string $modelName, string $key);

    /**
     * Returns the connection to write data related to a model
     */
    public function getWriteConnection(ModelInterface $model): AdapterInterface;

    /**
     * Returns the connection service name used to write data related to a model
     */
    public function getWriteConnectionService(ModelInterface $model): string;

    /**
     * Checks whether a model has a belongsTo relation with another model
     */
    public function hasBelongsTo(string $modelName, string $modelRelation): bool;

    /**
     * Checks whether a model has a hasMany relation with another model
     */
    public function hasHasMany(string $modelName, string $modelRelation): bool;

    /**
     * Checks whether a model has a hasManyToMany relation with another model
     */
    public function hasHasManyToMany(
        string $modelName,
        string $modelRelation
    ): bool;

    /**
     * Checks whether a model has a hasOne relation with another model
     */
    public function hasHasOne(string $modelName, string $modelRelation): bool;

    /**
     * Checks whether a model has a hasOneThrough relation with another model
     */
    public function hasHasOneThrough(
        string $modelName,
        string $modelRelation
    ): bool;

    /**
     * Initializes a model in the model manager
     *
     * @return mixed
     */
    public function initialize(ModelInterface $model);

    /**
     * Check of a model is already initialized
     */
    public function isInitialized(string $className): bool;

    /**
     * Checks if a model is keeping snapshots for the queried records
     */
    public function isKeepingSnapshots(ModelInterface $model): bool;

    /**
     * Checks if a model is using dynamic update instead of all-field update
     */
    public function isUsingDynamicUpdate(ModelInterface $model): bool;

    /**
     * Check whether a model property is declared as public.
     *
     * ```php
     * $isPublic = $manager->isVisibleModelProperty(
     *     new Invoices(),
     *     "inv_title"
     * );
     * ```
     */
    public function isVisibleModelProperty(
        ModelInterface $model,
        string $property
    ): bool;

    /**
     * Sets if a model must keep snapshots
     */
    public function keepSnapshots(
        ModelInterface $model,
        bool $keepSnapshots
    ): void;

    /**
     * Loads a model throwing an exception if it does not exist
     */
    public function load(string $modelName): ModelInterface;

    /**
     * Dispatch an event to the listeners and behaviors
     * This method expects that the endpoint listeners/behaviors returns true
     * meaning that a least one is implemented
     *
     * @return mixed
     */
    public function missingMethod(
        ModelInterface $model,
        string $eventName,
        mixed $data
    );

    /**
     * Receives events generated in the models and dispatches them to an
     * events-manager if available. Notify the behaviors that are listening
     * in the model
     *
     * @return mixed
     */
    public function notifyEvent(string $eventName, ModelInterface $model);

    /**
     * Marks the model's write connection service as written-to for the
     * current request cycle (sticky connections)
     */
    public function registerWrite(ModelInterface $model): void;

    /**
     * Removes a behavior from a model
     */
    public function removeBehavior(
        ModelInterface $model,
        string $behaviorClass
    ): void;

    /**
     * Clears the per-request sticky write tracking
     */
    public function resetConnectionState(): void;

    /**
     * Sets both write and read connection service for a model
     */
    public function setConnectionService(
        ModelInterface $model,
        string $connectionService
    ): void;

    /**
     * Sets the mapped schema for a model
     */
    public function setModelSchema(ModelInterface $model, string $schema): void;

    /**
     * Sets the mapped source for a model
     */
    public function setModelSource(ModelInterface $model, string $source): void;

    /**
     * Sets read connection service for a model
     */
    public function setReadConnectionService(
        ModelInterface $model,
        string $connectionService
    ): void;

    /**
     * Stores a reusable record in the internal list
     */
    public function setReusableRecords(
        string $modelName,
        string $key,
        mixed $records
    ): void;

    /**
     * Enables or disables sticky connections
     */
    public function setSticky(bool $sticky): void;

    /**
     * Sets write connection service for a model
     *
     * @return mixed
     */
    public function setWriteConnectionService(
        ModelInterface $model,
        string $connectionService
    );

    /**
     * Sets if a model must use dynamic update instead of the all-field update
     */
    public function useDynamicUpdate(
        ModelInterface $model,
        bool $dynamicUpdate
    ): void;
}
