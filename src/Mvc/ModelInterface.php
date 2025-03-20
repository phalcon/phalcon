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

namespace Phalcon\Mvc;

use Phalcon\Db\Adapter\AdapterInterface;
use Phalcon\Di\DiInterface;
use Phalcon\Messages\MessageInterface;
use Phalcon\Mvc\Model\CriteriaInterface;
use Phalcon\Mvc\Model\MetaDataInterface;
use Phalcon\Mvc\Model\ResultInterface;
use Phalcon\Mvc\Model\ResultsetInterface;
use Phalcon\Mvc\Model\Row;
use Phalcon\Mvc\Model\TransactionInterface;

/**
 * Interface for Phalcon\Mvc\Model
 */
interface ModelInterface
{
    /**
     * Appends a customized message on the validation process
     *
     * @param MessageInterface $message
     *
     * @return ModelInterface
     */
    public function appendMessage(MessageInterface $message): ModelInterface;

    /**
     * Assigns values to a model from an array
     *
     * @param array      $data
     * @param mixed|null $whiteList
     * @param mixed|null $dataColumnMap
     *
     * @return ModelInterface
     */
    public function assign(
        array $data,
        mixed $whiteList = null,
        mixed $dataColumnMap = null
    ): ModelInterface;

    /**
     * Allows to calculate the average value on a column matching the specified
     * conditions
     *
     * @param array $parameters
     *
     * @return ResultsetInterface|float
     */
    public static function average(
        array $parameters = []
    ): ResultsetInterface | float;

    /**
     * Assigns values to a model from an array returning a new model
     *
     * @param ModelInterface $base
     * @param array          $data
     * @param int            $dirtyState
     *
     * @return ModelInterface
     */
    public static function cloneResult(
        ModelInterface $base,
        array $data,
        int $dirtyState = 0
    ): ModelInterface;

    /**
     * Assigns values to a model from an array returning a new model
     *
     * @param ModelInterface|Row $base
     * @param array              $data
     * @param mixed              $columnMap
     * @param int                $dirtyState
     * @param bool               $keepSnapshots
     *
     * @return ModelInterface
     */
    public static function cloneResultMap(
        mixed $base,
        array $data,
        mixed $columnMap,
        int $dirtyState = 0,
        bool $keepSnapshots = false
    ): ModelInterface | ResultInterface;

    /**
     * Returns a hydrated result based on the data and the column map
     *
     * @param array $data
     * @param mixed $columnMap
     * @param int   $hydrationMode
     *
     * @return mixed
     */
    public static function cloneResultMapHydrate(
        array $data,
        mixed $columnMap,
        int $hydrationMode
    );

    /**
     * Allows to count how many records match the specified conditions
     *
     * Returns an integer for simple queries or a ResultsetInterface
     * instance for when the GROUP condition is used. The results will
     * contain the count of each group.
     *
     * @param array|string|null $parameters
     *
     * @return int|ResultsetInterface
     */
    public static function count(mixed $parameters = null): int | ResultsetInterface;

    /**
     * Inserts a model instance. If the instance already exists in the
     * persistence it will throw an exception. Returning true on success or
     * false otherwise.
     *
     * @return bool
     */
    public function create(): bool;

    /**
     * Deletes a model instance. Returning true on success or false otherwise.
     *
     * @return bool
     */
    public function delete(): bool;

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed|null $parameters
     *
     * @return ResultsetInterface
     */
    public static function find(mixed $parameters = null): ResultsetInterface;

    /**
     * Allows to query the first record that match the specified conditions
     *
     * TODO: Current method signature must be reviewed in v5.
     *       As it must return only ?ModelInterface (it also returns Row).
     *
     * @see https://github.com/phalcon/cphalcon/issues/15212
     * @see https://github.com/phalcon/cphalcon/issues/15883
     *
     * @param mixed|null $parameters
     *
     * @return mixed|ModelInterface|Row|null
     */
    public static function findFirst(mixed $parameters = null): mixed;

    /**
     * Fires an event, implicitly calls behaviors and listeners in the events
     * manager are notified
     *
     * @param string $eventName
     *
     * @return bool|null
     */
    public function fireEvent(string $eventName): bool | null;

    /**
     * Fires an event, implicitly calls behaviors and listeners in the events
     * manager are notified. This method stops if one of the callbacks/listeners
     * returns bool false
     *
     * @param string $eventName
     *
     * @return bool|null
     */
    public function fireEventCancel(string $eventName): bool | null;

