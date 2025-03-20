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

use ArrayAccess;
use Closure;
use Countable;
use Iterator;
use JsonSerializable;
use Phalcon\Db\Enum;
use Phalcon\Messages\MessageInterface;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Support\Settings;
use Psr\SimpleCache\CacheInterface;
use SeekableIterator;
use Serializable;

use function call_user_func_array;
use function is_array;
use function is_object;
use function method_exists;

/**
 * This component allows to Phalcon\Mvc\Model returns large resultsets with
 * the minimum memory consumption. Resultsets can be traversed using a standard
 * foreach or a while statement. If a resultset is serialized it will dump all
 * the rows into a big array. Then unserialize will retrieve the rows as they
 * were before serializing.
 *
 * ```php
 *
 * // Using a standard foreach
 * $robots = Robots::find(
 *     [
 *         "type = 'virtual'",
 *         "order" => "name",
 *     ]
 * );
 *
 * foreach ($robots as robot) {
 *     echo robot->name, "\n";
 * }
 *
 * // Using a while
 * $robots = Robots::find(
 *     [
 *         "type = 'virtual'",
 *         "order" => "name",
 *     ]
 * );
 *
 * $robots->rewind();
 *
 * while ($robots->valid()) {
 *     $robot = $robots->current();
 *
 *     echo $robot->name, "\n";
 *
 *     $robots->next();
 * }
 * ```
 */
