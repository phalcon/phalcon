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
use function str_repeat;
use function str_split;
use function strlen;

/**
 * Padding based on spaces
 */
class Space implements PadInterface
{
    /**
     * @param int $paddingSize
     *
     * @return string
     */
    public function pad(int $paddingSize): string
    {
        return str_repeat(" ", $paddingSize);
    }

    /**
     * @param string $input
     * @param int    $blockSize
     *
     * @return int
     */
    public function unpad(string $input, int $blockSize): int
    {
        $length      = strlen($input);
        $inputArray  = str_split($input);
        $counter     = $length - 1;
        $paddingSize = 0;

        while (
            $counter >= 0 &&
            $inputArray[$counter] === chr(32) &&
            $paddingSize <= $blockSize
        ) {
            $paddingSize++;
            $counter--;
        }

        return $paddingSize;
    }
}