    /**
     * Returns one of the DIRTY_STATE_* constants telling if the record exists
     * in the database or not
     *
     * @return int
     */
    public function getDirtyState(): int;

    /**
     * Returns array of validation messages
     *
     * @return MessageInterface[]
     */
    public function getMessages(): array;

    /**
     * Returns the models meta-data service related to the entity instance.
     *
     * @return MetaDataInterface
     */
    public function getModelsMetaData(): MetaDataInterface;

    /**
     * Returns the type of the latest operation performed by the ORM
     * Returns one of the OP_* class constants
     *
     * @return int
     */
    public function getOperationMade(): int;

    /**
     * Gets internal database connection
     *
     * @return AdapterInterface
     */
    public function getReadConnection(): AdapterInterface;

    /**
     * Returns DependencyInjection connection service used to read data
     *
     * @return string
     */
    public function getReadConnectionService(): string;

    /**
     * Returns related records based on defined relations
     *
     * @param string     $alias
     * @param mixed|null $arguments
     *
     * @return mixed
     * @todo make arguments array type
     */
    public function getRelated(string $alias, mixed $arguments = null);

    /**
     * Returns schema name where table mapped is located
     *
     * @return string|null
     */
    public function getSchema(): string | null;

    /**
     * Returns table name mapped in the model
     *
     * @return string
     */
    public function getSource(): string;

    /**
     * Gets internal database connection
     *
     * @return AdapterInterface
     */
    public function getWriteConnection(): AdapterInterface;

    /**
     * Returns DependencyInjection connection service used to write data
     *
     * @return string
     */
    public function getWriteConnectionService(): string;

    /**
     * Allows to get the maximum value of a column that match the specified
     * conditions
     *
     * @param mixed|null $parameters
     *
     * @return mixed
     */
    public static function maximum(mixed $parameters = null): mixed;

    /**
     * Allows to get the minimum value of a column that match the specified
     * conditions
     *
     * @param mixed|null $parameters
     *
     * @return mixed
     */
    public static function minimum(mixed $parameters = null): mixed;

    /**
     * Create a criteria for a specific model
     *
     * @param DiInterface|null $container
     *
     * @return CriteriaInterface
     */
    public static function query(
        DiInterface | null $container = null
    ): CriteriaInterface;

    /**
     * Refreshes the model attributes re-querying the record from the database
     *
     * @return ModelInterface
     */
    public function refresh(): ModelInterface;

    /**
     * Inserts or updates a model instance. Returning true on success or false
     * otherwise.
     *
     * @return bool
     */
    public function save(): bool;

    /**
     * Sets both read/write connection services
     *
     * @param string $connectionService
     *
     * @return void
     */
    public function setConnectionService(string $connectionService): void;

    /**
     * Sets the dirty state of the object using one of the DIRTY_STATE_*
     * constants
     *
     * @param int $dirtyState
     *
     * @return ModelInterface|bool
     */
    public function setDirtyState(int $dirtyState): ModelInterface | bool;

    /**
     * Sets the DependencyInjection connection service used to read data
     *
     * @param string $connectionService
     *
     * @return void
     */
    public function setReadConnectionService(string $connectionService): void;

    /**
     * Sets the record's snapshot data. This method is used internally to set
     * snapshot data when the model was set up to keep snapshot data
     *
     * @param array      $data
     * @param mixed|null $columnMap
     *
     * @return void
     */
    public function setSnapshotData(array $data, mixed $columnMap = null): void;

    /**
     * Sets a transaction related to the Model instance
     *
     * @param TransactionInterface $transaction
     *
     * @return ModelInterface
     */
    public function setTransaction(
        TransactionInterface $transaction
    ): ModelInterface;

    /**
     * Sets the DependencyInjection connection service used to write data
     *
     * @param string $connectionService
     *
     * @return void
     */
    public function setWriteConnectionService(string $connectionService): void;

    /**
     * Skips the current operation forcing a success state
     *
     * @param bool $skip
     *
     * @return void
     */
    public function skipOperation(bool $skip): void;

    /**
     * Allows to calculate a sum on a column that match the specified conditions
     *
     * @param mixed|null $parameters
     *
     * @return ResultsetInterface|float
     */
    public static function sum(mixed $parameters = null): ResultsetInterface | float;

    /**
     * Updates a model instance. If the instance doesn't exist in the
     * persistence it will throw an exception. Returning true on success or
     * false otherwise.
     *
     * @return bool
     */
    public function update(): bool;

    /**
     * Check whether validation process has generated any messages
     *
     * @return bool
     */
    public function validationHasFailed(): bool;
}