abstract class Resultset implements
    ResultsetInterface,
    Iterator,
    SeekableIterator,
    Countable,
    ArrayAccess,
    Serializable,
    JsonSerializable
{
    public const HYDRATE_ARRAYS      = 1;
    public const HYDRATE_OBJECTS     = 2;
    public const HYDRATE_RECORDS     = 0;
    public const TYPE_RESULT_FULL    = 0;
    public const TYPE_RESULT_PARTIAL = 1;

    /**
     * @var mixed|null
     */
    protected mixed $activeRow = null;

    /**
     * @var CacheInterface|null
     */
    protected CacheInterface | null $cache = null;

    /**
     * @var int
     */
    protected int $count = 0;

    /**
     * @var array
     */
    protected array $errorMessages = [];

    /**
     * @var int
     */
    protected int $hydrateMode = 0;

    /**
     * @var bool
     */
    protected bool $isFresh = true;

    /**
     * @var int
     */
    protected int $pointer = 0;

    /**
     * @var mixed|null
     */
    protected mixed $row = null;

    /**
     * @var array|null
     */
    protected array | null $rows = null;

    /**
     * Phalcon\Mvc\Model\Resultset constructor
     *
     * @param ResultInterface|false $result
     * @param mixed|null            $cache
     *
     * @throws Exception
     */
    public function __construct(
        protected mixed $result,
        mixed $cache = null
    ) {
        /**
         * 'false' is given as result for empty result-sets
         */
        if (!is_object($result)) {
            $this->count = 0;
            $this->rows  = [];

            return;
        }

        /**
         * Valid resultsets are Phalcon\Db\ResultInterface instances
         */
        $this->result = $result;

        /**
         * Update the related cache if any
         */
        if ($cache !== null) {
            if (!$cache instanceof CacheInterface) {
                throw new Exception(
                    "Cache service must be an object implementing " .
                    "Psr\SimpleCache\CacheInterface"
                );
            }


            $this->cache = $cache;
        }

        /**
         * Do the fetch using only associative indexes
         */
        $result->setFetchMode(Enum::FETCH_ASSOC);

        /**
         * Update the row-count
         */
        $rowCount    = $result->numRows();
        $this->count = $rowCount;

        /**
         * Empty result-set
         */
        if ($rowCount == 0) {
            $this->rows = [];

            return;
        }

        /**
         * Small result-sets with less equals 32 rows are fetched at once
         */
        $prefetchRecords = (int)Settings::get("orm.resultset_prefetch_records");
        if ($prefetchRecords > 0 && $rowCount <= $prefetchRecords) {
            /**
             * Fetch ALL rows from database
             */
            $rows = $result->fetchAll();

            if (is_array($rows)) {
                $this->rows = $rows;
            } else {
                $this->rows = [];
            }
        }
    }

    /**
     * Counts how many rows are in the resultset
     *
     * @return int
     */
    final public function count(): int
    {
        return $this->count;
    }

    /**
     * Deletes every record in the resultset
     *
     * @param Closure|null $conditionCallback
     *
     * @return bool
     * @throws Exception
     */
    public function delete(Closure | null $conditionCallback = null): bool
    {
        $connection  = null;
        $result      = true;
        $transaction = false;

        $this->rewind();

        while ($this->valid()) {
            $record = $this->current();

            if ($transaction === false) {
                /**
                 * We only can delete resultsets if every element is a complete object
                 */
                if (!method_exists($record, "getWriteConnection")) {
                    throw new Exception("The returned record is not valid");
                }

                $connection  = $record->getWriteConnection();
                $transaction = true;

                $connection->begin();
            }

            /**
             * Perform additional validations
             */
            if (
                is_object($conditionCallback) &&
                call_user_func_array($conditionCallback, [$record]) === false
            ) {
                $this->next();

                continue;
            }

            /**
             * Try to delete the record
             */
            if (!$record->delete()) {
                /**
                 * Get the messages from the record that produce the error
                 */
                $this->errorMessages = $record->getMessages();

                /**
                 * Rollback the transaction
                 */
                $connection->rollback();

                $result      = false;
                $transaction = false;

                break;
            }

            $this->next();
        }

        /**
         * Commit the transaction
         */
        if ($transaction === true) {
            $connection->commit();
        }

        return $result;
    }

    /**
     * Filters a resultset returning only those the developer requires
     *
     *```php
     * $filtered = $robots->filter(
     *     function ($robot) {
     *         if ($robot->id < 3) {
     *             return $robot;
     *         }
     *     }
     * );
     *```
     *
     * @param callable $filter
     *
     * @return array|ModelInterface[]
     */
    public function filter(callable $filter): array
    {
        $records = [];

        $this->rewind();

        while ($this->valid()) {
            $record = $this->current();

            $processedRecord = call_user_func_array(
                $filter,
                [
                    $record,
                ]
            );

            /**
             * Only add processed records to 'records' if the returned value is an array/object
             */
            if (!is_object($processedRecord) && !is_array($processedRecord)) {
                $this->next();

                continue;
            }

            $records[] = $processedRecord;

            $this->next();
        }

        return $records;
    }

    /**
     * Returns the associated cache for the resultset
     */
    public function getCache(): mixed
    {
        return $this->cache;
    }

    /**
     * Get first row in the resultset
     *
     * ```php
     * $model = new Robots();
     * $manager = $model->getModelsManager();
     *
     * // \Robots
     * $manager->createQuery('SELECT * FROM Robots')
     *         ->execute()
     *         ->getFirst();
     *
     * // \Phalcon\Mvc\Model\Row
     * $manager->createQuery('SELECT r.id FROM Robots AS r')
     *         ->execute()
     *         ->getFirst();
     *
     * // NULL
     * $manager->createQuery('SELECT r.id FROM Robots AS r WHERE r.name = "NON-EXISTENT"')
     *         ->execute()
     *         ->getFirst();
     * ```
     *
     * @return ModelInterface|Row|null
     */
    public function getFirst(): mixed
    {
        if ($this->count == 0) {
            return null;
        }

        $this->seek(0);

        return $this->current();
    }

    /**
     * Returns the current hydration mode
     *
     * @return int
     */
    public function getHydrateMode(): int
    {
        return $this->hydrateMode;
    }

    /**
     * Get last row in the resultset
     *
     * @return ModelInterface|null
     */
    public function getLast(): ModelInterface | null
    {
        $count = $this->count;

        if ($count == 0) {
            return null;
        }

        $this->seek($count - 1);

        return $this->current();
    }

    /**
     * Returns the error messages produced by a batch operation
     *
     * @return array|MessageInterface[]
     */
    public function getMessages(): array
    {
        return $this->errorMessages;
    }

    /**
     * Returns the internal type of data retrieval that the resultset is using
     *
     * @return int
     */
    public function getType(): int
    {
        return is_array($this->rows) ? self::TYPE_RESULT_FULL : self::TYPE_RESULT_PARTIAL;
    }

    /**
     * Tell if the resultset if fresh or an old one cached
     *
     * @return bool
     */
    public function isFresh(): bool
    {
        return $this->isFresh;
    }

    /**
     * Returns serialised model objects as array for json_encode.
     * Calls jsonSerialize on each object if present
     *
     *```php
     * $robots = Robots::find();
     *
     * echo json_encode($robots);
     *```
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $records = [];

        $this->rewind();

        while ($this->valid()) {
            $current = $this->current();

            if (is_object($current) && method_exists($current, "jsonSerialize")) {
                $records[] = $current->jsonSerialize();
            } else {
                $records[] = $current;
            }

            $this->next();
        }

        return $records;
    }

    /**
     * Gets pointer number of active row in the resultset
     *
     * @return int|null
     */
    public function key(): int | null
    {
        if (!$this->valid()) {
            return null;
        }

        return $this->pointer;
    }

    /**
     * Moves cursor to next row in the resultset
     *
     * @return void
     */
    public function next(): void
    {
        // Seek to the next position
        $this->seek($this->pointer + 1);
    }

    /**
     * Checks whether offset exists in the resultset
     *
     * @param mixed $index
     *
     * @return bool
     */
    public function offsetExists(mixed $index): bool
    {
        return $index < $this->count;
    }

    /**
     * Gets row in a specific position of the resultset
     *
     * @param mixed $index
     *
     * @return mixed
     * @throws Exception
     */
    public function offsetGet(mixed $index): mixed
    {
        if ($index >= $this->count) {
            throw new Exception("The index does not exist in the cursor");
        }

        /**
         * Move the cursor to the specific position
         */
        $this->seek($index);

        return $this->current();
    }

    /**
     * Resultsets cannot be changed. It has only been implemented to meet the
     * definition of the ArrayAccess interface
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     * @throws Exception
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new Exception("Cursor is an immutable ArrayAccess object");
    }

    /**
     * Resultsets cannot be changed. It has only been implemented to meet the
     * definition of the ArrayAccess interface
     *
     * @param mixed $offset
     *
     * @return void
     * @throws Exception
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new Exception("Cursor is an immutable ArrayAccess object");
    }

    /**
     * Rewinds resultset to its beginning
     *
     * @return void
     */
    final public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * Changes the internal pointer to a specific position in the resultset.
     * Set the new position if required, and then set this->row
     *
     * @param mixed $position
     *
     * @return void
     */
    final public function seek(mixed $position): void
    {
        if ($this->pointer != $position || $this->row === null) {
            if (is_array($this->rows)) {
                /**
                 * All rows are in memory
                 */
                if (isset($this->rows[$position])) {
                    $this->row = $this->rows[$position];
                }

                $this->pointer   = $position;
                $this->activeRow = null;

                return;
            }

            /**
             * Fetch from PDO one-by-one.
             */
            $result = $this->result;

            if ($this->row === null && $this->pointer === 0) {
                /**
                 * Fresh result-set: Query was already executed in
                 * `Model\Query::executeSelect()`
                 * The first row is available with fetch
                 */
                $this->row = $result->fetch();
            }

            if ($this->pointer > $position) {
                /**
                 * Current pointer is ahead requested position: e.g. request a
                 * previous row. It is not possible to rewind. Re-execute query
                 * with dataSeek.
                 */
                $result->dataSeek($position);

                $this->row     = $result->fetch();
                $this->pointer = $position;
            }

            while ($this->pointer < $position) {
                /**
                 * Requested position is greater than current pointer, seek
                 * forward until the requested position is reached. We do not
                 * need to re-execute the query!
                 */
                $this->row = $result->fetch();
                $this->pointer++;
            }

            $this->pointer   = $position;
            $this->activeRow = null;
        }
    }

    /**
     * Sets the hydration mode in the resultset
     *
     * @param int $hydrateMode
     *
     * @return ResultsetInterface
     */
    public function setHydrateMode(int $hydrateMode): ResultsetInterface
    {
        $this->hydrateMode = $hydrateMode;

        return $this;
    }

    /**
     * Set if the resultset is fresh or an old one cached
     *
     * @param bool $isFresh
     *
     * @return ResultsetInterface
     */
    public function setIsFresh(bool $isFresh): ResultsetInterface
    {
        $this->isFresh = $isFresh;

        return $this;
    }

    /**
     * Updates every record in the resultset
     *
     * @param mixed        $data
     * @param Closure|null $conditionCallback
     *
     * @return bool
     * @throws Exception
     */
    public function update(
        mixed $data,
        Closure | null $conditionCallback = null
    ): bool {
        $connection  = null;
        $transaction = false;

        $this->rewind();

        while ($this->valid()) {
            $record = $this->current();

            if ($transaction === false) {
                /**
                 * We only can update resultsets if every element is a complete object
                 */
                if (!method_exists($record, "getWriteConnection")) {
                    throw new Exception("The returned record is not valid");
                }

                $connection  = $record->getWriteConnection();
                $transaction = true;

                $connection->begin();
            }

            /**
             * Perform additional validations
             */
            if (
                is_object($conditionCallback) &&
                call_user_func_array($conditionCallback, [$record]) === false
            ) {
                $this->next();

                continue;
            }

            $record->assign($data);

            /**
             * Try to update the record
             */
            if (!$record->save()) {
                /**
                 * Get the messages from the record that produce the error
                 */
                $this->errorMessages = $record->getMessages();

                /**
                 * Rollback the transaction
                 */
                $connection->rollback();

                $transaction = false;

                break;
            }

            $this->next();
        }

        /**
         * Commit the transaction
         */
        if ($transaction === true) {
            $connection->commit();
        }

        return $transaction;
    }

    /**
     * Check whether internal resource has rows to fetch
     *
     * @return bool
     */
    public function valid(): bool
    {
        return $this->pointer < $this->count;
    }
}
