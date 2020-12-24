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

namespace Phalcon\Support\Str;

use Phalcon\Support\Str\Traits\EndsWithTrait;
use Phalcon\Support\Str\Traits\StartsWithTrait;

use RuntimeException;
use function array_map;
use function array_merge;
use function end;
use function explode;
use function implode;
use function is_array;
use function mb_substr_count;
use function preg_split;
use function str_split;
use function trim;
use function var_dump;

/**
 * Class Dynamic
 *
 * @package Phalcon\Support\Str
 */
class Dynamic
{
    /**
     * Generates random text in accordance with the template
     *
     * ```php
     * use Phalcon\Helper\Str;
     *
     * // Hi my name is a Bob
     * echo Str::dynamic("{Hi|Hello}, my name is a {Bob|Mark|Jon}!");
     *
     * // Hi my name is a Jon
     * echo Str::dynamic("{Hi|Hello}, my name is a {Bob|Mark|Jon}!");
     *
     * // Hello my name is a Bob
     * echo Str::dynamic("{Hi|Hello}, my name is a {Bob|Mark|Jon}!");
     *
     * // Hello my name is a Zyxep
     * echo Str::dynamic(
     *     "[Hi/Hello], my name is a [Zyxep/Mark]!",
     *     "[", "]",
     *     "/"
     * );
     * ```
     *
     * @param string $text
     * @param string $leftDelimiter
     * @param string $rightDelimiter
     * @param string $separator
     *
     * @return string
     */
    public function __invoke(
        string $text,
        string $leftDelimiter = '{',
        string $rightDelimiter = '}',
        string $separator = '|'
    ): string {
        if (mb_substr_count($text, $leftDelimiter) !== mb_substr_count($text, $rightDelimiter)) {
            throw new RuntimeException(
                'Syntax error in string "' . $text . '"'
            );
        }

        $left = preg_quote($leftDelimiter);
        $right = preg_quote($rightDelimiter);
        $pattern = '/' . $left . '([^' . $left . $right . ']+)' . $right . '/';
        $matches = [];

        if (
            false !== preg_match_all($pattern, $text, $matches, 2) &&
            true === is_array($matches)
        ) {
            foreach ($matches as $match) {
                if (true !== isset($match[0]) || true !== isset($match[1])) {
                    continue;
                }

                $words = explode($separator, $match[1]);
                $word  = $words[array_rand($words)];
                $sub   = preg_quote($match[0], $separator);
                $text  = preg_replace(
                    '/' . $sub . '/',
                    $word,
                    $text,
                    1
                );
            }
        }

        return $text;
    }
}
