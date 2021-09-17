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

namespace Phalcon\Support\Helper\Arr\Traits;

use function array_filter;
use function is_callable;

/**
 * Trait FilterTrait
 *
 * @package Phalcon\Support\Helper\Str\Traits
 */
trait FilterTrait
{
    /**
     * Helper method to filter the collection
     *
     * @param array<int|string,mixed> $collection
     * @param callable|null           $method
     *
     * @return array<int|string,mixed>
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
