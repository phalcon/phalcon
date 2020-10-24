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
use function str_repeat;

/**
 * Class PadIsoIek
 *
 * @package Phalcon\Crypt\Padding
 */
class PadIsoIek
{
    /**
     * @param int $paddingSize
     *
     * @return string
     */
    public function __invoke(int $paddingSize): string
    {
        return chr(0x80) . str_repeat(chr(0), $paddingSize - 1);
    }
}
