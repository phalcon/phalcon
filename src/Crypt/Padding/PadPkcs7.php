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
 * Class PadPkcs7
 *
 * @package Phalcon\Crypt\Padding
 */
class PadPkcs7
{
    /**
     * @param int $paddingSize
     *
     * @return string
     */
    public function __invoke(int $paddingSize): string
    {
        return str_repeat(chr($paddingSize), $paddingSize);
    }
}
