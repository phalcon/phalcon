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

use Phalcon\Traits\Helper\Arr\FilterTrait;

use function array_key_first;

/**
 * Returns the key of the first element of the collection. If a callable
 * is passed, the element returned is the first that validates true
 */
class FirstKey
{
    use FilterTrait;

    /**
     * @param array<array-key, mixed> $collection
     * @param callable|null           $method
     *
     * @return int|string|null
     */
    public function __invoke(array $collection, callable | null $method = null)
    {
        $filtered = $this->toFilter($collection, $method);

        return array_key_first($filtered);
    }
}
