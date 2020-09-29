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

use function mb_strlen;

/**
 * Class Len
 *
 * @package Phalcon\Support\Str
 */
class Len
{
    /**
     * Calculates the length of the string using mbstring
     *
     * @param string $text
     * @param string $encoding
     *
     * @return int
     */
    public function __invoke(string $text, string $encoding = 'UTF-8'): int
    {
        return mb_strlen($text, $encoding);
    }
}
