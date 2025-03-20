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
use Phalcon\Mvc\Model\Query\BuilderInterface;
use Phalcon\Mvc\Model\Query\StatusInterface;
use Phalcon\Mvc\Model\Resultset\Simple;
use Phalcon\Mvc\ModelInterface;

/**
 * Interface for Phalcon\Mvc\Model\Manager
 */
interface ManagerInterface
{
    /**
     * Binds a behavior to a model
     *
     * @param ModelInterface    $model
     * @param BehaviorInterface $behavior
     *
     * @return void
     */
    public function addBehavior(
        ModelInterface $model,
        BehaviorInterface $behavior
    ): void;

    /**
     * Setup a relation reverse 1-1  between two models
     *
     * @param ModelInterface $model
     * @param mixed          $fields
     * @param string         $referencedModel
     * @param string         $referencedFields
     * @param array          $options
     *
     * @return RelationInterface
     */
    public function addBelongsTo(
        ModelInterface $model,
        mixed $fields,
        string $referencedModel,
        string $referencedFields,
        array $options = []
    ): RelationInterface;

    /**
     * Setup a relation 1-n between two models
     *
     * @param ModelInterface $model
     * @param mixed          $fields
     * @param string         $referencedModel
     * @param string         $referencedFields
     * @param array          $options
     *
     * @return RelationInterface
     */
    public function addHasMany(
        ModelInterface $model,
        mixed $fields,
        string $referencedModel,
        string $referencedFields,
        array $options = []
    ): RelationInterface;

    /**
     * Setups a relation n-m between two models
     *
     * @param ModelInterface $model
     * @param mixed          $fields
     * @param string         $intermediateModel
     * @param mixed          $intermediateFields
     * @param mixed          $intermediateReferencedFields
     * @param string         $referencedModel
     * @param string         $referencedFields
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
        string $referencedFields,
        array $options = []
    ): RelationInterface;

    /**
     * Setup a 1-1 relation between two models
     *
     * @param ModelInterface $model
     * @param mixed          $fields
     * @param string         $referencedModel
     * @param string         $referencedFields
     * @param array          $options
     *
     * @return RelationInterface
     */
    public function addHasOne(
        ModelInterface $model,
        mixed $fields,
        string $referencedModel,
        string $referencedFields,
        array $options = []
    ): RelationInterface;

    /**
     * Setups a 1-1 relation between two models using an intermediate table
     *
     * @param ModelInterface $model
     * @param mixed          $fields
     * @param string         $intermediateModel
     * @param mixed          $intermediateFields
     * @param mixed          $intermediateReferencedFields
     * @param string         $referencedModel
     * @param string         $referencedFields
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
        string $referencedFields,
        array $options = []
    ): RelationInterface;

    /**
     * Creates a Phalcon\Mvc\Model\Query\Builder
     *
     * @param array|string|null $params
     *
     * @return BuilderInterface
     */
    public function createBuilder(
        array | string | null $params = null
    ): BuilderInterface;

    /**
     * Creates a Phalcon\Mvc\Model\Query without execute it
     *
     * @param string $phql
     *
     * @return QueryInterface
     */
    public function createQuery(string $phql): QueryInterface;

    /**
     * Creates a Phalcon\Mvc\Model\Query and execute it
     *
     * @param string     $phql
     * @param array|null $placeholders
     * @param array|null $types
     *
     * @return mixed
     * @return ResultsetInterface|StatusInterface
     */
    public function executeQuery(
        string $phql,
        array | null $placeholders = null,
        array | null $types = null
    ): mixed;

    /**
     * Gets belongsTo relations defined on a model
     *
     * @param ModelInterface $model
     *
     * @return RelationInterface[]
     */
    public function getBelongsTo(ModelInterface $model): array;

    /**
     * Gets belongsTo related records from a model
     *
     * @param string            $modelName
     * @param string            $modelRelation
     * @param ModelInterface    $record
     * @param array|string|null $parameters
     * @param string|null       $method
     */
    public function getBelongsToRecords(
        string $modelName,
        string $modelRelation,
        ModelInterface $record,
        array | string | null $parameters = null,
        string | null $method = null
    ): ResultsetInterface | bool;

    /**
     * Returns the newly created Phalcon\Mvc\Model\Query\Builder or null
     *
     * @return BuilderInterface|null
     */
    public function getBuilder(): BuilderInterface | null;

