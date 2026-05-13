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

namespace Phalcon\Support;

use Countable;
use Generator;
use InvalidArgumentException;
use JsonSerializable;
use Phalcon\Support\Collection\CollectionInterface;
use Phalcon\Support\Collection\Traits\ArrayAccessTrait;
use Phalcon\Support\Collection\Traits\GetSetHasTrait;
use Phalcon\Support\Traits\JsonTrait as BaseJsonTrait;
use Phalcon\Traits\Php\JsonTrait;

use function array_key_first;
use function array_key_last;
use function array_keys;
use function array_values;
use function arsort;
use function asort;
use function get_debug_type;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_string;
use function mb_strtolower;
use function method_exists;
use function property_exists;
use function serialize;
use function settype;
use function uasort;
use function unserialize;

use const SORT_ASC;
use const SORT_DESC;

/**
 * `Phalcon\Collection` is a supercharged object-oriented array. It implements:
 * - [ArrayAccess](https://www.php.net/manual/en/class.arrayaccess.php)
 * - [Countable](https://www.php.net/manual/en/class.countable.php)
 * - [IteratorAggregate](https://www.php.net/manual/en/class.iteratoraggregate.php)
 * - [JsonSerializable](https://www.php.net/manual/en/class.jsonserializable.php)
 *
 * It can be used in any part of the application that needs collection of data
 * Such implementations are for instance accessing globals `$_GET`, `$_POST`
 * etc.
 *
 * @phpstan-template T
 *
 * @property array       $data
 * @property bool        $insensitive
 * @property array       $lowerKeys
 * @property bool        $strictNull
 * @property string|null $type
 */
