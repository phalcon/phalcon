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

use function array_unique;
use function count;

/**
 * Checks a flat list for duplicate values. Returns true if duplicate
 * values exist and false if values are all unique.
 */
class IsUnique
{
    /**
     * @param array<int|string,mixed> $collection
     *
     * @return bool
     */
    public function __invoke(array $collection): bool
    {
        return count($collection) === count(array_unique($collection));
    }
}
