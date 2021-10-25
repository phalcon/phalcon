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

use function mb_strlen;

/**
 * Calculates the length of the string using `mb_strlen`
 */
class Len
{
    /**
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
