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

namespace Phalcon\Support\Arr;

/**
 * Class Set
 *
 * @package Phalcon\Support\Arr
 */
class Set
{
    /**
     * Helper method to set an array element
     *
     * @param array $collection
     * @param mixed $value
     * @param mixed $index
     *
     * @return array
     */
    public function __invoke(
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
