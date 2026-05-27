<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Support\Helper\Arr;

use Phalcon\Traits\Helper\Arr\FilterTrait;

use function array_diff_key;
use function array_flip;
use function is_int;
use function is_string;

/**
 * Black list filter by key: exclude elements of an array
 * by the keys obtained from the elements of a blacklist
 */
class Blacklist
{
    use FilterTrait;

    /**
     * @param array<array-key, mixed> $collection
     * @param array<array-key, mixed> $blackList
     *
     * @return array<array-key, mixed>
     */
    public function __invoke(array $collection, array $blackList): array
    {
        /** @var array<int|string> $blackList */
        $blackList = $this->toFilter(
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
}
