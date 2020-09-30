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

namespace Phalcon\Support\Str\Traits;

use function mb_convert_case;

use const MB_CASE_UPPER;

/**
 * Trait UpperTrait
 *
 * @package Phalcon\Support\Str\Traits
 */
trait UpperTrait
{
    /**
     * Uppercases a string using mbstring
     *
     * @param string $text
     * @param string $encoding
     *
     * @return string
     */
    private function toUpper(
        string $text,
        string $encoding = 'UTF-8'
    ): string {
        return mb_convert_case($text, MB_CASE_UPPER, $encoding);
    }
}
