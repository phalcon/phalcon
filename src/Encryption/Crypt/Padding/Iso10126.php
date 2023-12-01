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
use function rand;
use function strlen;
use function substr;

/**
 * Padding based on ISO10126
 */
class Iso10126 implements PadInterface
{
    /**
     * @param int $paddingSize
     *
     * @return string
     */
    public function pad(int $paddingSize): string
    {
        $padding = "";
        $length  = $paddingSize - 2;
        for ($counter = 0; $counter <= $length; $counter++) {
            $padding .= chr(rand());
        }

        $padding .= chr($paddingSize);

        return $padding;
    }

    /**
     * @param string $input
     * @param int    $blockSize
     *
     * @return int
     */
    public function unpad(string $input, int $blockSize): int
    {
        $length = strlen($input);
        $last   = substr($input, $length - 1, 1);

        return ord($last);
    }
}
