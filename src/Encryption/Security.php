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

namespace Phalcon\Encryption;

use Exception as BaseException;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Di\Traits\InjectionAwareTrait;
use Phalcon\Encryption\Security\Exception;
use Phalcon\Encryption\Security\Random;
use Phalcon\Http\RequestInterface;
use Phalcon\Session\ManagerInterface as SessionInterface;
use Phalcon\Traits\Helper\Str\StartsWithTrait;
use ValueError;

use function crypt;
use function hash_equals;
use function hash_hmac;
use function is_array;
use function password_get_info;
use function password_hash;
use function password_verify;
use function sprintf;
use function strlen;

use const PASSWORD_ARGON2_DEFAULT_MEMORY_COST;
use const PASSWORD_ARGON2_DEFAULT_THREADS;
use const PASSWORD_ARGON2_DEFAULT_TIME_COST;
use const PASSWORD_ARGON2I;
use const PASSWORD_ARGON2ID;
use const PASSWORD_BCRYPT;

/**
 * This component provides a set of functions to improve the security in Phalcon
 * applications
 *
 *```php
 * $login    = $this->request->getPost("login");
 * $password = $this->request->getPost("password");
 *
 * $user = Users::findFirstByLogin($login);
 *
 * if ($user) {
 *     if ($this->security->checkHash($password, $user->password)) {
 *         // The password is valid
 *     }
 * }
 *```
 *
 * @phpstan-type TOptions = array{
 *     cost?: int,
 *     memory_cost: int,
 *     time_cost: int,
 *     threads: int
 * }
 */
class Security implements InjectionAwareInterface
{
    use InjectionAwareTrait;
    use StartsWithTrait;

    public const CRYPT_ARGON2I    = 10;
    public const CRYPT_ARGON2ID   = 11;
    public const CRYPT_BCRYPT     = 0;
    public const CRYPT_BLOWFISH   = 4;
    public const CRYPT_BLOWFISH_A = 5;
    public const CRYPT_BLOWFISH_X = 6;
    public const CRYPT_BLOWFISH_Y = 7;
    public const CRYPT_DEFAULT    = 0;
    public const CRYPT_MD5        = 3;
    public const CRYPT_SHA256     = 8;
    public const CRYPT_SHA512     = 9;

    /**
     * @var int
     */
    protected int $defaultHash = Security::CRYPT_DEFAULT;

    /**
     * @var int
     */
    protected int $numberBytes = 16;

    /**
     * @var Random
     */
    protected Random $random;

    /**
     * @var string|null
     */
    protected string | null $requestToken = null;

    /**
     * @var string|null
     */
    protected string | null $token = null;

    /**
     * @var string|null
     */
    protected string | null $tokenKey = null;

    /**
     * @var string
     */
    protected string $tokenKeySessionId = '$PHALCON/CSRF/KEY$';

    /**
     * @var string
     */
    protected string $tokenValueSessionId = '$PHALCON/CSRF$';

    /**
     * @var int
     */
    protected int $workFactor = 10;

    /**
     * @var RequestInterface|null
     */
    private RequestInterface | null $localRequest = null;

    /**
     * @var SessionInterface|null
     */
    private SessionInterface | null $localSession = null;

    /**
     * Security constructor.
     *
     * @param SessionInterface|null $session
     * @param RequestInterface|null $request
     */
    public function __construct(
        SessionInterface | null $session = null,
        RequestInterface | null $request = null
    ) {
        $this->random       = new Random();
        $this->localRequest = $request;
        $this->localSession = $session;
    }

    /**
     * Checks a plain text password and its hash version to check if the
     * password matches
     *
     * @param string $password
     * @param string $passwordHash
     * @param int    $maxPassLength
     *
     * @return bool
     */
    public function checkHash(
        string $password,
        string $passwordHash,
        int $maxPassLength = 0
    ): bool {
        if ($maxPassLength > 0 && mb_strlen($password) > $maxPassLength) {
            return false;
        }

        return password_verify($password, $passwordHash);
    }

