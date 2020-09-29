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

use function array_values;
use function is_object;
use function krsort;
use function ksort;

/**
 * Class Order
 *
 * @package Phalcon\Support\Arr
 */
class Order
{
    /**
     * Sorts a collection of arrays or objects by key
     *
     * @param array  $collection
     * @param mixed  $attribute
     * @param string $order
     *
     * @return array
     */
    public function __invoke(
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
}
