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

use Phalcon\Helper\Traits\Str\AssertTrait;
use Phalcon\Helper\Traits\Str\TransformTrait;

use function array_merge;
use function end;
use function implode;
use function mt_rand;
use function pathinfo;
use function preg_match_all;
use function range;
use function rtrim;
use function str_split;
use function strlen;
use function substr;
use function trim;

use const DIRECTORY_SEPARATOR;
use const PATHINFO_FILENAME;

/**
 * This class offers quick string functions throughout the framework
 */
class Str
{
    use AssertTrait;
    use TransformTrait;

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
     *     '/',
     *     '/tmp/',
     *     '/folder_1/',
     *     '/folder_2',
     *     'folder_3/'
     * );
     *
     * echo $str;   // /tmp/folder_1/folder_2/folder_3/
     * ```
     *
     * @param string $delimiter
     * @param string $first
     * @param string $second
     * @param string ...$arguments
     *
     * @return string
     */
    final public static function concat(
        string $delimiter,
        string $first,
        string $second,
        string ...$arguments
    ): string {
        $data       = [];
        $parameters = array_merge([$first, $second], $arguments);
        $last       = end($parameters) ?? $second;

        $prefix = self::startsWith($first, $delimiter) ? $delimiter : '';
        $suffix = self::endsWith($last, $delimiter) ? $delimiter : '';

        foreach ($parameters as $parameter) {
            $data[] = trim($parameter, $delimiter);
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
        preg_match_all('/[aeiouy]/i', $text, $matches);

        return count($matches[0]);
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
     * Calculates the length of the string using mbstring
     *
     * @param string $text
     * @param string $encoding
     *
     * @return int
     */
    final public static function len(
        string $text,
        string $encoding = 'UTF-8'
    ): int {
        return mb_strlen($text, $encoding);
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
        $text  = '';
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
}
