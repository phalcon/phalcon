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

namespace Phalcon\Crypt\Traits;

use Phalcon\Crypt\Exception;

use function openssl_cipher_iv_length;
use function openssl_decrypt;
use function openssl_encrypt;
use function str_ireplace;
use function strlen;
use function strrpos;
use function strtolower;
use function substr;

use const OPENSSL_RAW_DATA;

/**
 * Provides encryption capabilities to Phalcon applications.
 *
 * ```php
 * use Phalcon\Crypt;
 *
 * $crypt = new Crypt();
 *
 * $crypt->setCipher('aes-256-ctr');
 *
 * $key  =
 * "T4\xb1\x8d\xa9\x98\x05\\\x8c\xbe\x1d\x07&[\x99\x18\xa4~Lc1\xbeW\xb3";
 * $input = "The message to be encrypted";
 *
 * $encrypted = $crypt->encrypt($input, $key);
 *
 * echo $crypt->decrypt($encrypted, $key);
 * ```
 *
 * @property string $authTag
 * @property string $authData
 * @property int    $authTagLength
 * @property string $key
 * @property int    $padding
 * @property string $cipher
 * @property array  $availableCiphers
 * @property int    $ivLength
 * @property string $hashAlgo
 * @property bool   $useSigning
 */
trait CryptGettersTrait
{
    /**
     * @var string
     */
    protected string $authTag = '';

    /**
     * @var string
     */
    protected string $authData = '';

    /**
     * @var int
     */
    protected int $authTagLength = 16;

    /**
     * @var string
     */
    protected string $key = '';

    /**
     * @var int
     */
    protected int $padding = 0;

    /**
     * @var string
     */
    protected string $cipher = 'aes-256-cfb';

    /**
     * Available cipher methods.
     *
     * @var array
     */
    protected array $availableCiphers = [];

    /**
     * The cipher iv length.
     *
     * @var int
     */
    protected int $ivLength = 16;

    /**
     * The name of hashing algorithm.
     *
     * @var string
     */
    protected string $hashAlgo = 'sha256';

    /**
     * Whether calculating message digest enabled or not.
     *
     * @var bool
     */
    protected bool $useSigning = true;

    /**
     * Returns a list of available ciphers.
     *
     * @return array
     */
    public function getAvailableCiphers(): array
    {
        return $this->availableCiphers;
    }

    /**
     * @return string
     */
    public function getAuthTag(): string
    {
        return $this->authTag;
    }

    /**
     * @return string
     */
    public function getAuthData(): string
    {
        return $this->authData;
    }

    /**
     * @return int
     */
    public function getAuthTagLength(): int
    {
        return $this->authTagLength;
    }

    /**
     * Returns the current cipher
     *
     * @return string
     */
    public function getCipher(): string
    {
        return $this->cipher;
    }

    /**
     * Get the name of hashing algorithm.
     *
     * @return string
     */
    public function getHashAlgo(): string
    {
        return $this->hashAlgo;
    }

    /**
     * Returns the encryption key
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Removes a padding from a text.
     *
     * If the function detects that the text was not padded, it will return it
     * unmodified.
     *
     * @param string $input
     * @param string $mode
     * @param int    $blockSize
     * @param int    $paddingType
     *
     * @return false|string
     */
    abstract protected function cryptUnpadText(
        string $input,
        string $mode,
        int $blockSize,
        int $paddingType
    );

    /**
     * @param string $mode
     * @param int    $blockSize
     * @param string $decrypted
     *
     * @return string
     */
    protected function decryptCbcEcb(
        string $mode,
        int $blockSize,
        string $decrypted
    ): string {
        if ('-cbc' === $mode || '-ecb' === $mode) {
            $decrypted = $this->cryptUnpadText(
                $decrypted,
                $mode,
                $blockSize,
                $this->padding
            );
        }

        return $decrypted;
    }

    /**
     * @param string $mode
     * @param string $ciphertext
     * @param string $decryptKey
     * @param string $iv
     *
     * @return string
     */
    protected function decryptGcmCcmAuth(
        string $mode,
        string $ciphertext,
        string $decryptKey,
        string $iv
    ): string {
        if (
            ('-gcm' === $mode || '-ccm' === $mode) &&
            true !== empty($this->authData)
        ) {
            return openssl_decrypt(
                $ciphertext,
                $this->cipher,
                $decryptKey,
                OPENSSL_RAW_DATA,
                $iv,
                $this->authTag,
                $this->authData
            );
        }

        return openssl_decrypt(
            $ciphertext,
            $this->cipher,
            $decryptKey,
            OPENSSL_RAW_DATA,
            $iv
        );
    }

    /**
     * @param string $mode
     * @param string $input
     * @param int    $blockSize
     *
     * @return string
     * @throws Exception
     */
    protected function encryptGetPadded(
        string $mode,
        string $input,
        int $blockSize
    ): string {
        if (0 !== $this->padding && ('-cbc' === $mode || '-ecb' === $mode)) {
            return $this->cryptPadText($input, $mode, $blockSize, $this->padding);
        }

        return $input;
    }

    /**
     * @param string $mode
     * @param string $padded
     * @param string $encryptKey
     * @param string $iv
     *
     * @return string
     */
    protected function encryptGcmCcm(
        string $mode,
        string $padded,
        string $encryptKey,
        string $iv
    ): string {
        /**
         * If the mode is "gcm" or "ccm" and auth data has been passed call it
         * with that data
         */
        if (
            ('-gcm' === $mode || '-ccm' === $mode) &&
            true !== empty($this->authData)
        ) {
            return openssl_encrypt(
                $padded,
                $this->cipher,
                $encryptKey,
                OPENSSL_RAW_DATA,
                $iv,
                $this->authTag,
                $this->authData,
                $this->authTagLength
            );
        }

        return openssl_encrypt(
            $padded,
            $this->cipher,
            $encryptKey,
            OPENSSL_RAW_DATA,
            $iv
        );
    }

    /**
     * Initialize available cipher algorithms.
     *
     * @param string $cipher
     *
     * @return int
     */
    protected function getIvLength(string $cipher): int
    {
        return openssl_cipher_iv_length($cipher);
    }

    /**
     * @param string $mode
     *
     * @return int
     */
    protected function getBlockSize(string $mode): int
    {
        if ($this->ivLength > 0) {
            return $this->ivLength;
        }

        return $this->getIvLength(
            str_ireplace('-' . $mode, '', $this->cipher)
        );
    }

    /**
     * @return string
     */
    protected function getMode(): string
    {
        return strtolower(
            substr(
                $this->cipher,
                strrpos($this->cipher, '-') - strlen($this->cipher)
            )
        );
    }
}
