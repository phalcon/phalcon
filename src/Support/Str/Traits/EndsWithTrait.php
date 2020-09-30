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

namespace Phalcon\Support\Str\Traits;

use function mb_strlen;
use function substr_compare;

/**
 * Trait EndsWithTrait
 *
 * @package Phalcon\Support\Str\Traits
 */
trait EndsWithTrait
{
    /**
     * Check if a string ends with a given string
     *
     * @param string $haystack
     * @param string $needle
     * @param bool   $ignoreCase
     *
     * @return bool
     */
    private function toEndsWith(
        string $haystack,
        string $needle,
        bool $ignoreCase = true
    ): bool {
        if ('' === $haystack) {
            return false;
        }

        return 0 === substr_compare(
            $haystack,
            $needle,
            -mb_strlen($needle),
            mb_strlen($needle),
            $ignoreCase
        );
    }
}