    /**
     * Check if the CSRF token sent in the request is the same that the current
     * in session
     *
     * @param string|null $tokenKey
     * @param mixed|null  $tokenValue
     * @param bool        $destroyIfValid
     *
     * @return bool
     */
    public function checkToken(
        string | null $tokenKey = null,
        mixed $tokenValue = null,
        bool $destroyIfValid = true
    ): bool {
        $tokenKey = $this->processTokenKey($tokenKey);

        /**
         * If tokenKey does not exist in session return false
         */
        if (empty($tokenKey)) {
            return false;
        }

        /**
         * The value is the same?
         */
        $userToken  = $this->processUserToken($tokenKey, $tokenValue);
        $knownToken = $this->getRequestToken();
        if (null === $knownToken || null === $userToken) {
            return false;
        }
        $equals = hash_equals($knownToken, $userToken);

        /**
         * Remove the key and value of the CSRF token in session
         */
        if (true === $equals && true === $destroyIfValid) {
            $this->destroyToken();
        }

        return $equals;
    }

    /**
     * Computes a HMAC
     *
     * @param string $data
     * @param string $key
     * @param string $algo
     * @param bool   $raw
     *
     * @return string
     * @throws Exception
     */
    public function computeHmac(
        string $data,
        string $key,
        string $algo,
        bool $raw = false
    ): string {
        try {
            $hmac = hash_hmac($algo, $data, $key, $raw);
        } catch (ValueError $ex) {
            throw new Exception($ex->getMessage());
        }

        return $hmac;
    }

    /**
     * Removes the value of the CSRF token and key from session
     *
     * @return $this
     */
    public function destroyToken(): Security
    {
        /** @var SessionInterface|null $session */
        $session = $this->getLocalService('session', 'localSession');
        if (null !== $session) {
            $session->remove($this->tokenKeySessionId);
            $session->remove($this->tokenValueSessionId);
        }

        $this->token        = null;
        $this->tokenKey     = null;
        $this->requestToken = null;

        return $this;
    }

    /**
     * Returns the default hash
     *
     * @return int
     */
    public function getDefaultHash(): int
    {
        return $this->defaultHash;
    }

    /**
     * Returns information regarding a hash
     *
     * @param string $hash
     *
     * @return array
     */
    public function getHashInformation(string $hash): array
    {
        $info = password_get_info($hash);

        return is_array($info) ? $info : [];
    }

    /**
     * Returns a secure random number generator instance
     *
     * @return Random
     */
    public function getRandom(): Random
    {
        return $this->random;
    }

    /**
     * Returns a number of bytes to be generated by the openssl pseudo random
     * generator
     *
     * @return int
     */
    public function getRandomBytes(): int
    {
        return $this->numberBytes;
    }

    /**
     * Returns the value of the CSRF token for the current request.
     *
     * @return string|null
     */
    public function getRequestToken(): string | null
    {
        if (empty($this->requestToken)) {
            return $this->getSessionToken();
        }

        return $this->requestToken;
    }

    /**
     * Generate a >22-length pseudo random string to be used as salt for
     * passwords
     *
     * @param int $numberBytes
     *
     * @return string
     * @throws BaseException
     */
    public function getSaltBytes(int $numberBytes = 0): string
    {
        while (true) {
            $safeBytes = $this->random->base64Safe($numberBytes);
            if ($safeBytes && strlen($safeBytes) >= $numberBytes) {
                break;
            }
        }

        return $safeBytes;
    }

    /**
     * Returns the value of the CSRF token in session
     *
     * @return string|null
     */
    public function getSessionToken(): string | null
    {
        /** @var SessionInterface|null $session */
        $session = $this->getLocalService('session', 'localSession');
        if (null !== $session) {
            return $session->get($this->tokenValueSessionId);
        }

        return null;
    }

