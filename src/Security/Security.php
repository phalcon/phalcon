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

namespace Phalcon\Security;

use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Di\Traits\InjectionAwareTrait;
use Phalcon\Http\RequestInterface;
use Phalcon\Session\ManagerInterface as SessionInterface;
use Phalcon\Support\Str\Traits\StartsWithTrait;

use function crypt;
use function mb_strlen;
use function password_verify;
use function sprintf;
use function var_dump;

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
 * @property int                   $defaultHash
 * @property int                   $numberBytes
 * @property Random                $random
 * @property string|null           $requestToken
 * @property string|null           $token
 * @property string|null           $tokenKey
 * @property string                $tokenKeySessionId
 * @property string                $tokenValueSessionId
 * @property int                   $workFactor
 * @property RequestInterface|null $localRequest
 * @property SessionInterface|null $localSession
 */
class Security implements InjectionAwareInterface
{
    use InjectionAwareTrait;
    use StartsWithTrait;

    public const CRYPT_DEFAULT    = 0;
    public const CRYPT_BLOWFISH   = 4;
    public const CRYPT_BLOWFISH_A = 5;
    public const CRYPT_BLOWFISH_X = 6;
    public const CRYPT_BLOWFISH_Y = 7;
    public const CRYPT_MD5        = 3;
    public const CRYPT_SHA256     = 8;
    public const CRYPT_SHA512     = 9;

    /**
     * @var int
     */
    protected int $defaultHash = self::CRYPT_DEFAULT;

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
    protected ?string $requestToken = null;

    /**
     * @var string|null
     */
    protected ?string $token = null;

    /**
     * @var string|null
     */
    protected ?string $tokenKey = null;

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
    private ?RequestInterface $localRequest = null;

    /**
     * @var SessionInterface|null
     */
    private ?SessionInterface $localSession = null;

