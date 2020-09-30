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

use function array_values;
use function is_object;

use const SORT_REGULAR;

/**
 * Class Order
 *
 * @package Phalcon\Support\Arr
 */
class Order
{
    public const ORDER_ASC  = 1;
    public const ORDER_DESC = 2;

    /**
     * Sorts a collection of arrays or objects by key
     *
     * @param array $collection
     * @param mixed $attribute
     * @param int   $order
     * @param int   $flags
     *
     * @return array
     */
    public function __invoke(
        array $collection,
        $attribute,
        int $order = self::ORDER_ASC,
        int $flags = SORT_REGULAR
    ): array {
        $sorted = [];
        foreach ($collection as $item) {
            $sorted = $this->checkObject($sorted, $attribute, $item);
            $sorted = $this->checkNonObject($sorted, $attribute, $item);
        }

        $method = (self::ORDER_ASC === $order) ? 'ksort' : 'krsort';
        $method($sorted, $flags);

        return array_values($sorted);
    }

    /**
     * @param array $sorted
     * @param mixed $attribute
     * @param mixed $item
     *
     * @return array
     */
    private function checkObject(array $sorted, $attribute, $item): array
    {
        if (true === is_object($item)) {
            $key = $item->{$attribute};
            $sorted[$key] = $item;
        }

        return $sorted;
    }

    /**
     * @param array $sorted
     * @param mixed $attribute
     * @param mixed $item
     *
     * @return array
     */
    private function checkNonObject(array $sorted, $attribute, $item): array
    {
        if (true !== is_object($item)) {
            $key = $item[$attribute];
            $sorted[$key] = $item;
        }

        return $sorted;
    }
}
