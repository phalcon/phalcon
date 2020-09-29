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

namespace Phalcon\Support\Str;

use function array_merge;
use function mt_rand;
use function range;
use function rtrim;
use function str_split;
use function strlen;
use function trim;

use const DIRECTORY_SEPARATOR;

/**
 * Class DirSeparator
 *
 * @package Phalcon\Support\Str
 */
class DirSeparator
{
    /**
     * Accepts a directory name and ensures that it ends with
     * DIRECTORY_SEPARATOR
     *
     * @param string $directory
     *
     * @return string
     */
    public function __invoke(string $directory): string
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
