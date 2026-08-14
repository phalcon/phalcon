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
use Phalcon\Contracts\Http\HttpTypes;
use Phalcon\Http\Request\Exceptions\NullKeyException;
use Stringable;
use Traversable;

use function array_key_exists;
use function count;
use function is_array;

/**
 * Shared base for the HTTP request bags. A bag is a string- or integer-keyed
 * value store backed by a raw array, exposing `get/has/set/remove/all` plus
 * typed readers for cast-with-default access.
 *
 * Two protected hooks (`normalizeKey`, `normalizeItems`) let subclasses
 * change key handling without restating the surface.
 *
 * The ArrayAccess append form (`$bag[] = $value`) is rejected with a
 * NullKeyException: the append form supplies no explicit key, so the write
 * could never be addressed by the caller.
 *
 * @phpstan-import-type http_bag_items from HttpTypes
 *
 * @implements ArrayAccess<int|string, mixed>
 * @implements IteratorAggregate<int|string, mixed>
 */
abstract class AbstractBag implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @phpstan-var http_bag_items
     */
    protected array $items = [];

    /**
     * AbstractBag constructor.
     *
     * @phpstan-param http_bag_items $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $this->normalizeItems($items);
    }

    /**
     * Returns all the elements of the bag
     *
     * @phpstan-return http_bag_items
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Returns the number of elements in the bag
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Returns an element of the bag, or the default value if it is not set
     */
    public function get(int | string $key, mixed $defaultValue = null): mixed
    {
        return $this->items[$this->normalizeKey($key)] ?? $defaultValue;
    }

    /**
     * Returns an element of the bag as an array. The default value is
     * returned if the element is not set or is not an array
     *
     * @phpstan-param  http_bag_items $defaultValue
     * @phpstan-return http_bag_items
     */
    public function getArray(int | string $key, array $defaultValue = []): array
    {
        $value = $this->items[$this->normalizeKey($key)] ?? null;

        return is_array($value) ? $value : $defaultValue;
    }

    /**
     * Returns an element of the bag cast to bool, or the default value if
     * it is not set
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
     */
    public function getFloat(int | string $key, float $defaultValue = 0.0): float
    {
        $key = $this->normalizeKey($key);

        /** @var scalar|null $value */
        $value = $this->items[$key] ?? null;

        return isset($value) ? (float)$value : $defaultValue;
    }

    /**
     * Returns an element of the bag cast to int, or the default value if
     * it is not set
     */
    public function getInt(int | string $key, int $defaultValue = 0): int
    {
        $key = $this->normalizeKey($key);

        /** @var scalar|null $value */
        $value = $this->items[$key] ?? null;

        return isset($value) ? (int)$value : $defaultValue;
    }

    /**
     * Returns the iterator of the bag
     *
     * @return Traversable<int|string, mixed>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Returns an element of the bag cast to string, or the default value if
     * it is not set
     */
    public function getString(int | string $key, string $defaultValue = ''): string
    {
        $key = $this->normalizeKey($key);

        /** @var scalar|Stringable|null $value */
        $value = $this->items[$key] ?? null;

        return isset($value) ? (string)$value : $defaultValue;
    }

    /**
     * Checks whether an element exists in the bag
     */
    public function has(int | string $key): bool
    {
        return array_key_exists($this->normalizeKey($key), $this->items);
    }

    /**
     * Whether an offset exists
     */
    public function offsetExists(mixed $offset): bool
    {
        /** @var scalar|Stringable|null $key */
        $key = $offset;

        return $this->has((string)$key);
    }

    /**
     * Offset to retrieve
     */
    public function offsetGet(mixed $offset): mixed
    {
        /** @var scalar|Stringable|null $key */
        $key = $offset;

        return $this->get((string)$key);
    }

    /**
     * Offset to set
     * @throws NullKeyException When the offset is null (append form)
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (null === $offset) {
            throw new NullKeyException();
        }

        /** @var scalar|Stringable $key */
        $key = $offset;

        $this->set((string)$key, $value);
    }

    /**
     * Offset to unset
     */
    public function offsetUnset(mixed $offset): void
    {
        /** @var scalar|Stringable|null $key */
        $key = $offset;

        $this->remove((string)$key);
    }

    /**
     * Removes an element from the bag
     */
    public function remove(int | string $key): void
    {
        unset($this->items[$this->normalizeKey($key)]);
    }

    /**
     * Sets an element in the bag
     */
    public function set(int | string $key, mixed $value): void
    {
        $this->items[$this->normalizeKey($key)] = $value;
    }

    /**
     * Normalizes the items at construction time. Identity in the base;
     * subclasses can override it to normalize keys
     *
     * @phpstan-param  http_bag_items $items
     * @phpstan-return http_bag_items
     */
    protected function normalizeItems(array $items): array
    {
        return $items;
    }

    /**
     * Normalizes a key for lookups and writes. Identity in the base;
     * subclasses can override it to change key handling
     */
    protected function normalizeKey(int | string $key): int | string
    {
        return $key;
    }
}
