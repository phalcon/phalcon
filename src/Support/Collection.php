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
use function mb_strtolower;
use function serialize;
use function settype;
use function unserialize;

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
 * @property array $data
 * @property bool  $insensitive
 * @property array $lowerKeys
 * @property bool  $strictNull
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
     */
    public function __construct(
        array $data = [],
        protected bool $insensitive = true,
        protected bool $strictNull = false
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
     * Count elements of an object
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->data);
    }

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
            settype($value, $cast);
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
     * Returns the keys (insensitive or not) of the collection
     *
     * @param bool $insensitive Case-insensitive keys (default: true)
     *
     * @return array<int|string, mixed>
     */
    public function getKeys(bool $insensitive = true): array
    {
        if ($insensitive) {
            return array_keys($this->lowerKeys);
        }

        return array_keys($this->data);
    }

    /**
     * Returns the values of the internal array
     *
     * @return array<int|string, mixed>
     */
    public function getValues(): array
    {
        return array_values($this->data);
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

    public function last(): mixed
    {
        if (empty($this->data)) {
            return null;
        }

        return $this->data[array_key_last($this->data)];
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
     * Returns the object in an array format
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
     * @param string $element Name of the element
     * @param mixed  $value   Value to store for the element
     */
    protected function setData(string $element, mixed $value): void
    {
        $key                   = $this->processKey($element);
        $this->data[$element]  = $value;
        $this->lowerKeys[$key] = $element;
    }
}
