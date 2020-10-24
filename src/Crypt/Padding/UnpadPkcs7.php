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
use function strlen;
use function substr;

/**
 * Class UnpadPkcs7
 *
 * @package Phalcon\Crypt\Padding
 */
class UnpadPkcs7
{
    /**
     * @param string $input
     * @param int    $blockSize
     *
     * @return int
     */
    public function __invoke(string $input, int $blockSize): int
    {
        $length = strlen($input);
        $last   = substr($input, $length - 1, 1);
        $ord    = (int) ord($last);

        if ($ord <= $blockSize) {
            $paddingSize = $ord;
            $padding     = str_repeat(chr($paddingSize), $paddingSize);

            if (substr($input, $length - $paddingSize) != $padding) {
                $paddingSize = 0;
            }
        }

        return $paddingSize;
    }
}
