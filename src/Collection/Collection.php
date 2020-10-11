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

namespace Phalcon\Collection;

use ArrayAccess;
use Countable;
use Generator;
use IteratorAggregate;
use JsonSerializable;
use Phalcon\Collection\Traits\ArrayAccessTrait;
use Phalcon\Collection\Traits\GetSetHasTrait;
use Phalcon\Collection\Traits\SerializableTrait;
use Phalcon\Support\Traits\JsonTrait;
use Serializable;

use function array_key_exists;
use function array_keys;
use function array_values;
use function is_object;
use function json_encode;
use function mb_strtolower;
use function method_exists;
use function settype;

/**
 * `Phalcon\Collection` is a supercharged object oriented array. It implements:
 * - [ArrayAccess](https://www.php.net/manual/en/class.arrayaccess.php)
 * - [Countable](https://www.php.net/manual/en/class.countable.php)
 * -
 * [IteratorAggregate](https://www.php.net/manual/en/class.iteratoraggregate.php)
 * -
 * [JsonSerializable](https://www.php.net/manual/en/class.jsonserializable.php)
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
    ArrayAccess,
    Countable,
    IteratorAggregate,
    JsonSerializable,
    Serializable
{
    use ArrayAccessTrait;
    use GetSetHasTrait;
    use JsonTrait;
    use SerializableTrait;

    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @var bool
     */
    protected bool $insensitive = true;

    /**
     * @var array
     */
    protected array $lowerKeys = [];

    /**
     * Collection constructor.
     *
     * @param array $data
     * @param bool  $insensitive
     */
    public function __construct(array $data = [], bool $insensitive = true)
    {
        $this->insensitive = $insensitive;
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
        $defaultValue = null,
        string $cast = null
    ) {
        $element = ($this->insensitive) ? mb_strtolower($element) : $element;

        if (!array_key_exists($element, $this->lowerKeys)) {
            return $defaultValue;
        }

        $key   = $this->lowerKeys[$element];
        $value = $this->data[$key];

        if (null !== $cast) {
            settype($value, $cast);
        }

        return $value;
    }

    /**
     * Returns the generator of the class
     *
     * @return Generator
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
     * @param bool $insensitive Case insensitive keys (default: true)
     *
     * @return array
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
     * @return array
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
        if ($this->insensitive) {
            $element = mb_strtolower($element);
        }

        return isset($this->lowerKeys[$element]);
    }

    /**
     * Initialize internal array
     *
     * @param array $data Array to initialize the collection with
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
     */
    public function jsonSerialize(): array
    {
        $records = [];

        foreach ($this->data as $key => $value) {
            $records[$key] = $this->checkSerializable($value);
        }

        return $records;
    }

    /**
     * Delete the element from the collection
     *
     * @param string $element Name of the element
     */
    public function remove(string $element): void
    {
        if ($this->has($element)) {
            if ($this->insensitive) {
                $element = mb_strtolower($element);
            }

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
    public function toJson(int $options = 4194383): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Internal method to set data
     *
     * @param mixed $element Name of the element
     * @param mixed $value   Value to store for the element
     */
    protected function setData($element, $value): void
    {
        $element = (string) $element;
        $key     = ($this->insensitive) ? mb_strtolower($element) : $element;

        $this->data[$element]  = $value;
        $this->lowerKeys[$key] = $element;
    }
}
