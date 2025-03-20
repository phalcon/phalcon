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

use function end;

/**
 * Returns the last element of the collection. If a callable is passed, the
 * element returned is the first that validates true
 */
class Last
{
    use FilterTrait;

    /**
     * @param array<array-key, mixed> $collection
     * @param callable|null           $method
     *
     * @return mixed
     */
    public function __invoke(array $collection, callable | null $method = null)
    {
        $filtered = $this->toFilter($collection, $method);

        return end($filtered);
    }
}
