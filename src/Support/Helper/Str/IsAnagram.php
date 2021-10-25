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

namespace Phalcon\Support\Helper\Str;

use function count_chars;

/**
 * Compare two strings and returns `true` if both strings are anagram,
 * `false` otherwise.
 */
class IsAnagram
{
    /**
     * @param string $first
     * @param string $second
     *
     * @return bool
     */
    public function __invoke(string $first, string $second): bool
    {
        return count_chars($first, 1) === count_chars($second, 1);
    }
}
