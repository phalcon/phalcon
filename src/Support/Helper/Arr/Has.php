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

use function array_key_exists;

/**
 * Checks an array if it has an element with a specific key and returns
 * `true`/`false` accordingly
 */
class Has
{
    /**
     * @param array<int|string,mixed> $collection
     * @param string|int              $index
     *
     * @return bool
     */
    public function __invoke(array $collection, $index): bool
    {
        return array_key_exists($index, $collection);
    }
}
