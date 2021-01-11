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

use function array_key_first;

/**
 * Class FirstKey
 *
 * @package Phalcon\Support\Arr
 */
class FirstKey
{
    use FilterTrait;

    /**
     * Returns the key of the first element of the collection. If a callable
     * is passed, the element returned is the first that validates true
     *
     * @param array         $collection
     * @param callable|null $method
     *
     * @return mixed
     */
    public function __invoke(array $collection, callable $method = null)
    {
        $filtered = $this->toFilter($collection, $method);

        return array_key_first($filtered);
    }
}
