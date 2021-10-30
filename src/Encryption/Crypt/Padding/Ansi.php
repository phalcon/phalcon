<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Encryption\Crypt\Padding;

use function chr;
use function ord;
use function str_repeat;
use function strlen;
use function substr;

/**
 * Padding based on Ansi
 */
class Ansi implements PadInterface
{
    /**
     * @param int $paddingSize
     *
     * @return string
     */
    public function pad(int $paddingSize): string
    {
        return str_repeat(chr(0), $paddingSize - 1) . chr($paddingSize);
    }

    /**
     * @param string $input
     * @param int    $blockSize
     *
     * @return int
     */
    public function unpad(string $input, int $blockSize): int
    {
        $paddingSize = 0;
        $length      = strlen($input);
        $last        = substr($input, $length - 1, 1);
        $ord         = ord($last);

        if ($ord <= $blockSize) {
            $paddingSize = $ord;
            $repeat      = "";
            if ($paddingSize > 1) {
                $repeat = str_repeat(chr(0), $paddingSize - 1);
            }

            $padding = $repeat . $last;

            if (substr($input, $length - $paddingSize) !== $padding) {
                $paddingSize = 0;
            }
        }

        return $paddingSize;
    }
}
