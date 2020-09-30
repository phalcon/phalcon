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

use function explode;

/**
 * Class Increment
 *
 * @package Phalcon\Support\Str
 */
class Increment
{
    /**
     * Adds a number to a string or increment that number if it already is
     * defined
     *
     * @param string $text
     * @param string $separator
     *
     * @return string
     */
    public function __invoke(
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
}
