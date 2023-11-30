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

namespace Phalcon\Support\Helper\Arr;

use function array_values;
use function is_object;

use const SORT_REGULAR;

/**
 * Sorts a collection of arrays or objects by an attribute of the object. It
 * supports ascending/descending sorts but also flags that are identical to
 * the ones used by `ksort` and `krsort`
 */
class Order
{
    public const ORDER_ASC  = 1;
    public const ORDER_DESC = 2;

    /**
     * @param array<int|string,mixed> $collection
     * @param mixed                   $attribute
     * @param int                     $order
     * @param int                     $flags
     *
     * @return array<int|string,mixed>
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
     * @param array<int|string,mixed> $sorted
     * @param mixed                   $attribute
     * @param mixed                   $item
     *
     * @return array<int|string,mixed>
     */
    private function checkNonObject(array $sorted, $attribute, $item): array
    {
        if (true !== is_object($item)) {
            $key          = $item[$attribute];
            $sorted[$key] = $item;
        }

        return $sorted;
    }

    /**
     * @param array<int|string,mixed> $sorted
     * @param mixed                   $attribute
     * @param mixed                   $item
     *
     * @return array<int|string,mixed>
     */
    private function checkObject(array $sorted, $attribute, $item): array
    {
        if (true === is_object($item)) {
            $key          = $item->{$attribute};
            $sorted[$key] = $item;
        }

        return $sorted;
    }
}
