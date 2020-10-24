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

namespace Phalcon\Crypt;

use Phalcon\Crypt\Traits\CryptGettersTrait;
use Phalcon\Support\Str\Traits\EndsWithTrait;
use Phalcon\Support\Str\Traits\StartsWithTrait;
use Phalcon\Support\Str\Traits\UpperTrait;
use Phalcon\Support\Traits\PhpFunctionTrait;

use function base64_decode;
use function base64_encode;
use function chr;
use function hash;
use function hash_hmac;
use function openssl_get_cipher_methods;
use function openssl_random_pseudo_bytes;
use function ord;
use function rand;
use function range;
use function rtrim;
use function sprintf;
use function str_repeat;
use function strlen;
use function substr;

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
class Crypt implements CryptInterface
{
    use CryptGettersTrait;
    use EndsWithTrait;
    use PhpFunctionTrait;
    use StartsWithTrait;
    use UpperTrait;

    public const PADDING_ANSI_X_923     = 1;
    public const PADDING_DEFAULT        = 0;
    public const PADDING_ISO_10126      = 3;
    public const PADDING_ISO_IEC_7816_4 = 4;
    public const PADDING_PKCS7          = 2;
    public const PADDING_SPACE          = 6;
    public const PADDING_ZERO           = 5;

    /**
     * Crypt constructor.
     *
     * @param string $cipher
     * @param bool   $useSigning
     *
     * @throws Exception
     */
    public function __construct(
        string $cipher = 'aes-256-cfb',
        bool $useSigning = false
    ) {
        $this->initializeAvailableCiphers();

        $this->setCipher($cipher);
        $this->useSigning($useSigning);
    }

    /**
     * Decrypts an encrypted text.
     *
     * ```php
     * $encrypted = $crypt->decrypt(
     *     $encrypted,
     *     "T4\xb1\x8d\xa9\x98\x05\\\x8c\xbe\x1d\x07&[\x99\x18\xa4~Lc1\xbeW\xb3"
     * );
     * ```
     *
     * @param string      $input
     * @param string|null $key
     *
     * @return string
     * @throws Exception
     * @throws Mismatch
     */
    public function decrypt(string $input, string $key = null): string
    {
        $decryptKey = (true === empty($key)) ? $this->key : $key;

        if (true === empty($decryptKey)) {
            throw new Exception('Decryption key cannot be empty');
        }

        $this->checkCipherIsAvailable($this->cipher, 'cipher');
        $mode      = $this->getMode();
        $blockSize = $this->getBlockSize($mode);
        $iv        = mb_substr($input, 0, $this->ivLength, '8bit');

        if (true === $this->useSigning) {
            $hashAlgorithm = $this->getHashAlgo();
            $hashLength    = strlen(hash($hashAlgorithm, '', true));
            $hash          = mb_substr($input, $this->ivLength, $hashLength, '8bit');
            $ciphertext    = mb_substr($input, $this->ivLength + $hashLength, null, '8bit');

            $decrypted = $this->decryptGcmCcmAuth(
                $mode,
                $ciphertext,
                $decryptKey,
                $iv
            );

            $decrypted = $this->decryptCbcEcb(
                $mode,
                $blockSize,
                $decrypted
            );

            /**
             * Checks on the decrypted's message digest using the HMAC method.
             */
            if ($hash !== hash_hmac($hashAlgorithm, $decrypted, $decryptKey, true)) {
                throw new Mismatch('Hash does not match.');
            }

            return $decrypted;
        }

        $ciphertext = mb_substr($input, $this->ivLength, null, '8bit');
        $decrypted  = $this->decryptGcmCcmAuth(
            $mode,
            $ciphertext,
            $decryptKey,
            $iv
        );

        return $this->decryptCbcEcb(
            $mode,
            $blockSize,
            $decrypted
        );
    }

    /**
     * Decrypt a text that is coded as a base64 string.
     *
     * @param string     $input
     * @param mixed|null $key
     * @param bool       $safe
     *
     * @return string
     * @throws Exception
     * @throws Mismatch
     */
    public function decryptBase64(
        string $input,
        $key = null,
        bool $safe = false
    ): string {
        if (true === $safe) {
            $input = strtr($input, '-_', '+/')
                . substr('===', (strlen($input) + 3) % 4);
        }

        return $this->decrypt(
            base64_decode($input),
            $key
        );
    }

    /**
     * Encrypts a text.
     *
     * ```php
     * $encrypted = $crypt->encrypt(
     *     "Top secret",
     *     "T4\xb1\x8d\xa9\x98\x05\\\x8c\xbe\x1d\x07&[\x99\x18\xa4~Lc1\xbeW\xb3"
     * );
     * ```
     *
     * @param string      $input
     * @param string|null $key
     *
     * @return string
     * @throws Exception
     */
    public function encrypt(string $input, string $key = null): string
    {
        $encryptKey = (true === empty($key)) ? $this->key : $key;

        if (true === empty($encryptKey)) {
            throw new Exception('Encryption key cannot be empty');
        }

        $this->checkCipherIsAvailable($this->cipher, 'cipher');
        $mode      = $this->getMode();
        $blockSize = $this->getBlockSize($mode);
        $iv        = openssl_random_pseudo_bytes($this->ivLength);
        $padded    = $this->encryptGetPadded($mode, $input, $blockSize);

        /**
         * If the mode is "gcm" or "ccm" and auth data has been passed call it
         * with that data
         */
        $encrypted = $this->encryptGcmCcm($mode, $padded, $encryptKey, $iv);

        if (true === $this->useSigning) {
            $digest = hash_hmac(
                $this->getHashAlgo(),
                $padded,
                $encryptKey,
                true
            );

            return $iv . $digest . $encrypted;
        }

        return $iv . $encrypted;
    }

