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

namespace Phalcon\Support\Traits;

use function base64_decode;
use function base64_encode;
use function str_repeat;
use function str_replace;

trait Base64Trait
{
    /**
     * Decode a Base64 Url string to a json string
     *
     * @param string $input
     *
     * @return string
     */
    private function doDecodeUrl(string $input): string
    {
        $remainder = mb_strlen($input) % 4;
        if ($remainder > 0) {
            $input .= str_repeat('=', 4 - $remainder);
        }

        $data = base64_decode(strtr($input, '-_', '+/'));
        if (false === $data) {
            $data = '';
        }

        return $data;
    }

    /**
     * Encode a json string in Base64 Url format.
     *
     * @param string $input
     *
     * @return string
     */
    private function doEncodeUrl(string $input): string
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }
}
