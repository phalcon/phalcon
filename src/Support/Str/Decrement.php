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
use function is_array;

class Decrement
{


    /**
     * Removes a number from a string or decrements that number if it is already
     * defined
     *
     * ```php
     * use Phalcon\Helper\Str;
     *
     * echo Str::decrement('a_1');  // 'a'
     * echo Str::decrement('a_2');  // 'a_1'
     * ```
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
        $number = 0;
        $parts  = explode($separator, $text);
        $parts  = !is_array($parts) ? [] : $parts;

        if (isset($parts[1])) {
            $number = $parts[1];
            $number--;
            if ($number <= 0) {
                return $parts[0];
            }
        }

        return $parts[0] . $separator . $number;
    }
}
