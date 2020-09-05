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

namespace Phalcon\Helper\Traits\Arr;

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
 * This trait contains element related methods
 */
trait ElementTrait
{
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
}
