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

use function openssl_cipher_iv_length;
use function openssl_random_pseudo_bytes;

trait PhpOpensslTrait
{
    /**
     * @param string $cipher
     *
     * @return false|int
     *
     * @link https://www.php.net/manual/en/function.openssl-cipher-iv-length
     */
    public function phpOpensslCipherIvLength(string $cipher)
    {
        return openssl_cipher_iv_length($cipher);
    }

    /**
     * @param int $length
     *
     * @return string
     *
     * @link https://php.net/manual/en/function.openssl-random-pseudo-bytes
     */
    protected function phpOpensslRandomPseudoBytes(int $length)
    {
        return openssl_random_pseudo_bytes($length);
    }
}
