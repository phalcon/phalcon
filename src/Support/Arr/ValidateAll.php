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

namespace Phiz\Support\Arr;

use Phiz\Support\Arr\Traits\FilterTrait;

use function count;

/**
 * Class ValidateAll
 *
 * @package Phiz\Support\Arr
 */
class ValidateAll
{
    use FilterTrait;

    /**
     * Returns true if the provided function returns true for all elements of
     * the collection, false otherwise.
     *
     * @param array    $collection
     * @param callable $method
     *
     * @return bool
     */
    public function __invoke(array $collection, callable $method): bool
    {
        return count($this->toFilter($collection, $method)) === count($collection);
    }
}