    /**
     * Generates a pseudo random token value to be used as input's value in a
     * CSRF check
     *
     * @return string
     * @throws BaseException
     */
    public function getToken(): string
    {
        if (null === $this->token) {
            $this->requestToken = $this->getSessionToken();
            $this->token        = $this->random->base64Safe($this->numberBytes);

            /** @var SessionInterface|null $session */
            $session = $this->getLocalService('session', 'localSession');
            if (null !== $session) {
                $session->set(
                    $this->tokenValueSessionId,
                    $this->token
                );
            }
        }

        return $this->token;
    }

    /**
     * Generates a pseudo random token key to be used as input's name in a CSRF
     * check
     *
     * @return string|null
     * @throws BaseException
     */
    public function getTokenKey(): string | null
    {
        if (null === $this->tokenKey) {
            /** @var SessionInterface|null $session */
            $session = $this->getLocalService('session', 'localSession');
            if (null !== $session) {
                $this->tokenKey = $this->random->base64Safe($this->numberBytes);
                $session->set(
                    $this->tokenKeySessionId,
                    $this->tokenKey
                );
            }
        }

        return $this->tokenKey;
    }

    /**
     * @return int
     */
    public function getWorkFactor(): int
    {
        return $this->workFactor;
    }

    /**
     * Creates a password hash using bcrypt with a pseudo random salt
     *
     * @param string   $password
     * @param TOptions $options
     *
     * @return string
     */
    public function hash(string $password, array $options = []): string
    {
        $cost = $this->processCost($options);

        $formatted = sprintf('%02s', $cost);
        $prefix    = "";
        $bytes     = 22;
        /**
         * This distinguishes between `password_hash` and non `password_hash`
         * hashing.
         */
        $legacy = true;

        switch ($this->defaultHash) {
            case self::CRYPT_MD5:
                /*
                 * MD5 hashing with a twelve character salt
                 * SHA-256/SHA-512 hash with a sixteen character salt.
                 */
                $prefix = "$1$";
                $bytes  = 12;
                break;
            case self::CRYPT_SHA256:
                $prefix = "$5$";
                $bytes  = 16;
                break;
            case self::CRYPT_SHA512:
                $prefix = "$6$";
                $bytes  = 16;
                break;
            /*
             * Blowfish hashing with a salt as follows: "$2a$", "$2x$" or
             * "$2y$", a two digit cost parameter, "$", and 22 characters
             * from the alphabet "./0-9A-Za-z". Using characters outside
             * this range in the salt will cause `crypt()` to return a
             * zero-length string. The two digit cost parameter is the
             * base-2 logarithm of the iteration count for the underlying
             * Blowfish-based hashing algorithm and must be in range 04-31,
             * values outside this range will cause crypt() to fail.
             */
            case self::CRYPT_BLOWFISH_A:
                $prefix = sprintf("$2a$%s$", $formatted);
                break;
            case self::CRYPT_BLOWFISH_X:
                $prefix = sprintf("$2x$%s$", $formatted);
                break;
            default:
                $legacy = false;
                break;
        }

        if (true === $legacy) {
            $salt = $prefix . $this->getSaltBytes($bytes) . "$";
            return (string)crypt($password, $salt);
        }

        /**
         * This is using password_hash
         *
         * We will not provide a "salt" but let PHP calculate it.
         */
        $options = [
            "cost" => $cost,
        ];

        $algorithm = $this->processAlgorithm();
        $arguments = $this->processArgonOptions($options);

        return (string)password_hash($password, $algorithm, $arguments);
    }

    /**
     * Checks if a password hash is a valid bcrypt's hash
     *
     * @param string $passwordHash
     *
     * @return bool
     */
    public function isLegacyHash(string $passwordHash): bool
    {
        return $this->toStartsWith($passwordHash, '$2a$');
    }

