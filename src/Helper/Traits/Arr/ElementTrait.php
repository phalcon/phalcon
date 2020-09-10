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

use function array_key_exists;
use function is_string;
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
