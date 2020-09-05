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

namespace Phalcon\Helper;

use function array_merge;
use function count_chars;
use function explode;
use function func_get_args;
use function implode;
use function is_array;
use function is_string;
use function mt_rand;
use function pathinfo;
use function preg_match_all;
use function preg_replace;
use function range;
use function rtrim;
use function str_replace;
use function str_split;
use function strlen;
use function strrev;
use function strtolower;
use function substr;
use function substr_compare;
use function trim;

use const DIRECTORY_SEPARATOR;
use const MB_CASE_TITLE;
use const MB_CASE_UPPER;
use const PATHINFO_FILENAME;

/**
 * This class offers quick string functions throughout the framework
 */
class Str
{
    // Only alpha numeric characters [a-zA-Z0-9]
    public const RANDOM_ALNUM = 0;
    // Only alphabetical characters [azAZ]
    public const RANDOM_ALPHA = 1;
    // Only alpha numeric uppercase characters exclude similar
    // characters [2345679ACDEFHJKLMNPRSTUVWXYZ]
    public const RANDOM_DISTINCT = 5;
    // Only hexadecimal characters [0-9a-f]
    public const RANDOM_HEXDEC = 2;
    // Only numbers without 0 [1-9]
    public const RANDOM_NOZERO = 4;
    // Only numbers [0-9]
    public const RANDOM_NUMERIC = 3;

    /**
     * Concatenates strings using the separator only once without duplication in
     * places concatenation
     *
     * ```php
     * $str = Phalcon\Helper\Str::concat(
     *     "/",
     *     "/tmp/",
     *     "/folder_1/",
     *     "/folder_2",
     *     "folder_3/"
     * );
     *
     * echo $str;   // /tmp/folder_1/folder_2/folder_3/
     * ```
     *
     * @param string separator
     * @param string a
     * @param string b
     * @param string ...N
     *
     * @return string
     * @throws Exception
     */
    final public static function concat(): string
    {
        $arguments = func_get_args();

        if (count($arguments) < 3) {
            throw new Exception(
                "concat needs at least three parameters"
            );
        }

        $delimiter = Arr::first($arguments);
        $arguments = Arr::sliceRight($arguments);
        $first     = Arr::first($arguments);
        $last      = Arr::last($arguments);
        $prefix    = "";
        $suffix    = "";
        $data      = [];

        if (self::startsWith($first, $delimiter)) {
            $prefix = $delimiter;
        }

        if (self::endsWith($last, $delimiter)) {
            $suffix = $delimiter;
        }

        foreach ($arguments as $argument) {
            $data[] = trim($argument, $delimiter);
        }

        return $prefix . implode($delimiter, $data) . $suffix;
    }

    /**
     * Returns number of vowels in provided string. Uses a regular expression
     * to count the number of vowels (A, E, I, O, U) in a string.
     *
     * @param string $text
     *
     * @return int
     */
    final public static function countVowels(string $text): int
    {
        preg_match_all("/[aeiou]/i", $text, $matches);

        return count($matches[0]);
    }

    /**
     * Decapitalizes the first letter of the string and then adds it with rest
     * of the string. Omit the upperRest parameter to keep the rest of the
     * string intact, or set it to true to convert to uppercase.
     *
     * @param string $text
     * @param bool   $upperRest
     * @param string $encoding
     *
     * @return string
     */
    final public static function decapitalize(
        string $text,
        bool $upperRest = false,
        string $encoding = "UTF-8"
    ): string {
        $substr = mb_substr($text, 1);
        $suffix = ($upperRest) ? self::upper($substr, $encoding) : $substr;

        return self::lower(mb_substr($text, 0, 1), $encoding) . $suffix;
    }

    /**
     * Removes a number from a string or decrements that number if it is already
     * defined
     *
     * ```php
     * use Phalcon\Helper\Str;
     *
     * echo Str::decrement("a_1");    // "a"
     * echo Str::decrement("a_2");  // "a_1"
     * ```
     *
     * @param string $text
     * @param string $separator
     *
     * @return string
     */
    final public static function decrement(
        string $text,
        string $separator = "_"
    ): string {
        $number = 0;
        $parts  = explode($separator, $text);

        if (isset($parts[1])) {
            $number = $parts[1];
            $number--;
            if ($number <= 0) {
                return $parts[0];
            }
        }

        return $parts[0] . $separator . $number;
    }

    /**
     * Accepts a file name (without extension) and returns a calculated
     * directory structure with the filename in the end
     *
     * @param string $file
     *
     * @return string
     */
    final public static function dirFromFile(string $file): string
    {
        $name  = pathinfo($file, PATHINFO_FILENAME);
        $start = substr($name, 0, -2);

        if (!$start) {
            $start = substr($name, 0, 1);
        }

        return implode('/', str_split($start, 2)) . '/';
    }

