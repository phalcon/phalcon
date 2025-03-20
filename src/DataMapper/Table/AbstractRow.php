<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by AtlasPHP
 *
 * @link    https://github.com/atlasphp/Atlas.Table
 * @license https://github.com/atlasphp/Atlas.Table/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\DataMapper\Table;

use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;
use Phalcon\DataMapper\Table\Exception\ImmutableAfterDeletedException;
use Phalcon\DataMapper\Table\Exception\InvalidOptionException;
use Phalcon\DataMapper\Table\Exception\PropertyDoesNotExistException;
use Traversable;

use function array_key_exists;
use function is_bool;

/**
 * @template TKey of string
 * @template TValue
 * @implements IteratorAggregate<TKey, TValue>
 */
abstract class AbstractRow implements IteratorAggregate, JsonSerializable
{
    public const DELETE = 'DELETE';
    public const INSERT = 'INSERT';
    public const SELECT = 'SELECT';
    public const UPDATE = 'UPDATE';
    /**
     * @var array<string, mixed>
     */
    protected array $store = [];
    /**
     * @var array<string, mixed>
     */
    private array $initStore;
    /**
     * @var bool
     */
    private bool $isClean = true;

    /**
     * @var bool
     */
    private bool $isDelete = false;
    /**
     * @var string|null
     */
    private string | null $lastAction = null;

    /**
     * @param array $columns
     *
     * @throws ImmutableAfterDeletedException
     * @throws PropertyDoesNotExistException
     */
    public function __construct(array $columns = [])
    {
        $this->init($columns);
        $this->initStore = $this->getCopy();
    }

    /**
     * Returns the value of a property accessed directly and throws an exception
     * if it does not exist
     *
     * @param string $column
     * @param array  $filters
     *
     * @return mixed
     * @throws PropertyDoesNotExistException
     */
    public function get(string $column, array $filters = []): mixed
    {
        $this->assertHas($column);

        return $this->store[$column];
    }

    /**
     * Returns an array copy of the stored data
     *
     * @return array<string, mixed>
     */
    public function getCopy(): array
    {
        return $this->store;
    }

    /**
     * Returns the diff between the initial values (object instantiation) and
     * the current ones
     *
     * @return array<string, mixed>
     * @throws PropertyDoesNotExistException
     */
    public function getDiff(): array
    {
        $diff = [];
        foreach ($this->initStore as $column => $old) {
            if ($this->isModified($column, $old)) {
                $diff[$column] = $this->get($column);
            }
        }

        return $diff;
    }

    /**
     * Returns the data array that the object was initialized with
     *
     * @return array<string, mixed>
     */
    public function getInit(): array
    {
        return $this->initStore;
    }

    /**
     * Returns the array iterator
     *
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->getCopy());
    }

    /**
     * Return the last action
     *
     * @return string|null
     */
    public function getLastAction(): string | null
    {
        return $this->lastAction;
    }

    /**
     * Return the next action
     *
     * @return string|null
     */
    public function getNextAction(): string | null
    {
        if (null === $this->lastAction) {
            return $this->isDelete ? null : static::INSERT;
        }

        if (true === $this->isDelete) {
            return static::DELETE;
        }

        if (true === $this->isClean) {
            return null;
        }

        foreach ($this->initStore as $column => $old) {
            if ($this->isModified($column, $old)) {
                return static::UPDATE;
            }

            $this->isClean = true;
        }

        return null;
    }

    /**
     * @param string $column
     *
     * @return bool
     */
    public function has(string $column): bool
    {
        return array_key_exists($column, $this->store);
    }

    /**
     * Set the column properties of this object and its values
     *
     * @param array $columns
     *
     * @return void
     * @throws ImmutableAfterDeletedException
     * @throws PropertyDoesNotExistException
     */
    public function init(array $columns): void
    {
        foreach ($columns as $column => $value) {
            $this->set($column, $value);
        }
    }

    /**
     * Returns the array for jsonSerialize
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->getCopy();
    }

    /**
     * Unsets a column value
     *
     * @param string $column
     *
     * @return void
     * @throws ImmutableAfterDeletedException
     * @throws PropertyDoesNotExistException
     */
    public function remove(string $column): void
    {
        if (self::DELETE === $this->lastAction) {
            throw new ImmutableAfterDeletedException(static::class, $column);
        }

        $this->assertHas($column);
        $this->store[$column] = null;
    }

    /**
     * @param string $column
     * @param mixed  $value
     *
     * @return static
     * @throws ImmutableAfterDeletedException
     * @throws PropertyDoesNotExistException
     */
    public function set(string $column, mixed $value): static
    {
        if (self::DELETE === $this->lastAction) {
            throw new ImmutableAfterDeletedException(static::class, $column);
        }

        $this->assertHas($column);

        $this->store[$column] = $value;
        $this->isClean        = false;

        return $this;
    }

    /**
     * Sets the delete flag
     *
     * @param bool $delete
     *
     * @return static
     */
    public function setDelete(bool $delete): static
    {
        $this->isDelete = $delete;

        return $this;
    }

    /**
     * Sets the last action
     *
     * @param string $lastAction
     *
     * @return static
     * @throws InvalidOptionException
     */
    public function setLastAction(string $lastAction): static
    {
        $options = [
            static::SELECT,
            static::INSERT,
            static::UPDATE,
            static::DELETE,
        ];

        if (true !== in_array($lastAction, $options)) {
            throw new InvalidOptionException($lastAction);
        }

        $this->lastAction = $lastAction;
        $this->initStore  = $this->getCopy();
        $this->isClean    = true;

        return $this;
    }

    /**
     * Checks if a property exists and if not throws an exception
     *
     * @param string $column
     *
     * @return void
     * @throws PropertyDoesNotExistException
     */
    protected function assertHas(string $column): void
    {
        if (true !== $this->has($column)) {
            throw new PropertyDoesNotExistException(static::class, $column);
        }
    }

    /**
     * @param string $column
     * @param mixed  $old
     *
     * @return bool
     */
    protected function isModified(string $column, mixed $old): bool
    {
        $new = $this->store[$column];
        $old = is_bool($old) ? (int)$old : $old;
        $new = is_bool($new) ? (int)$new : $new;

        return (is_numeric($old) && is_numeric($new))
            ? $old != $new   // numeric, compare loosely
            : $old !== $new; // not numeric, compare strictly
    }
}
