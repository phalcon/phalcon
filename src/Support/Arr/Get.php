<?php

/**
 * This file is part of the Phalcon.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Support\Arr;

use function is_string;
use function settype;

/**
 * Class Get
 *
 * @package Phalcon\Support\Arr
 */
class Get
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
    public function __invoke(
        array $collection,
        $index,
        $defaultValue = null,
        string $cast = null
    ) {
        $value = $collection[$index] ?? $defaultValue;

        if (null !== $cast) {
            settype($value, $cast);
        }

        return $value;
    }
}
