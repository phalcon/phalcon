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

use function array_map;
use function implode;
use function mb_strtolower;
use function preg_split;
use function ucfirst;

use const PREG_SPLIT_DELIM_CAPTURE;
use const PREG_SPLIT_NO_EMPTY;

/**
 * Class Camelize
 *
 * @package Phalcon\Support\Str
 */
class Camelize
{
    /**
     * Converts strings to camelize style
     *
     * ```php
     * use Phalcon\Support\Str;
     *
     * echo Str::camelize("coco_bongo");            // CocoBongo
     * echo Str::camelize("co_co-bon_go", "-");     // Co_coBon_go
     * echo Str::camelize("co_co-bon_go", "_-");    // CoCoBonGo
     * ```
     *
     * @param string      $text
     * @param string|null $delimiters
     *
     * @return string
     */
    public function __invoke(
        string $text,
        string $delimiters = null
    ): string {
        $delimiters = $delimiters ?: '_-';
        $exploded   = preg_split(
            '/[' . $delimiters . ']+/',
            $text,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );
        $output     = array_map(
            function ($element) {
                return ucfirst(mb_strtolower($element));
            },
            $exploded
        );

        return implode('', $output);
    }
}
