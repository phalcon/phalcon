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

use function chr;
use function rand;
use function range;
use function str_repeat;

/**
 * Class PadIso10126
 *
 * @package Phiz\Crypt\Padding
 */
class PadIso10126
{
    /**
     * @param int $paddingSize
     *
     * @return string
     */
    public function __invoke(int $paddingSize): string
    {
        $padding = '';
        $range   = range(0, $paddingSize - 2);
        foreach ($range as $item) {
            $padding .= chr(rand());
        }

        $padding .= chr($paddingSize);

        return $padding;
    }
}
