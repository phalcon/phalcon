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
 * Padding based on Pkcs7
 */
class Pkcs7 implements PadInterface
{
    /**
     * @param int $paddingSize
     *
     * @return string
     */
    public function pad(int $paddingSize): string
    {
        return str_repeat(chr($paddingSize), $paddingSize);
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
            $padding     = str_repeat(chr($paddingSize), $paddingSize);

            if (substr($input, $length - $paddingSize) !== $padding) {
                $paddingSize = 0;
            }
        }

        return $paddingSize;
    }
}