class Collection implements
    CollectionInterface,
    Countable,
    JsonSerializable
{
    use ArrayAccessTrait;
    use BaseJsonTrait;
    use GetSetHasTrait;
    use JsonTrait;

    /**
     * @var array<int|string, mixed>
     */
    protected array $data = [];

    /**
     * @var array<int|string, mixed>
     */
    protected array $lowerKeys = [];

    /**
     * Collection constructor.
     *
     * @param array<int|string, mixed> $data
     * @param bool                     $insensitive
     * @param bool                     $strictNull
     * @param string|null              $type
     */
    public function __construct(
        array $data = [],
        protected bool $insensitive = true,
        protected bool $strictNull = false,
        protected string | null $type = null,
    ) {
        $this->init($data);
    }

    /**
     * Returns the state of the collection for serialization, including
     * configuration flags so the round-trip restores full state.
     *
     * @return array
     */
    public function __serialize(): array
    {
        return [
            'data'        => $this->data,
            'insensitive' => $this->insensitive,
            'strictNull'  => $this->strictNull,
            'type'        => $this->type,
        ];
    }

    /**
     * Restores the collection state. Accepts both the structured format
     * emitted by __serialize() and the legacy flat-array format for BC
     * with previously serialized data.
     *
     * @param array $data
     *
     * @return void
     */
    public function __unserialize(array $data): void
    {
        if (isset($data['data']) && is_array($data['data'])) {
            $this->insensitive = (bool) ($data['insensitive'] ?? true);
            $this->strictNull  = (bool) ($data['strictNull'] ?? false);
            $this->type        = $data['type'] ?? null;
            $this->init($data['data']);

            return;
        }

        $this->init($data);
    }

    /**
     * Clears the internal collection
     */
    public function clear(): void
    {
        $this->data      = [];
        $this->lowerKeys = [];
    }

    /**
     * Returns the values from a single property/method extracted from every
     * item in the collection, keyed by the original collection key.
     *
     * @param string $propertyOrMethod
     *
     * @return array<int|string, mixed>
     */
    public function column(string $propertyOrMethod): array
    {
        $result = [];

        foreach ($this->data as $key => $value) {
            $result[$key] = $this->extractValue($value, $propertyOrMethod);
        }

        return $result;
    }

    /**
     * Count elements of an object
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Invokes the callback for every item in the collection. Returns the
     * collection itself to allow chaining.
     *
     * @phpstan-param callable(T, array-key): mixed $callback
     *
     * @param callable $callback
     *
     * @return static
     */
    public function each(callable $callback): static
    {
        foreach ($this->data as $key => $value) {
            $callback($value, $key);
        }

        return $this;
    }

    /**
     * Returns a new collection of items for which the callback returns true.
     * Keys are preserved.
     *
     * @phpstan-param  callable(T, array-key): bool $callback
     * @phpstan-return static<T>
     *
     * @param callable $callback
     *
     * @return static
     */
    public function filter(callable $callback): static
    {
        $result = [];

        foreach ($this->data as $key => $value) {
            if ($callback($value, $key)) {
                $result[$key] = $value;
            }
        }

        return $this->cloneEmpty($result);
    }

    /**
     * Returns the first value in the collection, or null if empty.
     *
     * @phpstan-return T|null
     *
     * @return mixed
     */
    public function first(): mixed
    {
        if (empty($this->data)) {
            return null;
        }

        return $this->data[array_key_first($this->data)];
    }

    /**
     * Get the element from the collection
     *
     * @phpstan-return T|mixed
     *
     * @param string      $element
     * @param mixed|null  $defaultValue
     * @param string|null $cast
     *
     * @return mixed
     */
    public function get(
        string $element,
        mixed $defaultValue = null,
        string | null $cast = null
    ): mixed {
        $element = $this->processKey($element);

        /**
         * If the key is not set, return the default value
         */
        if (!isset($this->lowerKeys[$element])) {
            return $defaultValue;
        }

        $key   = $this->lowerKeys[$element];
        $value = $this->data[$key];

        /**
         * If the key is set and is `null` then return the default
         * value also. This aligns with 3.x behavior
         */
        if (null === $value && false === $this->strictNull) {
            return $defaultValue;
        }

        if (null !== $cast) {
            if (
                'array' === $cast
                && is_object($value)
                && method_exists($value, 'toArray')
            ) {
                $value = $value->toArray();
            } else {
                settype($value, $cast);
            }
        }

        return $value;
    }

    /**
     * Returns the generator of the class
     *
     * @return Generator<int|string, mixed>
     */
    public function getIterator(): Generator
    {
        foreach ($this->data as $key => $value) {
            yield $key => $value;
        }
    }

    /**
     * Returns the keys (insensitive or not) of the collection.
     *
     * @deprecated Use {@see self::keys()} instead. Will be removed in a future major release.
     *
     * @param bool $insensitive Case-insensitive keys (default: true)
     *
     * @return array<int|string, mixed>
     */
    public function getKeys(bool $insensitive = true): array
    {
        return $this->keys($insensitive);
    }

    /**
     * Returns the configured runtime type guard, or null if none.
     *
     * @return string|null
     */
    public function getType(): string | null
    {
        return $this->type;
    }

    /**
     * Returns the values of the internal array.
     *
     * @deprecated Use {@see self::values()} instead. Will be removed in a future major release.
     *
     * @return array<int|string, mixed>
     */
    public function getValues(): array
    {
        return $this->values();
    }

    /**
     * Get the element from the collection
     *
     * @param string $element Name of the element
     *
     * @return bool
     */
    public function has(string $element): bool
    {
        $element = $this->processKey($element);

        return isset($this->lowerKeys[$element]);
    }

    /**
     * Initialize internal array
     *
     * @param array<int|string, mixed> $data Array to initialize the collection with
     */
    public function init(array $data = []): void
    {
        foreach ($data as $key => $value) {
            $this->setData($key, $value);
        }
    }

    /**
     * Return if the collection is empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return array<int|string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_map(
            function ($value) {
                return $this->checkSerializable($value);
            },
            $this->data
        );
    }

    /**
     * Returns the keys (insensitive or not) of the collection.
     *
     * @param bool $insensitive Case-insensitive keys (default: true)
     *
     * @return array<int|string, mixed>
     */
    public function keys(bool $insensitive = true): array
    {
        if ($insensitive) {
            return array_keys($this->lowerKeys);
        }

        return array_keys($this->data);
    }

    /**
     * Returns the last value in the collection, or null if empty.
     *
     * @phpstan-return T|null
     *
     * @return mixed
     */
    public function last(): mixed
    {
        if (empty($this->data)) {
            return null;
        }

        return $this->data[array_key_last($this->data)];
    }

    /**
     * Returns a new collection with the callback applied to every value.
     * Keys are preserved.
     *
     * @phpstan-param  callable(T, array-key): mixed $callback
     * @phpstan-return static<mixed>
     *
     * @param callable $callback
     *
     * @return static
     */
    public function map(callable $callback): static
    {
        $result = [];

        foreach ($this->data as $key => $value) {
            $result[$key] = $callback($value, $key);
        }

        return $this->cloneEmpty($result);
    }

    /**
     * Reduces the collection to a single value using the callback. The
     * callback receives `($accumulator, $value, $key)`.
     *
     * @phpstan-param callable(mixed, T, array-key): mixed $callback
     *
     * @param callable $callback
     * @param mixed    $initial
     *
     * @return mixed
     */
    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        $accumulator = $initial;

        foreach ($this->data as $key => $value) {
            $accumulator = $callback($accumulator, $value, $key);
        }

        return $accumulator;
    }

    /**
     * Delete the element from the collection
     *
     * @param string $element Name of the element
     */
    public function remove(string $element): void
    {
        if ($this->has($element)) {
            $element = $this->processKey($element);

            $value = $this->lowerKeys[$element];

            unset($this->lowerKeys[$element]);
            unset($this->data[$value]);
        }
    }

    /**
     * Replaces the collection data with a new array, clearing existing data first
     *
     * @phpstan-param array<int|string, mixed> $data
     */
    public function replace(array $data): void
    {
        $this->clear();
        $this->init($data);
    }

    /**
     * BC - delegate to __serialize()
     *
     * @return string|null
     */
    public function serialize(): string | null
    {
        return serialize($this->__serialize());
    }

    /**
     * Set an element in the collection
     *
     * @param string $element Name of the element
     * @param mixed  $value   Value to store for the element
     */
    public function set(string $element, $value): void
    {
        $this->setData($element, $value);
    }

    /**
     * Returns a new collection sorted by value. Keys are preserved. When a
     * callback is supplied, `uasort` is used. Without a callback, the
     * comparison direction is controlled by the `$order` argument
     * (`SORT_ASC` or `SORT_DESC`).
     *
     * @phpstan-return static<T>
     *
     * @param callable|null $callback
     * @param int           $order
     *
     * @return static
     */
    public function sort(callable | null $callback = null, int $order = SORT_ASC): static
    {
        $result = $this->data;

        if (null !== $callback) {
            uasort($result, $callback);
        } elseif (SORT_DESC === $order) {
            arsort($result);
        } else {
            asort($result);
        }

        return $this->cloneEmpty($result);
    }

    /**
     * Returns the object in an array format
     *
     * @phpstan-return array<array-key, T>
     *
     * @return array<int|string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Returns the object in a JSON format
     *
     * The following options are used if none specified for json_encode
     *
     * JSON_HEX_TAG, JSON_HEX_APOS, JSON_HEX_AMP, JSON_HEX_QUOT,
     * JSON_UNESCAPED_SLASHES, JSON_THROW_ON_ERROR
     *
     * @see https://www.ietf.org/rfc/rfc4627.txt
     *
     * @param int $options `
     *
     * @return string
     */
    public function toJson(
        int $options = JSON_HEX_TAG |
        JSON_HEX_AMP |
        JSON_HEX_APOS |
        JSON_HEX_QUOT |
        JSON_UNESCAPED_SLASHES |
        JSON_THROW_ON_ERROR
    ): string {
        $return = $this->phpJsonEncode($this->jsonSerialize(), $options);

        if (false === $return) {
            $return = '';
        }

        return $return;
    }

    /**
     * BC - delegate to __unserialize()
     *
     * @param string $data
     *
     * @return void
     */
    public function unserialize(string $data): void
    {
        $this->__unserialize(unserialize($data));
    }

    /**
     * Returns the values of the internal array.
     *
     * @return array<int|string, mixed>
     */
    public function values(): array
    {
        return array_values($this->data);
    }

    /**
     * Returns a new collection containing only the items whose
     * `propertyOrMethod` strictly equals `$value`.
     *
     * @phpstan-return static<T>
     *
     * @param string $propertyOrMethod
     * @param mixed  $value
     *
     * @return static
     */
    public function where(string $propertyOrMethod, mixed $value): static
    {
        $result = [];

        foreach ($this->data as $key => $item) {
            if ($this->extractValue($item, $propertyOrMethod) === $value) {
                $result[$key] = $item;
            }
        }

        return $this->cloneEmpty($result);
    }

    /**
     * Builds a new collection of the same concrete class, carrying over the
     * configuration (insensitivity, strict-null, type) of the current one.
     *
     * @param array<int|string, mixed> $data
     *
     * @return static
     */
    protected function cloneEmpty(array $data = []): static
    {
        return new static($data, $this->insensitive, $this->strictNull, $this->type);
    }

    /**
     * Extracts a single value from an item. For arrays returns the keyed
     * entry; for objects, prefers a callable method, then a readable
     * property. Returns null when nothing matches.
     *
     * @param mixed  $item
     * @param string $propertyOrMethod
     *
     * @return mixed
     */
    protected function extractValue(mixed $item, string $propertyOrMethod): mixed
    {
        if (is_array($item)) {
            return $item[$propertyOrMethod] ?? null;
        }

        if (is_object($item)) {
            if (method_exists($item, $propertyOrMethod)) {
                return $item->{$propertyOrMethod}();
            }

            if (property_exists($item, $propertyOrMethod)) {
                return $item->{$propertyOrMethod};
            }
        }

        return null;
    }

    /**
     * Checks if we need insensitive keys and if so, converts the element to
     * lowercase
     */
    protected function processKey(string $element): string
    {
        if (true === $this->insensitive) {
            return mb_strtolower($element);
        }

        return $element;
    }

    /**
     * Internal method to set data
     *
     * @phpstan-param T $value
     *
     * @param string $element Name of the element
     * @param mixed  $value   Value to store for the element
     */
    protected function setData(string $element, mixed $value): void
    {
        $this->validateType($value);

        $key                   = $this->processKey($element);
        $this->data[$element]  = $value;
        $this->lowerKeys[$key] = $element;
    }

    /**
     * Validates the value against the configured `$type` guard. When `$type`
     * is null this is a no-op. Scalar tokens (`int`, `string`, `bool`,
     * `float`, `array`, `object`) map to their `is_*` checks; anything else
     * is treated as a class/interface name and tested with `instanceof`.
     *
     * @param mixed $value
     *
     * @throws InvalidArgumentException
     */
    protected function validateType(mixed $value): void
    {
        if (null === $this->type) {
            return;
        }

        $ok = match ($this->type) {
            'int'    => is_int($value),
            'string' => is_string($value),
            'bool'   => is_bool($value),
            'float'  => is_float($value),
            'array'  => is_array($value),
            'object' => is_object($value),
            default  => $value instanceof $this->type,
        };

        if (!$ok) {
            throw new InvalidArgumentException(
                "Value must be of type '" . $this->type
                . "', '" . get_debug_type($value) . "' given"
            );
        }
    }
}
