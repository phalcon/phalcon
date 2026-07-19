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

namespace Phalcon\Http\Request\Bag;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Phalcon\Http\Request\Exceptions\NullKeyException;
use Traversable;

use function array_key_exists;
use function count;
use function is_array;

/**
 * Shared base for the HTTP request bags. A bag is a string- or integer-keyed value store
 * backed by a raw array, exposing `get/has/set/remove/all` plus typed readers
 * for cast-with-default access.
 *
 * Two protected hooks (`normalizeKey`, `normalizeItems`) let subclasses
 * change key handling without restating the surface.
 *
 * The ArrayAccess append form (`$bag[] = $value`) is rejected with a
 * NullKeyException: the append form supplies no explicit key, so the write
 * could never be addressed by the caller.
 */
abstract class AbstractBag implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @var array
     */
    protected array $items;

    /**
     * AbstractBag constructor.
     *
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $this->normalizeItems($items);
    }

    /**
     * Returns all the elements of the bag
     *
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Returns the number of elements in the bag
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Returns an element of the bag, or the default value if it is not set
     *
     * @param int|string $key
     * @param mixed  $defaultValue
     *
     * @return mixed
     */
    public function get(int | string $key, mixed $defaultValue = null): mixed
    {
        return $this->items[$this->normalizeKey($key)] ?? $defaultValue;
    }

    /**
     * Returns an element of the bag as an array. The default value is
     * returned if the element is not set or is not an array
     *
     * @param int|string $key
     * @param array  $defaultValue
     *
     * @return array
     */
    public function getArray(int | string $key, array $defaultValue = []): array
    {
        $value = $this->items[$this->normalizeKey($key)] ?? null;

        return is_array($value) ? $value : $defaultValue;
    }

    /**
     * Returns an element of the bag cast to bool, or the default value if
     * it is not set
     *
     * @param int|string $key
     * @param bool   $defaultValue
     *
     * @return bool
     */
    public function getBool(int | string $key, bool $defaultValue = false): bool
    {
        $key = $this->normalizeKey($key);

        return isset($this->items[$key])
            ? (bool)$this->items[$key]
            : $defaultValue;
    }

    /**
     * Returns an element of the bag cast to float, or the default value if
     * it is not set
     *
     * @param int|string $key
     * @param float  $defaultValue
     *
     * @return float
     */
    public function getFloat(int | string $key, float $defaultValue = 0.0): float
    {
        $key = $this->normalizeKey($key);

        return isset($this->items[$key])
            ? (float)$this->items[$key]
            : $defaultValue;
    }

    /**
     * Returns an element of the bag cast to int, or the default value if
     * it is not set
     *
     * @param int|string $key
     * @param int    $defaultValue
     *
     * @return int
     */
    public function getInt(int | string $key, int $defaultValue = 0): int
    {
        $key = $this->normalizeKey($key);

        return isset($this->items[$key])
            ? (int)$this->items[$key]
            : $defaultValue;
    }

    /**
     * Returns the iterator of the bag
     *
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Returns an element of the bag cast to string, or the default value if
     * it is not set
     *
     * @param int|string $key
     * @param string $defaultValue
     *
     * @return string
     */
    public function getString(int | string $key, string $defaultValue = ''): string
    {
        $key = $this->normalizeKey($key);

        return isset($this->items[$key])
            ? (string)$this->items[$key]
            : $defaultValue;
    }

    /**
     * Checks whether an element exists in the bag
     *
     * @param int|string $key
     *
     * @return bool
     */
    public function has(int | string $key): bool
    {
        return array_key_exists($this->normalizeKey($key), $this->items);
    }

    /**
     * Whether an offset exists
     *
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has((string)$offset);
    }

    /**
     * Offset to retrieve
     *
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get((string)$offset);
    }

    /**
     * Offset to set
     *
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     *
     * @throws NullKeyException When the offset is null (append form)
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (null === $offset) {
            throw new NullKeyException();
        }

        $this->set((string)$offset, $value);
    }

    /**
     * Offset to unset
     *
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->remove((string)$offset);
    }

    /**
     * Removes an element from the bag
     *
     * @param int|string $key
     *
     * @return void
     */
    public function remove(int | string $key): void
    {
        unset($this->items[$this->normalizeKey($key)]);
    }

    /**
     * Sets an element in the bag
     *
     * @param int|string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function set(int | string $key, mixed $value): void
    {
        $this->items[$this->normalizeKey($key)] = $value;
    }

    /**
     * Normalizes the items at construction time. Identity in the base;
     * subclasses can override it to normalize keys
     *
     * @param array $items
     *
     * @return array
     */
    protected function normalizeItems(array $items): array
    {
        return $items;
    }

    /**
     * Normalizes a key for lookups and writes. Identity in the base;
     * subclasses can override it to change key handling
     *
     * @param int|string $key
     *
     * @return int|string
     */
    protected function normalizeKey(int | string $key): int | string
    {
        return $key;
    }
}
