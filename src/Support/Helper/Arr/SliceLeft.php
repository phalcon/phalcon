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

use function array_slice;

/**
 * Returns a new array with n elements removed from the left.
 */
class SliceLeft
{
    /**
     * @param array<int|string,mixed> $collection
     * @param int                     $elements
     *
     * @return array<int|string,mixed>
     */
    public function __invoke(array $collection, int $elements = 1): array
    {
        return array_slice($collection, 0, $elements);
    }
}
