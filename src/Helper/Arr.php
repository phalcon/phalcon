<?php

/**
 * This file is part of the Phalcon.
 *
 * (c) Phalcon Team <team@phalcon.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Helper;

use function array_chunk;
use function array_diff_key;
use function array_filter;
use function array_flip;
use function array_intersect_key;
use function array_key_exists;
use function array_key_first;
use function array_key_last;
use function array_keys;
use function array_merge;
use function array_slice;
use function array_unique;
use function array_values;
use function call_user_func;
use function end;
use function function_exists;
use function is_array;
use function is_callable;
use function is_int;
use function is_object;
use function is_string;
use function krsort;
use function ksort;
use function reset;
use function settype;

/**
 * This class offers quick array functions throughout the framework
 */
class Arr
{
    /**
     * Black list filter by key: exclude elements of an array
     * by the keys obtained from the elements of a blacklist
     *
     * @param array $collection
     * @param array $blackList
     *
     * @return array
     */
    final public static function blackList(
        array $collection,
        array $blackList
    ): array {
        $blackList = array_filter(
            $blackList,
            function ($element) {
                return is_int($element) || is_string($element);
            }
        );

        return array_diff_key(
            $collection,
            array_flip($blackList)
        );
    }

    /**
     * Chunks an array into smaller arrays of a specified size.
     *
     * @param array $collection
     * @param int   $size
     * @param bool  $preserveKeys
     *
     * @return array
     */
    final public static function chunk(
        array $collection,
        int $size,
        bool $preserveKeys = false
    ): array {
        return array_chunk($collection, $size, $preserveKeys);
    }

    /**
     * Returns the first element of the collection. If a callable is passed, the
     * element returned is the first that validates true
     *
     * @param array         $collection
     * @param callable|null $method
     *
     * @return mixed
     */
    final public static function first(
        array $collection,
        callable $method = null
    ) {
        $filtered = self::filter($collection, $method);

        return reset($filtered);
    }

    /**
     * Helper method to filter the collection
     *
     * @param array         $collection
     * @param callable|null $method
     *
     * @return array
     */
    final public static function filter(
        array $collection,
        callable $method = null
    ): array {
        if (null === $method || !is_callable($method)) {
            return $collection;
        }

        return array_filter($collection, $method);
    }

    /**
     * Returns the key of the first element of the collection. If a callable
     * is passed, the element returned is the first that validates true
     *
     * @param array         $collection
     * @param callable|null $method
     *
     * @return mixed
     */
    final public static function firstKey(
        array $collection,
        callable $method = null
    ) {
        $filtered = self::filter($collection, $method);

        return array_key_first($filtered);
    }

    /**
     * Flattens an array up to the one level depth, unless `$deep` is set to
     * `true`
     *
     * @param array $collection
     * @param bool  $deep
     *
     * @return array
     */
    final public static function flatten(
        array $collection,
        bool $deep = false
    ): array {
        $data = [];

        foreach ($collection as $item) {
            if (!is_array($item)) {
                $data[] = $item;
            } else {
                if ($deep) {
                    $data = array_merge(
                        $data,
                        self::flatten($item, true)
                    );
                } else {
                    $data = array_merge(
                        $data,
                        array_values($item)
                    );
                }
            }
        }

        return $data;
    }

    /**
     * Helper method to get an array element or a default
     *
     * @param array       $collection
     * @param mixed       $index
     * @param mixed|null  $defaultValue
     * @param string|null $cast
     *
     * @return mixed|null
     */
    final public static function get(
        array $collection,
        $index,
        $defaultValue = null,
        string $cast = null
    ) {
        $value = $collection[$index] ?? $defaultValue;

        if (is_string($cast)) {
            settype($value, $cast);
        }

        return $value;
    }

    /**
     * Groups the elements of an array based on the passed callable
     *
     * @param array           $collection
     * @param callable|string $method
     *
     * @return array
     */
    final public static function group(array $collection, $method): array
    {
        $filtered = [];
        foreach ($collection as $element) {
            if (
                is_callable($method) ||
                (is_string($method) && function_exists($method))
            ) {
                $key              = call_user_func($method, $element);
                $filtered[$key][] = $element;
            } elseif (is_object($element)) {
                $key              = $element->{$method};
                $filtered[$key][] = $element;
            } elseif (isset($element[$method])) {
                $key              = $element[$method];
                $filtered[$key][] = $element;
            }
        }

        return $filtered;
    }

    /**
     * Helper method to get an array element or a default
     *
     * @param array $collection
     * @param mixed $index
     *
     * @return bool
     */
    final public static function has(array $collection, $index): bool
    {
        return array_key_exists($index, $collection);
    }