    /**
     * Sets the default hash
     *
     * @param int $defaultHash
     *
     * @return Security
     */
    public function setDefaultHash(int $defaultHash): Security
    {
        $this->defaultHash = $defaultHash;

        return $this;
    }

    /**
     * Sets a number of bytes to be generated by the openssl pseudo random
     * generator
     *
     * @param int $randomBytes
     *
     * @return Security
     */
    public function setRandomBytes(int $randomBytes): Security
    {
        $this->numberBytes = $randomBytes;

        return $this;
    }

    /**
     * Sets the work factor
     *
     * @param int $workFactor
     *
     * @return $this
     */
    public function setWorkFactor(int $workFactor): Security
    {
        $this->workFactor = $workFactor;

        return $this;
    }

    /**
     * @param string $name
     * @param string $property
     *
     * @return RequestInterface|SessionInterface|null
     */
    protected function getLocalService(
        string $name,
        string $property
    ): RequestInterface | SessionInterface | null {
        if (
            null === $this->$property &&
            null !== $this->container &&
            true === $this->container->has($name)
        ) {
            $this->$property = $this->container->getShared($name);
        }

        return $this->$property;
    }

    /**
     * Checks the algorithm for `password_hash`. If it is argon based, it
     * returns the relevant constant
     *
     * @return string
     */
    private function processAlgorithm(): string
    {
        $algorithm = PASSWORD_BCRYPT;

        if ($this->defaultHash === self::CRYPT_ARGON2I) {
            $algorithm = PASSWORD_ARGON2I;
        } elseif ($this->defaultHash === self::CRYPT_ARGON2ID) {
            $algorithm = PASSWORD_ARGON2ID;
        }

        return $algorithm;
    }

    /**
     * We check if the algorithm is Argon based. If yes, options are set for
     * `password_hash` such as `memory_cost`, `time_cost` and `threads`
     *
     * @param TOptions $options
     *
     * @return array<string, int>
     */
    private function processArgonOptions(array $options): array
    {
        if (
            $this->defaultHash === self::CRYPT_ARGON2I ||
            $this->defaultHash === self::CRYPT_ARGON2ID
        ) {
            $options["memory_cost"] = $options["memory_cost"] ?? PASSWORD_ARGON2_DEFAULT_MEMORY_COST;
            $options["time_cost"]   = $options["time_cost"] ?? PASSWORD_ARGON2_DEFAULT_TIME_COST;
            $options["threads"]     = $options["threads"] ?? PASSWORD_ARGON2_DEFAULT_THREADS;
        }

        return $options;
    }

    /**
     * Checks the options array for `cost`. If not defined it is set to 10.
     * It also checks the cost if it is between 4 and 31
     *
     * @param TOptions $options
     *
     * @return int
     */
    private function processCost(array $options = []): int
    {
        $cost = $options["cost"] ?? 10;
        $cost = ($cost >= 4) ? $cost : 4;

        return ($cost <= 31) ? $cost : 31;
    }

    /**
     * @param string|null $tokenKey
     *
     * @return string|null
     */
    private function processTokenKey(string | null $tokenKey = null): string | null
    {
        /** @var SessionInterface|null $session */
        $session = $this->getLocalService('session', 'localSession');
        if (null !== $session && empty($tokenKey)) {
            $tokenKey = $session->get($this->tokenKeySessionId);
        }

        return $tokenKey;
    }

    /**
     * @param string      $tokenKey
     * @param string|null $tokenValue
     *
     * @return string|null
     */
    private function processUserToken(
        string $tokenKey,
        string | null $tokenValue = null
    ): string | null {
        $userToken = $tokenValue;
        if (null === $tokenValue) {
            /** @var RequestInterface|null $request */
            $request = $this->getLocalService('request', 'localRequest');

            /**
             * We always check if the value is correct in post
             */
            if (null !== $request) {
                /** @var string|null $userToken */
                $userToken = $request->getPost($tokenKey, 'string');
            }
        }

        return $userToken;
    }
}