    /**
     * Encrypts a text returning the result as a base64 string.
     *
     * @param string     $input
     * @param mixed|null $key
     * @param bool       $safe
     *
     * @return string
     * @throws Exception
     */
    public function encryptBase64(
        string $input,
        $key = null,
        bool $safe = false
    ): string {
        if (true === $safe) {
            return rtrim(
                strtr(
                    base64_encode(
                        $this->encrypt($input, $key)
                    ),
                    '+/',
                    '-_'
                ),
                '='
            );
        }

        return base64_encode($this->encrypt($input, $key));
    }

    /**
     * Return a list of registered hashing algorithms suitable for hash_hmac.
     *
     * @return array
     */
    public function getAvailableHashAlgos(): array
    {
        $algos = hash_algos();

        if (true === function_exists('hash_hmac_algos')) {
            $algos = hash_hmac_algos();
        }

        $available = [];
        foreach ($algos as $algo) {
            $upper = $this->toUpper($algo);

            $available[$upper] = $algo;
        }

        return $available;
    }

    /**
     * @param string $tag
     *
     * @return CryptInterface
     */
    public function setAuthTag(string $tag): CryptInterface
    {
        $this->authTag = $tag;

        return $this;
    }

    /**
     * @param string $data
     *
     * @return CryptInterface
     */
    public function setAuthData(string $data): CryptInterface
    {
        $this->authData = $data;

        return $this;
    }

    /**
     * @param int $length
     *
     * @return CryptInterface
     */
    public function setAuthTagLength(int $length): CryptInterface
    {
        $this->authTagLength = $length;

        return $this;
    }

    /**
     * Sets the cipher algorithm for data encryption and decryption.
     *
     * The `aes-256-gcm' is the preferable cipher, but it is not usable
     * until the openssl library is upgraded, which is available in PHP 7.1.
     *
     * The `aes-256-ctr' is arguably the best choice for cipher
     * algorithm for current openssl library version.
     *
     * @param string $cipher
     *
     * @return CryptInterface
     * @throws Exception
     */
    public function setCipher(string $cipher): CryptInterface
    {
        $this->checkCipherIsAvailable($cipher, 'cipher');

        $this->ivLength = $this->getIvLength($cipher);
        $this->cipher   = $cipher;

        return $this;
    }

    /**
     * Set the name of hashing algorithm.
     *
     * @param string $hashAlgo
     *
     * @return CryptInterface
     * @throws Exception
     */
    public function setHashAlgo(string $hashAlgo): CryptInterface
    {
        $this->checkCipherIsAvailable($hashAlgo, 'hash');

        $this->hashAlgo = $hashAlgo;

        return $this;
    }

    /**
     * Sets the encryption key.
     *
     * The `$key' should have been previously generated in a cryptographically
     * safe way.
     *
     * Bad key:
     * "le password"
     *
     * Better (but still unsafe):
     * "#1dj8$=dp?.ak//j1V$~%*0X"
     *
     * Good key:
     * "T4\xb1\x8d\xa9\x98\x05\\\x8c\xbe\x1d\x07&[\x99\x18\xa4~Lc1\xbeW\xb3"
     *
     * @param string $key
     *
     * @return CryptInterface
     */
    public function setKey(string $key): CryptInterface
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Changes the padding scheme used.
     *
     * @param int $scheme
     *
     * @return CryptInterface
     */
    public function setPadding(int $scheme): CryptInterface
    {
        $this->padding = $scheme;

        return $this;
    }

    /**
     * Sets if the calculating message digest must used.
     *
     * @param bool $useSigning
     *
     * @return CryptInterface
     */
    public function useSigning(bool $useSigning): CryptInterface
    {
        $this->useSigning = $useSigning;

        return $this;
    }

    /**
     * Initialize available cipher algorithms.
     */
    protected function initializeAvailableCiphers(): void
    {
        if (true !== $this->phpFunctionExists('openssl_get_cipher_methods')) {
            throw new Exception('openssl extension is required');
        }

        $available = openssl_get_cipher_methods(true);
        $allowed   = [];

        foreach ($available as $cipher) {
            if (
                true !== $this->toStartsWith($cipher, 'des') &&
                true !== $this->toStartsWith($cipher, 'rc2') &&
                true !== $this->toStartsWith($cipher, 'rc4') &&
                true !== $this->toEndsWith($cipher, 'ecb')
            ) {
                $upper           = $this->toUpper($cipher);
                $allowed[$upper] = $upper;
            }
        }

        $this->availableCiphers = $allowed;
    }

