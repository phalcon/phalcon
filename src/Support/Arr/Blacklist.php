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

use Phalcon\Support\Arr\Traits\FilterTrait;

use function array_diff_key;
use function array_flip;
use function is_int;
use function is_string;

/**
 * Class Blacklist
 *
 * @package Phalcon\Support\Arr
 */
class Blacklist
{
    use FilterTrait;

    /**
     * Black list filter by key: exclude elements of an array
     * by the keys obtained from the elements of a blacklist
     *
     * @param array $collection
     * @param array $blackList
     *
     * @return array
     */
    public function __invoke(array $collection, array $blackList): array
    {
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
