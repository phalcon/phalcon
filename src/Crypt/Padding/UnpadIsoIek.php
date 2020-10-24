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

namespace Phalcon\Crypt\Padding;

use function chr;
use function ord;
use function str_repeat;
use function str_split;
use function strlen;
use function substr;

/**
 * Class UnpadIsoIek
 *
 * @package Phalcon\Crypt\Padding
 */
class UnpadIsoIek
{
    /**
     * @param string $input
     * @param int    $blockSize
     *
     * @return int
     */
    public function __invoke(string $input, int $blockSize): int
    {
        $length     = strlen($input);
        $inputArray = str_split($input);
        $counter    = $length - 1;

        while ($counter > 0 && $inputArray[$counter] == 0x00 && $paddingSize < $blockSize) {
            $paddingSize++;
            $counter--;
        }

        if ($inputArray[$counter] == 0x80) {
            $paddingSize++;
        } else {
            $paddingSize = 0;
        }

        return $paddingSize;
    }
}
