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
use function ord;
use function str_repeat;
use function strlen;
use function substr;

/**
 * Class UnpadIso10126
 *
 * @package Phiz\Crypt\Padding
 */
class UnpadIso10126
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

        return (int) ord($last);
    }
}
