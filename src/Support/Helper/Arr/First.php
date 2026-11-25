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
use function reset;

/**
 * Returns the first element of the collection. If a callable is passed, the
 * element returned is the first that validates true
 */
class First extends AbstractArr
{
    /**
     * @param array<array-key, mixed> $collection
     */
    public function __invoke(array $collection, mixed $method = null): mixed
    {
        $filtered = $this->toFilter($collection, is_callable($method) ? $method : null);

        return reset($filtered);
    }
}
