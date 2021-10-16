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
use function rand;
use function range;
use function strlen;
use function substr;

/**
 * Class Iso10126
 *
 * @package Phalcon\Crypt\Padding
 */
class Iso10126 implements PadInterface
{
    /**
     * @param string $input
     * @param int    $blockSize
     *
     * @return string
     */
    public function pad(int $paddingSize): string
    {
        $padding = "";
        $range   = range(0, $paddingSize - 2);
        foreach ($range as $item) {
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
