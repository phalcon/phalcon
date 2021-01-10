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

namespace Phiz\Crypt\Padding;

use function str_split;
use function strlen;

/**
 * Class UnpadZero
 *
 * @package Phiz\Crypt\Padding
 */
class UnpadZero
{
    /**
     * @param string $input
     * @param int    $blockSize
     *
     * @return int
     */
    public function __invoke(string $input, int $blockSize): int
    {
        $length      = strlen($input);
        $inputArray  = str_split($input);
        $counter     = $length - 1;
        $paddingSize = 0;

        while ($counter >= 0 && $inputArray[$counter] == 0x00 && $paddingSize <= $blockSize) {
            $paddingSize++;
            $counter--;
        }

        return $paddingSize;
    }
}
