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

use function is_callable;

/**
 * Filters an array using array_filter. If a callback is supplied, it will be
 * used.
 */
class Filter extends AbstractArr
{
    /**
     * @param array<array-key, mixed> $collection
     */
    public function __invoke(array $collection, mixed $method = null): mixed
    {
        return $this->toFilter($collection, is_callable($method) ? $method : null);
    }
}