    /**
     * Gets hasMany relations defined on a model
     *
     * @param ModelInterface $model
     *
     * @return RelationInterface[]
     */
    public function getHasMany(ModelInterface $model): array;

    /**
     * Gets hasMany related records from a model
     *
     * @param string            $modelName
     * @param string            $modelRelation
     * @param ModelInterface    $record
     * @param array|string|null $parameters
     * @param string|null       $method
     */
    public function getHasManyRecords(
        string $modelName,
        string $modelRelation,
        ModelInterface $record,
        array | string | null $parameters = null,
        string | null $method = null
    ): ResultsetInterface | bool;

    /**
     * Gets hasManyToMany relations defined on a model
     *
     * @param ModelInterface $model
     *
     * @return RelationInterface[]
     */
    public function getHasManyToMany(ModelInterface $model): array;

    /**
     * Gets hasOne relations defined on a model
     *
     * @param ModelInterface $model
     *
     * @return RelationInterface[]
     */
    public function getHasOne(ModelInterface $model): array;

    /**
     * Gets hasOne relations defined on a model
     *
     * @param ModelInterface $model
     *
     * @return RelationInterface[]
     */
    public function getHasOneAndHasMany(ModelInterface $model): array;

    /**
     * Gets hasOne related records from a model
     *
     * @param string            $modelName
     * @param string            $modelRelation
     * @param ModelInterface    $record
     * @param array|string|null $parameters
     * @param string|null       $method
     */
    public function getHasOneRecords(
        string $modelName,
        string $modelRelation,
        ModelInterface $record,
        array | string | null $parameters = null,
        string | null $method = null
    ): ModelInterface | bool;

    /**
     * Gets hasOneThrough relations defined on a model
     *
     * @param ModelInterface $model
     *
     * @return RelationInterface[]
     */
    public function getHasOneThrough(ModelInterface $model): array;

    /**
     * Get last initialized model
     *
     * @return ModelInterface
     */
    public function getLastInitialized(): ModelInterface;

    /**
     * Returns the last query created or executed in the models manager
     *
     * @return QueryInterface
     */
    public function getLastQuery(): QueryInterface;

    /**
     * Returns the mapped schema for a model
     *
     * @param ModelInterface $model
     *
     * @return string|null
     */
    public function getModelSchema(ModelInterface $model): string | null;

    /**
     * Returns the mapped source for a model
     *
     * @param ModelInterface $model
     *
     * @return string
     */
    public function getModelSource(ModelInterface $model): string;

    /**
     * Returns the connection to read data related to a model
     *
     * @param ModelInterface $model
     *
     * @return AdapterInterface
     */
    public function getReadConnection(ModelInterface $model): AdapterInterface;