    /**
     * Checks a flat list for duplicate values. Returns true if duplicate
     * values exist and false if values are all unique.
     *
     * @param array $collection
     *
     * @return bool
     */
    final public static function isUnique(array $collection): bool
    {
        return count($collection) === count(array_unique($collection));
    }

    /**
     * Returns the last element of the collection. If a callable is passed, the
     * element returned is the first that validates true
     *
     * @param array         $collection
     * @param callable|null $method
     *
     * @return mixed
     */
    final public static function last(array $collection, $method = null)
    {
        $filtered = self::filter($collection, $method);

        return end($filtered);
    }

    /**
     * Returns the key of the last element of the collection. If a callable is
     * passed, the element returned is the first that validates true
     *
     * @param array         $collection
     * @param callable|null $method
     *
     * @return mixed
     */
    final public static function lastKey(
        array $collection,
        callable $method = null
    ) {
        $filtered = self::filter($collection, $method);

        return array_key_last($filtered);
    }

    /**
     * Sorts a collection of arrays or objects by key
     *
     * @param array  $collection
     * @param mixed  $attribute
     * @param string $order
     *
     * @return array
     */
    final public static function order(
        array $collection,
        $attribute,
        string $order = 'asc'
    ): array {
        $sorted = [];
        foreach ($collection as $item) {
            if (is_object($item)) {
                $key = $item->{$attribute};
            } else {
                $key = $item[$attribute];
            }

            $sorted[$key] = $item;
        }

        if ('asc' === $order) {
            ksort($sorted);
        } else {
            krsort($sorted);
        }

        return array_values($sorted);
    }

    /**
     * Retrieves all of the values for a given key:
     *
     * @param array  $collection
     * @param string $element
     *
     * @return array
     */
    final public static function pluck(
        array $collection,
        string $element
    ): array {
        $filtered = [];
        foreach ($collection as $item) {
            if (is_object($item) && isset($item->{$element})) {
                $filtered[] = $item->{$element};
            } elseif (is_array($item) && isset($item[$element])) {
                $filtered[] = $item[$element];
            }
        }

        return $filtered;
    }

    /**
     * Helper method to set an array element
     *
     * @param array $collection
     * @param mixed $value
     * @param mixed $index
     *
     * @return array
     */
    final public static function set(
        array $collection,
        $value,
        $index = null
    ): array {
        if (null === $index) {
            $collection[] = $value;
        } else {
            $collection[$index] = $value;
        }

        return $collection;
    }

    /**
     * Returns a new array with n elements removed from the right.
     *
     * @param array $collection
     * @param int   $elements
     *
     * @return array
     */
    final public static function sliceLeft(
        array $collection,
        int $elements = 1
    ): array {
        return array_slice($collection, 0, $elements);
    }

    /**
     * Returns a new array with the X elements from the right
     *
     * @param array $collection
     * @param int   $elements
     *
     * @return array
     */
    final public static function sliceRight(
        array $collection,
        int $elements = 1
    ): array {
        return array_slice($collection, $elements);
    }

    /**
     * Returns a new array with keys of the passed array as one element and
     * values as another
     *
     * @param array $collection
     *
     * @return array
     */
    final public static function split(array $collection): array
    {
        return [
            array_keys($collection),
            array_values($collection),
        ];
    }

    /**
     * Returns the passed array as an object
     *
     * @param array $collection
     *
     * @return object
     */
    final public static function toObject(array $collection)
    {
        return (object) $collection;
    }

    /**
     * Returns true if the provided function returns true for all elements of
     * the collection, false otherwise.
     *
     * @param array    $collection
     * @param callable $method
     *
     * @return bool
     */
    final public static function validateAll(
        array $collection,
        callable $method
    ): bool {
        return count(self::filter($collection, $method)) === count($collection);
    }

    /**
     * Returns true if the provided function returns true for at least one
     * element of the collection, false otherwise.
     *
     * @param array    $collection
     * @param callable $method
     *
     * @return bool
     */
    final public static function validateAny(
        array $collection,
        callable $method
    ): bool {
        return count(self::filter($collection, $method)) > 0;
    }

    /**
     * White list filter by key: obtain elements of an array filtering
     * by the keys obtained from the elements of a whitelist
     *
     * @param array $collection
     * @param array $whiteList
     *
     * @return array
     */
    final public static function whiteList(
        array $collection,
        array $whiteList
    ): array {
        /**
         * Clean whitelist, just strings and integers
         */
        $whiteList = self::filter(
            $whiteList,
            function ($element) {
                return is_int($element) || is_string($element);
            }
        );

        return array_intersect_key(
            $collection,
            array_flip($whiteList)
        );
    }
}
