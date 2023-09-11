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

use function explode;
use function is_array;

/**
 * Removes a number from the end of a string or decrements that number if it
 * is already defined
 */
class Decrement
{
    /**
     * @param string $text
     * @param string $separator
     *
     * @return string
     */
    public function __invoke(
        string $text,
        string $separator = '_'
    ): string {
        $number = 0;
        $parts  = explode($separator, $text);
        $parts  = !is_array($parts) ? [] : $parts;

        if (true === isset($parts[1])) {
            $number = (int)$parts[1];
            $number--;
            if ($number <= 0) {
                return $parts[0];
            }
        }

        return $parts[0] . $separator . $number;
    }
}
