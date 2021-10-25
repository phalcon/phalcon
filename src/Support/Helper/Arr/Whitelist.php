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

use Phalcon\Support\Helper\Arr\Traits\FilterTrait;

use function array_flip;
use function array_intersect_key;
use function is_int;
use function is_string;

/**
 * White list filter by key: obtain elements of an array filtering by the keys
 * obtained from the elements of a whitelist
 */
class Whitelist
{
    use FilterTrait;

    /**
     * @param array<int|string,mixed> $collection
     * @param array<int|string,mixed> $whiteList
     *
     * @return array<int|string,mixed>
     */
    public function __invoke(array $collection, array $whiteList): array
    {
        /**
         * Clean whitelist, just strings and integers
         */
        $whiteList = $this->toFilter(
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
