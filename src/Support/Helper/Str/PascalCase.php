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

use function array_map;
use function implode;
use function mb_strtolower;
use function preg_split;
use function str_replace;
use function ucfirst;

use const PREG_SPLIT_DELIM_CAPTURE;
use const PREG_SPLIT_NO_EMPTY;

/**
 * Converts strings to PascalCase style
 */
class PascalCase
{
    /**
     * @param string      $text
     * @param string|null $delimiters
     *
     * @return string
     */
    public function __invoke(
        string $text,
        string | null $delimiters = null
    ): string {
        $exploded = $this->processArray($text, $delimiters);

        $output = array_map(
            function ($element) {
                return ucfirst(mb_strtolower($element));
            },
            $exploded
        );

        return implode('', $output);
    }

    /**
     * @param string      $text
     * @param string|null $delimiters
     *
     * @return string[]
     */
    protected function processArray(
        string $text,
        string | null $delimiters = null
    ): array {
        $delimiters = $delimiters ?: '\-_';
        /**
         * Escape the `-` if it exists so that it does not get interpreted
         * as a range. First remove any escaping for the `-` if present and then
         * add it again - just to be on the safe side
         */
        $delimiters = str_replace(['\-', '-'], ['-', '\-'], $delimiters);

        /** @var list<string>|false $result */
        $result = preg_split(
            '/[' . $delimiters . ']+/',
            $text,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );

        return (false === $result) ? [] : $result;
    }
}