    /**
     * Pads texts before encryption. See
     * [cryptopad](http://www.di-mgt.com.au/cryptopad.html)
     *
     * @param string $input
     * @param string $mode
     * @param int    $blockSize
     * @param int    $paddingType
     *
     * @return string
     */
    protected function cryptPadText(
        string $input,
        string $mode,
        int $blockSize,
        int $paddingType
    ): string {
        $padding     = null;
        $paddingSize = 0;
        if ('cbc' === $mode || 'ecb' === $mode) {
            $paddingSize = $blockSize - (strlen($input) % $blockSize);

            if ($paddingSize >= 256) {
                throw new Exception('Block size is bigger than 256');
            }

            switch ($paddingType) {
                case self::PADDING_ANSI_X_923:
                    $padding = str_repeat(chr(0), $paddingSize - 1) . chr($paddingSize);
                    break;

                case self::PADDING_PKCS7:
                    $padding = str_repeat(chr($paddingSize), $paddingSize);
                    break;

                case self::PADDING_ISO_10126:
                    $padding = '';
                    $range   = range(0, $paddingSize - 2);
                    foreach ($range as $item) {
                        $padding .= chr(rand());
                    }

                    $padding .= chr($paddingSize);

                    break;

                case self::PADDING_ISO_IEC_7816_4:
                    $padding = chr(0x80) . str_repeat(chr(0), $paddingSize - 1);
                    break;

                case self::PADDING_ZERO:
                    $padding = str_repeat(chr(0), $paddingSize);
                    break;

                case self::PADDING_SPACE:
                    $padding = str_repeat(' ', $paddingSize);
                    break;

                default:
                    $paddingSize = 0;
                    break;
            }
        }

        if (!$paddingSize) {
            return $input;
        }

        if ($paddingSize > $blockSize) {
            throw new Exception("Invalid padding size");
        }

        return $input . substr($padding, 0, $paddingSize);
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
    protected function cryptUnpadText(
        string $input,
        string $mode,
        int $blockSize,
        int $paddingType
    ) {
        $paddingSize = 0;
        $length      = strlen($input);
        if (
            $length > 0 &&
            ($length % $blockSize == 0) &&
            ('cbc' === $mode || 'ecb' === $mode)
        ) {
            switch ($paddingType) {
                case self::PADDING_ANSI_X_923:
                    $last = substr($input, $length - 1, 1);
                    $ord  = (int) ord($last);

                    if ($ord <= $blockSize) {
                        $paddingSize = $ord;
                        $padding     = str_repeat(chr(0), $paddingSize - 1) . $last;

                        if (substr($input, $length - $paddingSize) != $padding) {
                            $paddingSize = 0;
                        }
                    }
                    break;

                case self::PADDING_PKCS7:
                    $last = substr($input, $length - 1, 1);
                    $ord  = (int) ord($last);

                    if ($ord <= $blockSize) {
                        $paddingSize = $ord;
                        $padding     = str_repeat(chr($paddingSize), $paddingSize);

                        if (substr($input, $length - $paddingSize) != $padding) {
                            $paddingSize = 0;
                        }
                    }
                    break;

                case self::PADDING_ISO_10126:
                    $last        = substr($input, $length - 1, 1);
                    $paddingSize = (int) ord($last);
                    break;

                case self::PADDING_ISO_IEC_7816_4:
                    $counter = $length - 1;

                    while ($counter > 0 && $input[$counter] == 0x00 && $paddingSize < $blockSize) {
                        $paddingSize++;
                        $counter--;
                    }

                    if ($input[$counter] == 0x80) {
                        $paddingSize++;
                    } else {
                        $paddingSize = 0;
                    }
                    break;

                case self::PADDING_ZERO:
                    $counter = $length - 1;

                    while ($counter >= 0 && $input[$counter] == 0x00 && $paddingSize <= $blockSize) {
                        $paddingSize++;
                        $counter--;
                    }
                    break;

                case self::PADDING_SPACE:
                    $counter = $length - 1;

                    while ($counter >= 0 && $input[$counter] == 0x20 && $paddingSize <= $blockSize) {
                        $paddingSize++;
                        $counter--;
                    }
                    break;

                default:
                    break;
            }

            if ($paddingSize && $paddingSize <= $blockSize) {
                if ($paddingSize < $length) {
                    return substr($input, 0, $length - $paddingSize);
                }

                return '';
            } else {
                $paddingSize = 0;
            }
        }

        if (!$paddingSize) {
            return $input;
        }
    }

    /**
     * @param string $cipher
     * @param string $type
     *
     * @throws Exception
     */
    protected function checkCipherIsAvailable(string $cipher, string $type): void
    {
        $method    = ('hash' === $cipher) ? 'getAvailableHashAlgos' : 'getAvailableCiphers';
        $available = $this->$method();
        $upper     = $this->toUpper($cipher);
        if (true !== isset($available[$upper])) {
            throw new Exception(
                sprintf(
                    'The %s algorithm "%s" is not supported on this system.',
                    $type,
                    $cipher
                )
            );
        }
    }
}
