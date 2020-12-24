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
use function explode;
use function implode;
use function preg_split;
use function str_split;
use function trim;
use function var_dump;

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
        $exploded = preg_split('/[' . $delimiters . ']+/', $text);

        $output = array_map('mb_strtolower', $exploded);
        $output = array_map('ucfirst', $output);

        return implode('', $output);
    }
}
