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
use Phalcon\Support\Collection\Traits\SerializableTrait;
use Phalcon\Support\Traits\JsonTrait as BaseJsonTrait;
use Phalcon\Traits\Php\JsonTrait;
use Serializable;

use function array_keys;
use function array_values;
use function mb_strtolower;
use function settype;

/**
 * `Phalcon\Collection` is a supercharged object-oriented array. It implements:
 * - [ArrayAccess](https://www.php.net/manual/en/class.arrayaccess.php)
 * - [Countable](https://www.php.net/manual/en/class.countable.php)
 * - [IteratorAggregate](https://www.php.net/manual/en/class.iteratoraggregate.php)
 * - [JsonSerializable](https://www.php.net/manual/en/class.jsonserializable.php)
 * - [Serializable](https://www.php.net/manual/en/class.serializable.php)
 *
 * It can be used in any part of the application that needs collection of data
 * Such implementations are for instance accessing globals `$_GET`, `$_POST`
 * etc.
 *
 * @property array $data
 * @property bool  $insensitive
 * @property array $lowerKeys
 */
class Collection implements
    CollectionInterface,
    Countable,
    JsonSerializable,
    Serializable
{
    use ArrayAccessTrait;
    use BaseJsonTrait;
    use GetSetHasTrait;
    use JsonTrait;
    use SerializableTrait;

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
     */
    public function __construct(
        array $data = [],
        protected bool $insensitive = true
    ) {
        $this->init($data);
    }

    /**
     * @return array
     */
    public function __serialize(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return void
     */
    public function __unserialize(array $data): void
    {
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
        if (null === $value) {
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
    public function toJson(int $options = 79): string
    {
        $return = $this->phpJsonEncode($this->jsonSerialize(), $options);

        if (false === $return) {
            $return = '';
        }

        return $return;
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