    /**
     * Accepts a directory name and ensures that it ends with
     * DIRECTORY_SEPARATOR
     *
     * @param string $directory
     *
     * @return string
     */
    final public static function dirSeparator(string $directory): string
    {
        return rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * Check if a string ends with a given string
     *
     * @param string $haystack
     * @param string $needle
     * @param bool   $ignoreCase
     *
     * @return bool
     */
    final public static function endsWith(
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

    /**
     * Returns the first string there is between the strings from the
     * parameter start and end.
     *
     * @param string $text
     * @param string $start
     * @param string $end
     *
     * @return string
     */
    final public static function firstBetween(
        string $text,
        string $start,
        string $end
    ): string {
        $result = mb_strstr($text, $start);
        $result = (false === $result) ? '' : $result;
        $result = mb_strstr($result, $end, true);
        $result = (false === $result) ? '' : $result;

        return trim($result, $start . $end);
    }

    /**
     * Changes a text to a URL friendly one
     *
     * @param string     $text
     * @param string     $separator
     * @param bool       $lowercase
     * @param mixed|null $replace
     *
     * @return string
     * @throws Exception
     */
    public function friendly(
        string $text,
        string $separator = "-",
        bool $lowercase = true,
        $replace = null
    ): string {

        if (null !== $replace) {
            if (!is_array($replace) && !is_string($replace)) {
                throw new Exception(
                    "Parameter replace must be an array or a string"
                );
            }

            if (is_string($replace)) {
                $replace = [$replace];
            }

            $text = str_replace($replace, " ", $text);
        }

        $friendly = preg_replace(
            "/[^a-zA-Z0-9\\/_|+ -]/",
            "",
            $text
        );

        if ($lowercase) {
            $friendly = strtolower($friendly);
        }

        $friendly = preg_replace("/[\\/_|+ -]+/", $separator, $friendly);
        $friendly = trim($friendly, $separator);

        return $friendly;
    }

    /**
     * Makes an underscored or dashed phrase human-readable
     *
     * @param string $text
     *
     * @return string
     */
    final public static function humanize(string $text): string
    {
        $result = preg_replace('#[_-]+#', ' ', trim($text));

        return (null === $result) ? '' : $result;
    }

    /**
     * Lets you determine whether or not a string includes another string.
     *
     * @param string $haystack
     * @param string $needle
     *
     * @return bool
     */
    final public static function includes(
        string $haystack,
        string $needle
    ): bool {
        return false !== mb_strpos($haystack, $needle);
    }

    /**
     * Adds a number to a string or increment that number if it already is
     * defined
     *
     * @param string $text
     * @param string $separator
     *
     * @return string
     */
    final public static function increment(
        string $text,
        string $separator = "_"
    ): string {
        $parts  = explode($separator, $text);
        $number = 1;

        if (isset($parts[1])) {
            $number = ((int) $parts[1]) + 1;
        }

        return $parts[0] . $separator . $number;
    }

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
        return $text === self::lower($text, $encoding);
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
        return $text === self::upper($text, $encoding);
    }

    /**
     * Calculates the length of the string. Uses mbstring if present
     *
     * @param string $text
     * @param string $encoding
     *
     * @return int
     */
    final public static function len(
        string $text,
        string $encoding = "UTF-8"
    ): int {
        return mb_strlen($text, $encoding);
    }

    /**
     * Lowercases a string, this function makes use of the mbstring extension if
     * available
     *
     * @param string $text
     * @param string $encoding
     *
     * @return string
     */
    final public static function lower(
        string $text,
        string $encoding = "UTF-8"
    ): string {
        return mb_convert_case($text, MB_CASE_LOWER, $encoding);
    }

    /**
     * Generates a random string based on the given type. Type is one of the
     * RANDOM_* constants
     *
     * @param int $type
     * @param int $length
     *
     * @return string
     */
    final public static function random(
        int $type = self::RANDOM_ALNUM,
        int $length = 8
    ): string {
        $text  = "";
        $type  = ($type < 0 || $type > 5) ? self::RANDOM_ALNUM : $type;
        $pools = [
            self::RANDOM_ALPHA    => array_merge(
                range('a', 'z'),
                range('A', 'Z')
            ),
            self::RANDOM_HEXDEC   => array_merge(
                range(0, 9),
                range('a', 'f')
            ),
            self::RANDOM_NUMERIC  => range(0, 9),
            self::RANDOM_NOZERO   => range(1, 9),
            self::RANDOM_DISTINCT => str_split('2345679ACDEFHJKLMNPRSTUVWXYZ'),
            self::RANDOM_ALNUM    => array_merge(
                range(0, 9),
                range('a', 'z'),
                range('A', 'Z')
            ),
        ];

        $end = count($pools[$type]) - 1;

        while (strlen($text) < $length) {
            $text .= $pools[$type][mt_rand(0, $end)];
        }

        return $text;
    }

    /**
     * Reduces multiple slashes in a string to single slashes
     *
     * @param string $text
     *
     * @return string
     */
    final public static function reduceSlashes(string $text): string
    {
        $result = preg_replace('#(?<!:)//+#', '/', $text);

        return (null === $result) ? '' : $result;
    }

    /**
     * Check if a string starts with a given string
     *
     * @param string $haystack
     * @param string $needle
     * @param bool   $ignoreCase
     *
     * @return bool
     */
    final public static function startsWith(
        string $haystack,
        string $needle,
        bool $ignoreCase = true
    ): bool {
        if ("" === $haystack) {
            return false;
        }

        return 0 === substr_compare(
                $haystack,
                $needle,
                0,
                strlen($needle),
                $ignoreCase
            );
    }

    /**
     * Makes a phrase underscored instead of spaced
     *
     * @param string $text
     * @param string $encoding
     *
     * @return string
     */
    final public static function ucwords(
        string $text,
        string $encoding = "UTF-8"
    ): string {
        return mb_convert_case($text, MB_CASE_TITLE, $encoding);
    }

    /**
     * Uppercases a string, this function makes use of the mbstring extension if
     * available
     *
     * @param string $text
     * @param string $encoding
     *
     * @return string
     */
    final public static function upper(
        string $text,
        string $encoding = "UTF-8"
    ): string {
        return mb_convert_case($text, MB_CASE_UPPER, $encoding);
    }

    /**
     * Makes a phrase underscored instead of spaced
     *
     * @param string $text
     *
     * @return string
     */
    final public static function underscore(string $text): string
    {
        $result = preg_replace('#\s+#', '_', trim($text));

        return (null === $result) ? "" : $result;
    }
}