    /**
     * Security constructor.
     *
     * @param SessionInterface|null $session
     * @param RequestInterface|null $request
     */
    public function __construct(
        SessionInterface $session = null,
        RequestInterface $request = null
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
        string $tokenKey = null,
        $tokenValue = null,
        bool $destroyIfValid = true
    ): bool {
        /** @var SessionInterface|null $session */
        $session = $this->getLocalService('session', 'localSession');
        var_dump('1');
        if (null !== $session && true === empty($tokenKey)) {
            var_dump('11');
            $tokenKey = $session->get($this->tokenKeySessionId);
        }

        /**
         * If tokenKey does not exist in session return false
         */
        var_dump('2');
        if (true === empty($tokenKey)) {
            var_dump('21');
            return false;
        }

        $userToken = $tokenValue;
        var_dump('3');
        if (null === $tokenValue) {
            var_dump('31');
            /** @var RequestInterface|null $request */
            $request = $this->getLocalService('request', 'localRequest');

            /**
             * We always check if the value is correct in post
             */
            if (null !== $request) {
                var_dump('32');
                /** @var string|null $userToken */
                $userToken = $request->getPost($tokenKey, 'string');
                var_dump('321');
                var_dump($userToken);
            }
        }

        /**
         * The value is the same?
         */
        $knownToken = $this->getRequestToken();
        var_dump('4');
        var_dump($knownToken);
        var_dump($userToken);
        if (null === $knownToken || null === $userToken) {
            var_dump('41');
            return false;
        }
        $equals = hash_equals($knownToken, $userToken);

        /**
         * Remove the key and value of the CSRF token in session
         */
        var_dump('5');
        var_dump($equals);
        if (true === $equals && true === $destroyIfValid) {
            var_dump('51');
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
        $hmac = hash_hmac($algo, $data, $key, $raw);
        if (false === $hmac) {
            throw new Exception('Unknown hashing algorithm: ' . $algo);
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
    public function getRequestToken(): ?string
    {
        if (true === empty($this->requestToken)) {
            return $this->getSessionToken();
        }

        return $this->requestToken;
    }

    /**
     * Returns the value of the CSRF token in session
     *
     * @return string|null
     */
    public function getSessionToken(): ?string
    {
        /** @var SessionInterface|null $session */
        $session = $this->getLocalService('session', 'localSession');
        if (null !== $session) {
            return $session->get($this->tokenValueSessionId);
        }

        return null;
    }

    /**
     * Generate a >22-length pseudo random string to be used as salt for
     * passwords
     *
     * @param int $numberBytes
     *
     * @return string
     * @throws Exception
     */
    public function getSaltBytes(int $numberBytes = 0): string
    {
        while (true) {
            $safeBytes = $this->random->base64Safe($numberBytes);
            if ($safeBytes && mb_strlen($safeBytes) >= $numberBytes) {
                break;
            }
        }

        return $safeBytes;
    }

    /**
     * Generates a pseudo random token value to be used as input's value in a
     * CSRF check
     *
     * @return string
     * @throws Exception
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
     * @throws Exception
     */
    public function getTokenKey(): ?string
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
     * @param string $password
     * @param int    $workFactor
     *
     * @return string
     */
    public function hash(string $password, int $workFactor = 10): string
    {
        try {
            $workFactor = ($workFactor >= 4) ? $workFactor : 4;
            $workFactor = ($workFactor <= 31) ? $workFactor : 31;
            $formatted  = sprintf('%02s', $workFactor);
            $map        = [
                /*
                 * MD5 hashing with a twelve character salt
                 * SHA-256/SHA-512 hash with a sixteen character salt.
                 */
                self::CRYPT_MD5        => [
                    'prefix' => '$1$',
                    'bytes'  => 12,
                    'suffix' => '$',
                ],
                self::CRYPT_SHA256     => [
                    'prefix' => '$5$',
                    'bytes'  => 16,
                    'suffix' => '$',
                ],
                self::CRYPT_SHA512     => [
                    'prefix' => '$6$',
                    'bytes'  => 16,
                    'suffix' => '$',
                ],

                /*
                 * Blowfish hashing with a salt as follows: "$2a$", "$2x$" or
                 * "$2y$", a two digit cost parameter, "$", and 22 characters
                 * from the alphabet "./0-9A-Za-z". Using characters outside of
                 * this range in the salt will cause `crypt()` to return a
                 * zero-length string. The two digit cost parameter is the
                 * base-2 logarithm of the iteration count for the underlying
                 * Blowfish-based hashing algorithm and must be in range 04-31,
                 * values outside this range will cause crypt() to fail.
                 */
                self::CRYPT_BLOWFISH_A => [
                    'prefix' => '$2a$' . $formatted . '$',
                    'bytes'  => 22,
                    'suffix' => '$',
                ],
                self::CRYPT_BLOWFISH_X => [
                    'prefix' => '$2x$' . $formatted . '$',
                    'bytes'  => 22,
                    'suffix' => '$',
                ],
                self::CRYPT_BLOWFISH_Y => [
                    'prefix' => '$2y$' . $formatted . '$',
                    'bytes'  => 22,
                    'suffix' => '$',
                ],
                self::CRYPT_DEFAULT    => [
                    'prefix' => '$2y$' . $formatted . '$',
                    'bytes'  => 22,
                    'suffix' => '$',
                ],
            ];


            $option    = $map[$this->defaultHash] ?? $map[self::CRYPT_DEFAULT];
            $numBytes  = $option['bytes'];
            $saltBytes = $this->getSaltBytes($numBytes);
            $salt      = $option['prefix'] . $saltBytes . $option['suffix'];

            return crypt($password, $salt);
        } catch (\Exception $ex) {
            return '';
        }
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
    private function getLocalService(string $name, string $property)
    {
        if (
            null === $this->$property &&
            null !== $this->container &&
            true === $this->container->has($name)
        ) {
            $this->$property = $this->container->getShared($name);
        }

        return $this->$property;
    }
}
