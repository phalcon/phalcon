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

namespace Phalcon\Support\Arr\Traits;

use function array_filter;
use function is_callable;

/**
 * Trait FilterTrait
 *
 * @package Phalcon\Support\Str\Traits
 */
trait FilterTrait
{
    /**
     * Helper method to filter the collection
     *
     * @param array         $collection
     * @param callable|null $method
     *
     * @return array
     */
    private function toFilter(
        array $collection,
        callable $method = null
    ): array {
        if (null === $method || !is_callable($method)) {
            return $collection;
        }

        return array_filter($collection, $method);
    }
}
