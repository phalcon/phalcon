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
use function array_keys;
use function array_merge;
use function array_slice;
use function array_values;
use function call_user_func;
use function function_exists;
use function is_array;
use function is_callable;
use function is_int;
use function is_object;
use function is_string;
use function krsort;
use function ksort;

/**
 * This trait contains array transformation methods
 */
trait TransformTrait
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
        if (
            is_callable($method) ||
            (is_string($method) && function_exists($method))
        ) {
            foreach ($collection as $element) {
                $key              = call_user_func($method, $element);
                $filtered[$key][] = $element;
            }
        } else {
            foreach ($collection as $element) {
                if (is_object($element)) {
                    $key              = $element->{$method};
                    $filtered[$key][] = $element;
                } elseif (isset($element[$method])) {
                    $key              = $element[$method];
                    $filtered[$key][] = $element;
                }
            }
        }

        return $filtered;
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
}
