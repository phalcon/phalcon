<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Encryption;

use Phalcon\Encryption\Crypt\CryptInterface;
use Phalcon\Encryption\Crypt\Exception\DecryptionFailed;
use Phalcon\Encryption\Crypt\Exception\EmptyDecryptionKey;
use Phalcon\Encryption\Crypt\Exception\EmptyEncryptionKey;
use Phalcon\Encryption\Crypt\Exception\EncryptionFailed;
use Phalcon\Encryption\Crypt\Exception\Exception;
use Phalcon\Encryption\Crypt\Exception\InvalidPaddingSize;
use Phalcon\Encryption\Crypt\Exception\IvLengthCalculationFailed;
use Phalcon\Encryption\Crypt\Exception\Mismatch;
use Phalcon\Encryption\Crypt\Exception\MissingAuthData;
use Phalcon\Encryption\Crypt\Exception\MissingOpensslExtension;
use Phalcon\Encryption\Crypt\Exception\RandomBytesGenerationFailed;
use Phalcon\Encryption\Crypt\Exception\UnsupportedAlgorithm;
use Phalcon\Encryption\Crypt\PadFactory;
use ValueError;

/**
 * Provides encryption capabilities to Phalcon applications.
 *
 * ```php
 * use Phalcon\Crypt;
 *
 * $crypt = new Crypt();
 *
 * $crypt->setCipher("aes-256-ctr");
 *
 * $key  =
 * "T4\xb1\x8d\xa9\x98\x05\\\x8c\xbe\x1d\x07&[\x99\x18\xa4~Lc1\xbeW\xb3";
 * $input = "The message to be encrypted";
 *
 * $encrypted = $crypt->encrypt($input, $key);
 *
 * echo $crypt->decrypt($encrypted, $key);
 * ```
 */
class Crypt implements CryptInterface
{
    public const DEFAULT_ALGORITHM = "sha256";
    public const DEFAULT_CIPHER    = "aes-256-cfb";

    /**
     * Padding
     */
    public const PADDING_ANSI_X_923     = 1;
    public const PADDING_DEFAULT        = 0;
    public const PADDING_ISO_10126      = 3;
    public const PADDING_ISO_IEC_7816_4 = 4;
    public const PADDING_PKCS7          = 2;
    public const PADDING_SPACE          = 6;
    public const PADDING_ZERO           = 5;

    /**
     * @var string
     */
    protected string $authData = "";

    /**
     * @var string
     */
    protected string $authTag = "";

    /**
     * @var int
     */
    protected int $authTagLength = 16;

    /**
     * Available cipher methods.
     *
     * @var array
     */
    protected array $availableCiphers = [];

    /**
     * @var string
     */
    protected string $cipher = self::DEFAULT_CIPHER;

    /**
     * The name of hashing algorithm.
     *
     * @var string
     */
    protected string $hashAlgorithm = self::DEFAULT_ALGORITHM;

    /**
     * Memoized `strlen(hash($algo, "", true))` results, keyed by
     * algorithm name. The hash output length is deterministic for a
     * given algorithm, so this collapses the per-decrypt strlen+hash
     * call to a single hash lookup after warm-up.
     *
     * @var array
     */
    protected array $hashLengthCache = [];

    /**
     * The cipher iv length.
     *
     * @var int
     */
    protected int $ivLength = 16;

    /**
     * @var string
     */
    protected string $key = "";

    /**
     * @var int
     */
    protected int $padding = 0;

    /**
     * @var PadFactory
     */
    protected PadFactory $padFactory;

    /**
     * Whether calculating message digest enabled or not.
     *
     * @var bool
     */
    protected bool $useSigning = true;

