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

use function array_map;
use function array_merge;
use function end;
use function implode;
use function mb_strtolower;
use function preg_split;
use function trim;
use function ucfirst;
use const PREG_SPLIT_DELIM_CAPTURE;
use const PREG_SPLIT_NO_EMPTY;

/**
 * Class Uncamelize
 *
 * @package Phalcon\Support\Str
 */
class Uncamelize
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
     * @param string $text
     * @param string $delimiters
     *
     * @return string
     */
    public function __invoke(
        string $text,
        string $delimiter = '_'
    ): string {
        return mb_strtolower(
            preg_replace(
                '/[A-Z]/',
                $delimiter . '\\0',
                lcfirst($text)
            )
        );
    }
}
