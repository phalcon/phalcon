<?php

/**
 * This file is part of the Phalcon.
 *
 * (c) Phalcon Team <team@phalcon.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Helper\Traits\Str;

use function count_chars;
use function strrev;

use const MB_CASE_LOWER;
use const MB_CASE_UPPER;

/**
 * Assertion related methods
 */
trait AssertTrait
{
    /**
     * Compare two strings and returns true if both strings are anagram,
     * false otherwise.
     *
     * @param string $first
     * @param string $second
     *
     * @return bool
     */
    final public static function isAnagram(string $first, string $second): bool
    {
        return count_chars($first, 1) === count_chars($second, 1);
    }

    /**
     * Returns true if the given string is lower case, false otherwise.
     *
     * @param string $text
     * @param string $encoding
     *
     * @return bool
     */
    final public static function isLower(
        string $text,
        string $encoding = "UTF-8"
    ): bool {
        return $text === mb_convert_case($text, MB_CASE_LOWER, $encoding);
    }

    /**
     * Returns true if the given string is a palindrome, false otherwise.
     *
     * @param string $text
     *
     * @return bool
     */
    final public static function isPalindrome(string $text): bool
    {
        return strrev($text) === $text;
    }

    /**
     * Returns true if the given string is upper case, false otherwise.
     *
     * @param string $text
     * @param string $encoding
     *
     * @return bool
     */
    final public static function isUpper(
        string $text,
        string $encoding = "UTF-8"
    ): bool {
        return $text === mb_convert_case($text, MB_CASE_UPPER, $encoding);
    }
}
