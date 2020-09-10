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

use Phalcon\Helper\Exception;

use function explode;
use function is_array;
use function is_string;
use function preg_replace;
use function str_replace;
use function trim;

use const MB_CASE_LOWER;
use const MB_CASE_TITLE;
use const MB_CASE_UPPER;

/**
 * Transformation related methods
 */
trait TransformTrait
{
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
        string $encoding = 'UTF-8'
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
     * echo Str::decrement('a_1');  // 'a'
     * echo Str::decrement('a_2');  // 'a_1'
     * ```
     *
     * @param string $text
     * @param string $separator
     *
     * @return string
     */
    final public static function decrement(
        string $text,
        string $separator = '_'
    ): string {
        $number = 0;
        $parts  = explode($separator, $text);
        $parts  = !is_array($parts) ? [] : $parts;

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
    final public static function friendly(
        string $text,
        string $separator = '-',
        bool $lowercase = true,
        $replace = null
    ): string {
        if (null !== $replace) {
            if (!is_array($replace) && !is_string($replace)) {
                throw new Exception(
                    'Parameter replace must be an array or a string'
                );
            }

            if (is_string($replace)) {
                $replace = [$replace];
            }

            $text = str_replace($replace, ' ', $text);
        }

        $friendly = preg_replace(
            '/[^a-zA-Z0-9\\/_|+ -]/',
            '',
            $text
        );

        if ($lowercase) {
            $friendly = self::lower((string) $friendly);
        }

        return trim(
            (string) preg_replace(
                '/[\\/_|+ -]+/',
                $separator,
                $friendly
            ),
            $separator
        );
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
        string $separator = '_'
    ): string {
        $parts  = explode($separator, $text);
        $number = 1;

        if (isset($parts[1])) {
            $number = ((int) $parts[1]) + 1;
        }

        return $parts[0] . $separator . $number;
    }

    /**
     * Lowercases a string using mbstring
     *
     * @param string $text
     * @param string $encoding
     *
     * @return string
     */
    final public static function lower(
        string $text,
        string $encoding = 'UTF-8'
    ): string {
        return mb_convert_case($text, MB_CASE_LOWER, $encoding);
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
     * Capitalizes the first letter of each word
     *
     * @param string $text
     * @param string $encoding
     *
     * @return string
     */
    final public static function ucwords(
        string $text,
        string $encoding = 'UTF-8'
    ): string {
        return mb_convert_case($text, MB_CASE_TITLE, $encoding);
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

        return (null === $result) ? '' : $result;
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
        string $encoding = 'UTF-8'
    ): string {
        return mb_convert_case($text, MB_CASE_UPPER, $encoding);
    }
}
