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

use Phalcon\Helper\Traits\Arr\ElementTrait;
use Phalcon\Helper\Traits\Arr\TransformTrait;

use function array_filter;
use function array_flip;
use function array_intersect_key;
use function array_key_first;
use function array_key_last;
use function array_unique;
use function end;
use function is_callable;
use function is_int;
use function is_string;
use function reset;

/**
 * This class offers quick array functions throughout the framework
 */
class Arr
{
    use ElementTrait;
    use TransformTrait;

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