    /**
     * Crypt constructor.
     *
     * @param string          $cipher
     * @param bool            $useSigning
     * @param PadFactory|null $padFactory
     *
     * @throws Exception
     */
    public function __construct(
        string $cipher = self::DEFAULT_CIPHER,
        bool $useSigning = true,
        PadFactory | null $padFactory = null
    ) {
        if (null === $padFactory) {
            $padFactory = new PadFactory();
        }

        $this->padFactory    = $padFactory;
        $this->hashAlgorithm = self::DEFAULT_ALGORITHM;

        $this
            ->initializeAvailableCiphers()
            ->setCipher($cipher)
            ->useSigning($useSigning)
        ;
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
    public function decrypt(string $input, string | null $key = null): string
    {
        $decryptKey = $this->key;
        if (true !== empty($key)) {
            $decryptKey = $key;
        }

        if (true === empty($decryptKey)) {
            throw new EmptyDecryptionKey();
        }

        $cipher   = $this->cipher;
        $ivLength = $this->ivLength;

        $this->checkCipherHashIsAvailable($cipher, "cipher");

        $mode      = $this->getMode();
        $blockSize = $this->getBlockSize($mode);
        $iv        = mb_substr($input, 0, $ivLength, "8bit");

        /**
         * Check if we have chosen signing and use the hash
         */
        $digest        = "";
        $hashAlgorithm = $this->getHashAlgorithm();
        if (true === $this->useSigning) {
            $hashLength = $this->hashLengthCache[$hashAlgorithm] ?? null;
            if (!$hashLength) {
                $hashLength = strlen(hash($hashAlgorithm, "", true));
                $this->hashLengthCache[$hashAlgorithm] = $hashLength;
            }

            $digest     = mb_substr($input, $ivLength, $hashLength, "8bit");
            $cipherText = mb_substr($input, $ivLength + $hashLength, null, "8bit");
        } else {
            $cipherText = mb_substr($input, $ivLength, null, "8bit");
        }

        $decrypted = $this->decryptGcmCcmAuth(
            $mode,
            $cipherText,
            $decryptKey,
            $iv
        );

        /**
         * The variable below keeps the string (not unpadded). It will be used
         * to compare the hash if we use a digest (signed)
         */
        $padded = $decrypted;

        $decrypted = $this->decryptGetUnpadded(
            $mode,
            $blockSize,
            $decrypted
        );

        if (true === $this->useSigning) {
            /**
             * Checks on the decrypted message digest using the HMAC method.
             */
            if ($digest !== hash_hmac($hashAlgorithm, $padded, $decryptKey, true)) {
                throw new Mismatch("Hash does not match.");
            }
        }

        return $decrypted;
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
        ?string $key = null,
        bool $safe = false
    ): string {
        if ($safe) {
            $input = strtr($input, "-_", "+/")
                . substr("===", (strlen($input) + 3) % 4);
        }

        return $this->decrypt(base64_decode($input), $key);
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
    public function encrypt(string $input, ?string $key = null): string
    {
        $encryptKey = $this->key;
        if (true !== empty($key)) {
            $encryptKey = $key;
        }

        if (true === empty($encryptKey)) {
            throw new EmptyEncryptionKey();
        }

        $cipher   = $this->cipher;
        $ivLength = $this->ivLength;

        $this->checkCipherHashIsAvailable($cipher, "cipher");

        $mode      = $this->getMode();
        $blockSize = $this->getBlockSize($mode);

        try {
            $iv = $this->phpOpensslRandomPseudoBytes($ivLength);
        } catch (ValueError) {
            throw new RandomBytesGenerationFailed();
        }

        if (false === $iv) {
            throw new RandomBytesGenerationFailed();
        }

        $padded = $this->encryptGetPadded($mode, $input, $blockSize);

        /**
         * If the mode is "gcm" or "ccm" and auth data has been passed call it
         * with that data
         */
        $encrypted = $this->encryptGcmCcm($mode, $padded, $encryptKey, $iv);

        if (true === $this->useSigning) {
            $digest = hash_hmac(
                $this->getHashAlgorithm(),
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
        ?string $key = null,
        bool $safe = false
    ): string {
        if ($safe) {
            return rtrim(
                strtr(
                    base64_encode(
                        $this->encrypt($input, $key)
                    ),
                    "+/",
                    "-_"
                ),
                "="
            );
        }

        return base64_encode($this->encrypt($input, $key));
    }

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
     * Returns the auth data
     *
     * @return string
     */
    public function getAuthData(): string
    {
        return $this->authData;
    }

    /**
     * Returns the auth tag
     *
     * @return string
     */
    public function getAuthTag(): string
    {
        return $this->authTag;
    }

    /**
     * Returns the auth tag length
     *
     * @return int
     */
    public function getAuthTagLength(): int
    {
        return $this->authTagLength;
    }

    /**
     * Return a list of registered hashing algorithms suitable for hash_hmac.
     *
     * @return array
     */
    public function getAvailableHashAlgorithms(): array
    {
        if (true === $this->phpFunctionExists("hash_hmac_algos")) {
            return hash_hmac_algos();
        }

        return hash_algos();
    }

    /**
     * Get the name of hashing algorithm.
     *
     * @return string
     */
    public function getHashAlgorithm(): string
    {
        return $this->hashAlgorithm;
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
     * Returns the encryption key
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Returns if the input length for decryption is valid or not
     * (number of bytes required by the cipher).
     *
     * @param string $input
     *
     * @return bool
     */
    public function isValidDecryptLength(string $input): bool
    {
        $length = $this->phpOpensslCipherIvLength($this->cipher);

        if ($length === false) {
            return false;
        }

        return $length <= strlen($input);
    }

    /**
     * @param string $data
     *
     * @return static
     */
    public function setAuthData(string $data): static
    {
        $this->authData = $data;

        return $this;
    }

    /**
     * @param string $tag
     *
     * @return static
     */
    public function setAuthTag(string $tag): static
    {
        $this->authTag = $tag;

        return $this;
    }

    /**
     * @param int $length
     *
     * @return static
     */
    public function setAuthTagLength(int $length): static
    {
        $this->authTagLength = $length;

        return $this;
    }

    /**
     * Sets the cipher algorithm for data encryption and decryption.
     *
     * @param string $cipher
     *
     * @return static
     * @throws Exception
     */
    public function setCipher(string $cipher): static
    {
        $this->checkCipherHashIsAvailable($cipher, "cipher");

        $this->ivLength = $this->getIvLength($cipher);
        $this->cipher   = $cipher;

        return $this;
    }

    /**
     * Sets the encryption key.
     *
     * The `$key` should have been previously generated in a cryptographically
     * safe way.
     *
     * Bad key:
     * "le password"
     *
     * Better (but still unsafe) ->
     * "#1dj8$=dp?.ak//j1V$~%*0X"
     *
     * Good key:
     * "T4\xb1\x8d\xa9\x98\x05\\\x8c\xbe\x1d\x07&[\x99\x18\xa4~Lc1\xbeW\xb3"
     *
     * @param string $key
     *
     * @return static
     */
    public function setKey(string $key): static
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Set the name of hashing algorithm.
     *
     * @param string $hashAlgorithm
     *
     * @return static
     * @throws Exception
     */
    public function setHashAlgorithm(string $hashAlgorithm): static
    {
        $this->checkCipherHashIsAvailable($hashAlgorithm, "hash");

        $this->hashAlgorithm = $hashAlgorithm;

        return $this;
    }

    /**
     * Changes the padding scheme used.
     *
     * @param int $scheme
     *
     * @return static
     */
    public function setPadding(int $scheme): static
    {
        $this->padding = $scheme;

        return $this;
    }

    /**
     * Sets if the calculating message digest must used.
     *
     * @param bool $useSigning
     *
     * @return static
     */
    public function useSigning(bool $useSigning): static
    {
        $this->useSigning = $useSigning;

        return $this;
    }

    /**
     * Checks if a cipher or a hash algorithm is available
     *
     * @param string $cipher
     * @param string $type
     *
     * @throws Exception
     */
    protected function checkCipherHashIsAvailable(string $cipher, string $type): void
    {
        if ("hash" === $type) {
            $method = "getAvailableHashAlgorithms";
        } else {
            $method = "getAvailableCiphers";
        }

        $available = $this->{$method}();
        $lower     = mb_strtolower($cipher);
        if (true !== in_array($lower, $available)) {
            throw new UnsupportedAlgorithm($type, $cipher);
        }
    }

    /**
     * Pads texts before encryption. See
     * [cryptopad](https://www.di-mgt.com.au/cryptopad.html)
     *
     * @param string $input
     * @param string $mode
     * @param int    $blockSize
     * @param int    $paddingType
     *
     * @return string
     * @throws Exception
     */
    protected function cryptPadText(
        string $input,
        string $mode,
        int $blockSize,
        int $paddingType
    ): string {
        $padding     = "";
        $paddingSize = 0;

        if (true === $this->checkIsMode(["cbc", "ecb"], $mode)) {
            $paddingSize = $blockSize - (strlen($input) % $blockSize);

            if ($paddingSize >= 256 || $paddingSize < 0) {
                throw new InvalidPaddingSize();
            }

            $service = $this->padFactory->padNumberToService($paddingType);
            $padding = $this->padFactory->newInstance($service)
                    ->pad($paddingSize);
        }

        if (0 === $paddingSize) {
            return $input;
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
     * @return string
     * @throws Exception
     */
    protected function cryptUnpadText(
        string $input,
        string $mode,
        int $blockSize,
        int $paddingType
    ): string {
        $length      = strlen($input);
        $paddingSize = 0;

        if (
            $length > 0 &&
            ($length % $blockSize === 0) &&
            true === $this->checkIsMode(["cbc", "ecb"], $mode)
        ) {
            $service     = $this->padFactory->padNumberToService($paddingType);
            $paddingSize = $this->padFactory->newInstance($service)
                    ->unpad($input, $blockSize);

            if ($paddingSize > 0) {
                if ($paddingSize <= $blockSize) {
                    if ($paddingSize < $length) {
                        return substr($input, 0, $length - $paddingSize);
                    }

                    return "";
                }

                $paddingSize = 0;
            }
        }

        if (0 === $paddingSize) {
            return $input;
        }

        return "";
    }

    /**
     * @param string $mode
     * @param int    $blockSize
     * @param string $decrypted
     *
     * @return string
     */
    protected function decryptGetUnpadded(
        string $mode,
        int $blockSize,
        string $decrypted
    ): string {
        $localDecrypted = $decrypted;
        if (true === $this->checkIsMode(["cbc", "ecb"], $mode)) {
            $padding   = $this->padding;
            $localDecrypted = $this->cryptUnpadText(
                $decrypted,
                $mode,
                $blockSize,
                $padding
            );
        }

        return $localDecrypted;
    }

    /**
     * @param string $mode
     * @param string $cipherText
     * @param string $decryptKey
     * @param string $iv
     *
     * @return string
     * @throws Exception
     */
    protected function decryptGcmCcmAuth(
        string $mode,
        string $cipherText,
        string $decryptKey,
        string $iv
    ): string {
        $cipher = $this->cipher;

        if (true === $this->checkIsMode(["ccm", "gcm"], $mode)) {
            $authData      = $this->authData;
            $authTagLength = $this->authTagLength;
            $authTag       = substr($cipherText, -$authTagLength);
            $encrypted     = str_replace($authTag, "", $cipherText);

            $decrypted = openssl_decrypt(
                $encrypted,
                $cipher,
                $decryptKey,
                OPENSSL_RAW_DATA,
                $iv,
                $authTag,
                $authData
            );
        } else {
            $decrypted = openssl_decrypt(
                $cipherText,
                $cipher,
                $decryptKey,
                OPENSSL_RAW_DATA,
                $iv
            );
        }

        if (false === $decrypted) {
            throw new DecryptionFailed();
        }

        return $decrypted;
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
        if (
            0 !== $this->padding &&
            true === $this->checkIsMode(["cbc", "ecb"], $mode)
        ) {
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
     * @throws Exception
     */
    protected function encryptGcmCcm(
        string $mode,
        string $padded,
        string $encryptKey,
        string $iv
    ): string {
        $cipher  = $this->cipher;
        $authTag = "";

        /**
         * If the mode is "gcm" or "ccm" and auth data has been passed call it
         * with that data
         */
        if (true === $this->checkIsMode(["ccm", "gcm"], $mode)) {
            $authData = $this->authData;

            if (true === empty($authData)) {
                throw new MissingAuthData();
            }

            $authTag       = $this->authTag;
            $authTagLength = $this->authTagLength;

            $encrypted = openssl_encrypt(
                $padded,
                $cipher,
                $encryptKey,
                OPENSSL_RAW_DATA,
                $iv,
                $authTag,
                $authData,
                $authTagLength
            );

            $this->authTag = $authTag;
        } else {
            $encrypted = openssl_encrypt(
                $padded,
                $cipher,
                $encryptKey,
                OPENSSL_RAW_DATA,
                $iv
            );
        }

        if (false === $encrypted) {
            throw new EncryptionFailed();
        }

        /**
         * Store the tag with encrypted data and return it. In the non AEAD
         * mode this is an empty string
         */
        return $encrypted . $authTag;
    }

    /**
     * Initialize available cipher algorithms.
     *
     * @return static
     * @throws Exception
     */
    protected function initializeAvailableCiphers(): static
    {
        if (true !== $this->phpFunctionExists("openssl_get_cipher_methods")) {
            throw new MissingOpensslExtension();
        }

        $available = openssl_get_cipher_methods(true);
        $allowed   = [];

        foreach ($available as $cipher) {
            if (
                true !== str_starts_with($cipher, "des") &&
                true !== str_starts_with($cipher, "rc2") &&
                true !== str_starts_with($cipher, "rc4") &&
                true !== str_ends_with($cipher, "ecb")
            ) {
                $allowed[$cipher] = $cipher;
            }
        }

        $this->availableCiphers = $allowed;

        return $this;
    }

    /**
     * Checks if a mode (string) is in the values to compare (modes array)
     *
     * @param array  $modes
     * @param string $mode
     *
     * @return bool
     */
    private function checkIsMode(array $modes, string $mode): bool
    {
        return in_array($mode, $modes);
    }

    /**
     * Returns the block size
     *
     * @param string $mode
     *
     * @return int
     * @throws Exception
     */
    private function getBlockSize(string $mode): int
    {
        if ($this->ivLength > 0) {
            return $this->ivLength;
        }

        return $this->getIvLength(
            str_ireplace("-" . $mode, "", $this->cipher)
        );
    }

    /**
     * Initialize available cipher algorithms.
     *
     * @param string $cipher
     *
     * @return int
     * @throws Exception
     */
    private function getIvLength(string $cipher): int
    {
        $length = openssl_cipher_iv_length($cipher);
        if (false === $length) {
            throw new IvLengthCalculationFailed();
        }

        return $length;
    }

    /**
     * Returns the mode (last few characters of the cipher)
     *
     * @return string
     */
    private function getMode(): string
    {
        $position = intval(strrpos($this->cipher, "-"));

        return mb_strtolower(
            substr($this->cipher, $position - strlen($this->cipher) + 1)
        );
    }

    /**
     * @todo to be removed when we get traits
     */
    protected function phpFunctionExists(string $name): bool
    {
        return function_exists($name);
    }

    protected function phpOpensslCipherIvLength(string $cipher): int|bool
    {
        return openssl_cipher_iv_length($cipher);
    }

    protected function phpOpensslRandomPseudoBytes(int $length)
    {
        return openssl_random_pseudo_bytes($length);
    }
}
