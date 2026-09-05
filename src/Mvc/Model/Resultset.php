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
use Phalcon\Mvc\Model\Exceptions\CursorIsImmutable;
use Phalcon\Mvc\Model\Exceptions\IndexNotInCursor;
use Phalcon\Mvc\Model\Exceptions\InvalidResultsetCacheService;
use Phalcon\Mvc\Model\Exceptions\InvalidReturnedRecord;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Support\Settings;
use SeekableIterator;

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
 * $invoices = Invoices::find(
 *     [
 *         "inv_status_flag = 1",
 *         "order" => "inv_title",
 *     ]
 * );
 *
 * foreach ($invoices as invoice) {
 *     echo invoice->inv_title, "\n";
 * }
 *
 * // Using a while
 * $invoices = Invoices::find(
 *     [
 *         "inv_status_flag = 1",
 *         "order" => "inv_title",
 *     ]
 * );
 *
 * $invoices->rewind();
 *
 * while ($invoices->valid()) {
 *     $invoice = $invoices->current();
 *
 *     echo $invoice->inv_title, "\n";
 *
 *     $invoices->next();
 * }
 * ```
 *
 * @template TKey of int
 * @template TValue
 * @implements Iterator<TKey, TValue>
 * @implements ArrayAccess<TKey, TValue>
 * @implements SeekableIterator<TKey, TValue>
 */
abstract class Resultset implements
    ResultsetInterface,
    Iterator,
    SeekableIterator,
    Countable,
    ArrayAccess,
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
     * @var mixed
     */
    protected mixed $cache = null;

    /**
     * Number of rows, or null while it has not been worked out yet. Resolved
     * lazily by count() - asking the driver up front costs SQLite an extra
     * statement on every single result-set.
     *
     * @var int|null
     */
    protected int | null $count = null;

    /**
     * @var array
     *
     * @phpstan-var array<array-key, MessageInterface>
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
     * Phalcon\Db\ResultInterface or false for empty resultset
     *
     * @var bool|ResultInterface
     *
     * @phpstan-var bool|\Phalcon\Contracts\Db\Result|null
     */
    protected mixed $result = null;

    /**
     * @var mixed|null
     */
    protected mixed $row = null;

    /**
     * @var array|null
     *
     * @phpstan-var array<array-key, mixed>|null
     */
    protected array | null $rows = null;

    /**
     * Phalcon\Mvc\Model\Resultset constructor
     *
     * @param false|ResultInterface $result
     * @param mixed|null            $cache
     *
     * @throws Exception
     *
     * @phpstan-param \Phalcon\Contracts\Db\Result|false|null $result
     */
    public function __construct(
        mixed $result,
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
            /**
             * A cache service is an object.
             *
             * @var object $cache
             */
            if (
                !is_a($cache, 'Phalcon\\Cache\\CacheInterface') &&
                !is_a($cache, 'Psr\\SimpleCache\\CacheInterface')
            ) {
                throw new InvalidResultsetCacheService();
            }


            $this->cache = $cache;
        }

        /**
         * Do the fetch using only associative indexes
         */
        $result->setFetchMode(Enum::FETCH_ASSOC);

        /**
         * Consume the first row. The statement has already been executed by
         * `Model\Query::executeSelect()`, so this costs no extra round trip,
         * and it is the only way to tell an empty result-set from a populated
         * one without asking the driver for a row count - which SQLite can
         * only answer by running a second statement.
         */
        $this->row = $result->fetch();

        /**
         * Empty result-set
         */
        if (!is_array($this->row)) {
            $this->count = 0;
            $this->rows  = [];

            return;
        }

        /**
         * Small result-sets with less equals 32 rows are fetched at once.
         * The count is only worth asking for when the prefetch is switched on,
         * which it is not by default.
         */
        $prefetchRecords = (int)Settings::get("orm.resultset_prefetch_records");

        if ($prefetchRecords > 0 && $this->count() <= $prefetchRecords) {
            $this->materialize();
        }
    }

    /**
     * Counts how many rows are in the resultset
     *
     * @return int
     */
    final public function count(): int
    {
        if (null === $this->count) {
            if (is_array($this->rows)) {
                $this->count = count($this->rows);
            } elseif (is_object($this->result)) {
                $this->count = (int) $this->result->numRows();
            } else {
                $this->count = 0;
            }
        }

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
            /** @var ModelInterface|Row $record */
            $record = $this->current();

            if ($transaction === false) {
                /**
                 * We only can delete resultsets if every element is a complete object
                 */
                if (!method_exists($record, "getWriteConnection")) {
                    throw new InvalidReturnedRecord();
                }

                /** @var ModelInterface $record */
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
            /** @var ModelInterface $record */
            if (!$record->delete()) {
                /**
                 * Get the messages from the record that produce the error
                 */
                $this->errorMessages = $record->getMessages();

                /**
                 * Rollback the transaction
                 */
                /** @var \Phalcon\Db\Adapter\AdapterInterface $connection */
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
            /** @var \Phalcon\Db\Adapter\AdapterInterface $connection */
            $connection->commit();
        }

        $this->refresh();

        return $result;
    }

    /**
     * Filters a resultset returning only those the developer requires
     *
     *```php
     * $filtered = $invoices->filter(
     *     function ($invoice) {
     *         if ($invoice->inv_id < 3) {
     *             return $invoice;
     *         }
     *     }
     * );
     *```
     *
     * @param mixed $filter
     *
     * @return array|ModelInterface[]
     *
     * @phpstan-return list<array<array-key, mixed>|object>
     */
    public function filter(mixed $filter): array
    {
        $records = [];

        $this->rewind();

        while ($this->valid()) {
            $record = $this->current();

            /**
             * The caller passes a callable that takes one record.
             *
             * @var callable(mixed): mixed $filter
             */
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
     * $model = new Invoices();
     * $manager = $model->getModelsManager();
     *
     * // \Invoices
     * $manager->createQuery('SELECT * FROM Invoices')
     *         ->execute()
     *         ->getFirst();
     *
     * // \Phalcon\Mvc\Model\Row
     * $manager->createQuery('SELECT r.inv_id FROM Invoices AS r')
     *         ->execute()
     *         ->getFirst();
     *
     * // NULL
     * $manager->createQuery('SELECT r.inv_id FROM Invoices AS r WHERE r.inv_title = "NON-EXISTENT"')
     *         ->execute()
     *         ->getFirst();
     * ```
     *
     * @return ModelInterface|Row|null
     */
    public function getFirst()
    {
        $this->seek(0);

        /**
         * Positioning at the first row already tells us whether there is one,
         * so there is no need to work out the whole count
         */
        if (!$this->valid()) {
            return null;
        }

        /** @var ModelInterface|Row|null */
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
     * @return ModelInterface|Row|null
     */
    public function getLast(): \Phalcon\Mvc\Model\Row | \Phalcon\Mvc\ModelInterface | null
    {
        $count = $this->count();

        if ($count == 0) {
            return null;
        }

        $this->seek($count - 1);

        /** @var ModelInterface|Row|null */
        return $this->current();
    }

    /**
     * Returns the error messages produced by a batch operation
     *
     * @return array|MessageInterface[]
     *
     * @phpstan-return array<array-key, MessageInterface>
     */
    public function getMessages(): array
    {
        return $this->errorMessages;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
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
     * Returns serialized model objects as array for json_encode.
     * Calls jsonSerialize on each object if present
     *
     *```php
     * $invoices = Invoices::find();
     *
     * echo json_encode($invoices);
     *```
     *
     * @return array
     *
     * @phpstan-return array<array-key, mixed>
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
     * @return TKey|null
     */
    public function key(): int | null
    {
        if (!$this->valid()) {
            return null;
        }

        /**
         * The pointer is the key of the row, and `TKey` is bound to `int`.
         */
        /** @var TKey $pointer */
        $pointer = $this->pointer;

        return $pointer;
    }

    /**
     * Fetches every remaining row of the underlying cursor into memory,
     * turning the resultset into TYPE_RESULT_FULL.
     *
     * Free when called before the cursor has been advanced: the statement has
     * already been executed by Model\Query::executeSelect() and only the row
     * the constructor consumed is missing from the cursor, so no re-execution
     * takes place. Idempotent.
     *
     * @return void
     */
    public function materialize(): void
    {
        if (is_array($this->rows)) {
            return;
        }

        $result = $this->result;

        if (!is_object($result)) {
            $this->rows = [];

            return;
        }

        if ($this->pointer > 0) {
            /**
             * The cursor has been advanced past the first row, so it has to be
             * replayed from the beginning
             */
            $result->execute();

            $records = $result->fetchAll(Enum::FETCH_ASSOC);
        } else {
            /**
             * The cursor sits right behind the row the constructor consumed, so
             * the whole set is that row followed by whatever is left
             */
            $records = $result->fetchAll(Enum::FETCH_ASSOC);

            if (!is_array($records)) {
                $records = [];
            }

            if (is_array($this->row)) {
                $records = array_merge([$this->row], $records);
            }
        }

        $this->row  = null;
        $this->rows = is_array($records) ? $records : [];
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
     *
     * @phpstan-param int $index
     */
    public function offsetExists(mixed $index): bool
    {
        return $index < $this->count();
    }

    /**
     * Gets row in a specific position of the resultset
     *
     * @param mixed $index
     *
     * @return mixed
     * @throws Exception
     *
     * @phpstan-param int $index
     */
    public function offsetGet(mixed $index): mixed
    {
        if ($index >= $this->count()) {
            throw new IndexNotInCursor();
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
        throw new CursorIsImmutable();
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
        throw new CursorIsImmutable();
    }

    /**
     * @return bool
     */
    public function refresh(): bool
    {
        /**
         * 'false' is given as result for empty result-sets
         */
        if (!is_object($this->result)) {
            $this->count = 0;
            $this->rows  = [];

            return true;
        }

        $result  = $this->result;
        $success = $result->execute();
        if ($success === false) {
            return false;
        }

        /**
         * The statement has been replayed, so everything derived from the
         * previous run has to go - including the cursor position
         */
        $this->isFresh   = true;
        $this->count     = null;
        $this->rows      = null;
        $this->row       = null;
        $this->activeRow = null;
        $this->pointer   = 0;

        /**
         * Consume the first row to tell an empty result-set from a populated
         * one, the same way the constructor does
         */
        $this->row = $result->fetch();

        /**
         * Empty result-set
         */
        if (!is_array($this->row)) {
            $this->count = 0;
            $this->rows  = [];

            return true;
        }

        /**
         * Small result-sets with less equals 32 rows are fetched at once
         */
        $prefetchRecords = (int)Settings::get("orm.resultset_prefetch_records");

        if ($prefetchRecords > 0 && $this->count() <= $prefetchRecords) {
            $this->materialize();
        }

        return true;
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
     *
     * @phpstan-param int $position
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
                } else {
                    /**
                     * Past the end - the previous row must not be left behind
                     * as the current one
                     */
                    $this->row = false;
                }

                $this->pointer   = $position;
                $this->activeRow = null;

                return;
            }

            /**
             * Fetch from PDO one-by-one.
             */
            /** @var \Phalcon\Contracts\Db\Result $result */
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
     *
     * @phpstan-param array<array-key, mixed> $data
     */
    public function update(
        mixed $data,
        Closure | null $conditionCallback = null
    ): bool {
        $connection  = null;
        $transaction = false;

        $this->rewind();

        while ($this->valid()) {
            /** @var ModelInterface|Row $record */
            $record = $this->current();

            if ($transaction === false) {
                /**
                 * We only can update resultsets if every element is a complete object
                 */
                if (!method_exists($record, "getWriteConnection")) {
                    throw new InvalidReturnedRecord();
                }

                /** @var ModelInterface $record */
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

            /** @var ModelInterface $record */
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
                /** @var \Phalcon\Db\Adapter\AdapterInterface $connection */
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
            /** @var \Phalcon\Db\Adapter\AdapterInterface $connection */
            $connection->commit();
        }

        $this->refresh();

        return $transaction;
    }

    /**
     * Check whether internal resource has rows to fetch
     *
     * Driven by the row the cursor is parked on rather than by the count, so
     * that a plain traversal never has to ask the driver how many rows there
     * are - on SQLite that answer costs a second statement.
     *
     * @return bool
     */
    public function valid(): bool
    {
        /**
         * Nothing has been fetched yet, or the rows have just been pulled into
         * memory - position the cursor before reporting
         */
        if ($this->row === null) {
            $this->seek($this->pointer);
        }

        return is_array($this->row);
    }
}