    /**
     * Returns the connection service name used to read data related to a model
     *
     * @param ModelInterface $model
     *
     * @return string
     */
    public function getReadConnectionService(ModelInterface $model): string;

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
    ): RelationInterface | bool;

    /**
     * Helper method to query records based on a relation definition
     *
     * @param RelationInterface $relation
     * @param ModelInterface    $record
     * @param array|string|null $parameters
     * @param string|null       $method
     *
     * @return ModelInterface|Simple|int|false
     */
    public function getRelationRecords(
        RelationInterface $relation,
        ModelInterface $record,
        array | string | null $parameters = null,
        string | null $method = null
    ): ModelInterface | Simple | int | false;

    /**
     * Query all the relationships defined on a model
     *
     * @param string $modelName
     *
     * @return RelationInterface[]
     */
    public function getRelations(string $modelName): array;

    /**
     * Query the relations between two models
     *
     * @param string $first
     * @param string $second
     *
     * @return RelationInterface[]|bool
     */
    public function getRelationsBetween(
        string $first,
        string $second
    ): array | bool;

    /**
     * Returns the connection to write data related to a model
     *
     * @param ModelInterface $model
     *
     * @return AdapterInterface
     */
    public function getWriteConnection(ModelInterface $model): AdapterInterface;

    /**
     * Returns the connection service name used to write data related to a model
     *
     * @param ModelInterface $model
     *
     * @return string
     */
    public function getWriteConnectionService(ModelInterface $model): string;

    /**
     * Checks whether a model has a belongsTo relation with another model
     *
     * @param string $modelName
     * @param string $modelRelation
     *
     * @return bool
     */
    public function hasBelongsTo(string $modelName, string $modelRelation): bool;

    /**
     * Checks whether a model has a hasMany relation with another model
     *
     * @param string $modelName
     * @param string $modelRelation
     *
     * @return bool
     */
    public function hasHasMany(string $modelName, string $modelRelation): bool;

    /**
     * Checks whether a model has a hasManyToMany relation with another model
     *
     * @param string $modelName
     * @param string $modelRelation
     *
     * @return bool
     */
    public function hasHasManyToMany(
        string $modelName,
        string $modelRelation
    ): bool;

    /**
     * Checks whether a model has a hasOne relation with another model
     *
     * @param string $modelName
     * @param string $modelRelation
     *
     * @return bool
     */
    public function hasHasOne(string $modelName, string $modelRelation): bool;

    /**
     * Checks whether a model has a hasOneThrough relation with another model
     *
     * @param string $modelName
     * @param string $modelRelation
     *
     * @return bool
     */
    public function hasHasOneThrough(
        string $modelName,
        string $modelRelation
    ): bool;

    /**
     * Initializes a model in the model manager
     *
     * @param ModelInterface $model
     *
     * @return mixed
     */
    public function initialize(ModelInterface $model);

    /**
     * Check of a model is already initialized
     *
     * @param string $className
     *
     * @return bool
     */
    public function isInitialized(string $className): bool;

    /**
     * Checks if a model is keeping snapshots for the queried records
     *
     * @param ModelInterface $model
     *
     * @return bool
     */
    public function isKeepingSnapshots(ModelInterface $model): bool;

    /**
     * Checks if a model is using dynamic update instead of all-field update
     *
     * @param ModelInterface $model
     *
     * @return bool
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
     *
     * @param ModelInterface $model
     * @param string         $property
     *
     * @return bool
     */
    public function isVisibleModelProperty(
        ModelInterface $model,
        string $property
    ): bool;

    /**
     * Sets if a model must keep snapshots
     *
     * @param ModelInterface $model
     * @param bool           $keepSnapshots
     *
     * @return void
     */
    public function keepSnapshots(
        ModelInterface $model,
        bool $keepSnapshots
    ): void;

    /**
     * Loads a model throwing an exception if it doesn't exist
     *
     * @param string $modelName
     *
     * @return ModelInterface
     */
    public function load(string $modelName): ModelInterface;

    /**
     * Dispatch an event to the listeners and behaviors
     * This method expects that the endpoint listeners/behaviors returns true
     * meaning that a least one is implemented
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
    );

    /**
     * Receives events generated in the models and dispatches them to an
     * events-manager if available. Notify the behaviors that are listening
     * in the model
     *
     * @param string         $eventName
     * @param ModelInterface $model
     *
     * @return mixed
     */
    public function notifyEvent(string $eventName, ModelInterface $model);

    /**
     * Removes a behavior from a model
     *
     * @param ModelInterface $model
     * @param string         $behaviorClass
     *
     * @return void
     */
    public function removeBehavior(
        ModelInterface $model,
        string $behaviorClass
    ): void;

    /**
     * Sets both write and read connection service for a model
     *
     * @param ModelInterface $model
     * @param string         $connectionService
     *
     * @return void
     */
    public function setConnectionService(
        ModelInterface $model,
        string $connectionService
    ): void;

    /**
     * Sets the mapped schema for a model
     *
     * @param ModelInterface $model
     * @param string         $schema
     *
     * @return void
     */
    public function setModelSchema(ModelInterface $model, string $schema): void;

    /**
     * Sets the mapped source for a model
     *
     * @param ModelInterface $model
     * @param string         $source
     *
     * @return void
     */
    public function setModelSource(ModelInterface $model, string $source): void;

    /**
     * Sets read connection service for a model
     *
     * @param ModelInterface $model
     * @param string         $connectionService
     *
     * @return void
     */
    public function setReadConnectionService(
        ModelInterface $model,
        string $connectionService
    ): void;

    /**
     * Sets write connection service for a model
     *
     * @param ModelInterface $model
     * @param string         $connectionService
     *
     * @return mixed
     */
    public function setWriteConnectionService(
        ModelInterface $model,
        string $connectionService
    );

    /**
     * Sets if a model must use dynamic update instead of the all-field update
     *
     * @param ModelInterface $model
     * @param bool           $dynamicUpdate
     *
     * @return void
     */
    public function useDynamicUpdate(
        ModelInterface $model,
        bool $dynamicUpdate
    ): void;
}
