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

use function array_merge;
use function mt_rand;
use function range;
use function str_split;
use function strlen;

/**
 * Generates a random string based on the given type. Type is one of the
 * RANDOM_* constants
 */
class Random
{
    // Only alphanumeric characters [a-zA-Z0-9]
    public const RANDOM_ALNUM = 0;
    // Only alphabetical characters [azAZ]
    public const RANDOM_ALPHA = 1;
    // Only alphanumeric uppercase characters exclude similar
    // characters [2345679ACDEFHJKLMNPRSTUVWXYZ]
    public const RANDOM_DISTINCT = 5;
    // Only hexadecimal characters [0-9a-f]
    public const RANDOM_HEXDEC = 2;
    // Only numbers without 0 [1-9]
    public const RANDOM_NOZERO = 4;
    // Only numbers [0-9]
    public const RANDOM_NUMERIC = 3;

    /**
     * @param int $type
     * @param int $length
     *
     * @return string
     */
    public function __invoke(
        int $type = self::RANDOM_ALNUM,
        int $length = 8
    ): string {
        $text  = '';
        $type  = ($type < 0 || $type > 5) ? self::RANDOM_ALNUM : $type;
        $pools = [
            self::RANDOM_ALPHA    => array_merge(
                range('a', 'z'),
                range('A', 'Z')
            ),
            self::RANDOM_HEXDEC   => array_merge(
                range(0, 9),
                range('a', 'f')
            ),
            self::RANDOM_NUMERIC  => range(0, 9),
            self::RANDOM_NOZERO   => range(1, 9),
            self::RANDOM_DISTINCT => str_split('2345679ACDEFHJKLMNPRSTUVWXYZ'),
            self::RANDOM_ALNUM    => array_merge(
                range(0, 9),
                range('a', 'z'),
                range('A', 'Z')
            ),
        ];

        $end = count($pools[$type]) - 1;

        while (strlen($text) < $length) {
            $text .= $pools[$type][mt_rand(0, $end)];
        }

        return $text;
    }
}
