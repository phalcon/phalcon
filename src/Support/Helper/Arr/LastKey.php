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

use function array_key_last;
use function is_callable;

/**
 * Returns the key of the last element of the collection. If a callable is
 * passed, the element returned is the first that validates true
 */
class LastKey extends AbstractArr
{
    /**
     * @param array<array-key, mixed> $collection
     *
     * @return int|string|null
     */
    public function __invoke(array $collection, mixed $method = null)
    {
        $filtered = $this->toFilter($collection, is_callable($method) ? $method : null);

        return array_key_last($filtered);
    }
}
