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

use RuntimeException;

use function explode;
use function is_array;
use function mb_substr_count;

/**
 * Generates random text in accordance with the template. The template is
 * defined by the left and right delimiter and it can contain values separated
 * by the separator
 */
class Dynamic
{
    /**
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
                "Syntax error in string '" . $text . "'"
            );
        }

        $left    = preg_quote($leftDelimiter);
        $right   = preg_quote($rightDelimiter);
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
