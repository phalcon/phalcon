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

use function array_key_exists;

/**
 * Class Has
 *
 * @package Phalcon\Support\Arr
 */
class Has
{
    /**
     * Helper method to get an array element or a default
     *
     * @param array $collection
     * @param mixed $index
     *
     * @return bool
     */
    public function __invoke(array $collection, $index): bool
    {
        return array_key_exists($index, $collection);
    }
}
