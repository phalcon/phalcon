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

namespace Phalcon\Encryption\Security\JWT\Signer;

use Phalcon\Encryption\Security\JWT\Exceptions\UnsupportedAlgorithmException;

use function hash_equals;
use function hash_hmac;
use function str_replace;

/**
 * HMAC signing class
 */
class Hmac extends AbstractSigner
{
    /**
     * Hmac constructor.
     *
     * @param string $algo
     *
     * @throws UnsupportedAlgorithmException
     */
    public function __construct(string $algo = 'sha512')
    {
        $supported = [
            'sha512' => 1,
            'sha384' => 1,
            'sha256' => 1,
        ];

        if (true !== isset($supported[$algo])) {
            throw new UnsupportedAlgorithmException(
                'Unsupported HMAC algorithm'
            );
        }

        $this->algorithm = $algo;
    }

    /**
     * Return the value that is used for the "alg" header
     *
     * @return string
     */
    public function getAlgHeader(): string
    {
        return 'HS' . str_replace('sha', '', $this->algorithm);
    }

    /**
     * Sign a payload using the passphrase
     *
     * @param string $payload
     * @param string $passphrase
     *
     * @return string
     */
    public function sign(string $payload, string $passphrase): string
    {
        return $this->getHash($payload, $passphrase);
    }

    /**
     * Verify a passed source with a payload and passphrase
     *
     * @param string $source
     * @param string $payload
     * @param string $passphrase
     *
     * @return bool
     */
    public function verify(
        string $source,
        string $payload,
        string $passphrase
    ): bool {
        return hash_equals($source, $this->getHash($payload, $passphrase));
    }

    /**
     * Calculates a hash from the passed parameters
     *
     * @param string $payload
     * @param string $passphrase
     *
     * @return string
     */
    private function getHash(string $payload, string $passphrase): string
    {
        return hash_hmac($this->getAlgorithm(), $payload, $passphrase, true);
    }
}
