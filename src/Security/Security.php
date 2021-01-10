<?php
/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phiz\Security;

//use Phiz\Di\DiInterface;
use Phiz\Di\AbstractInjectionAware;
use Phiz\Http\RequestInterface;
use Phiz\Session\ManagerInterface as SessionInterface;

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
 */
class Security extends AbstractInjectionAware
{
    const CRYPT_DEFAULT    = 0;
    const CRYPT_BLOWFISH   = 4;
    const CRYPT_BLOWFISH_A = 5;
    const CRYPT_BLOWFISH_X = 6;
    const CRYPT_BLOWFISH_Y = 7;
    const CRYPT_EXT_DES    = 2;
    const CRYPT_MD5        = 3;
    const CRYPT_SHA256     = 8;
    const CRYPT_SHA512     = 9;
    const CRYPT_STD_DES    = 1;

    /**
     * @var int|null
     */
    protected int $defaultHash;

    /**
     * @var int
     */
    protected int $numberBytes = 16;

    /**
     * @var Random
     */
    protected Random $random;

    /**
     * @var string | null
     */
    protected ?string $requestToken = null;

    /**
     * @var string|null
     */
    protected ?string $token = null;

    /**
     * @var string|null
     */
    protected ?string $tokenKey= null;

    /**
     * @var string
     */
    protected ?string $tokenKeySessionId = "\$PHALCON/CSRF/KEY\$";

    /**
     * @var string
     */
    protected ?string $tokenValueSessionId = "\$PHALCON/CSRF\$";

    /**
     * @var int
     */
    protected int $workFactor = 10;/// { get };

    /**
     * @var SessionInterface|null
     */
    private ?SessionInterface $localSession = null;

    /**
     * @var RequestInterface|null
     */
    private ?RequestInterface $localRequest = null;

    /**
     * Phiz\Security constructor
     */
    public function __construct(SessionInterface $session = null, RequestInterface $request = null)
    {
         $this->random       = new Random();
         $this->localRequest = $request;
         $this->localSession = $session;
    }

    /**
     * Checks a plain text password and its hash version to check if the
     * password matches
     */
    public function checkHash(string $password, string $passwordHash, int $maxPassLength = 0) : bool
    {

        if ($maxPassLength > 0 && strlen($password) > $maxPassLength) {
            return false;
        }

         $cryptedHash = (string) crypt($password, $passwordHash);

         $cryptedLength = strlen($cryptedHash);
            $passwordLength = strlen($passwordHash);

         $cryptedHash .= $passwordHash;

         $sum = $cryptedLength - $passwordLength;
         // the hash check works on numeric values, not strings
         
         $split = array_map('intval', str_split($passwordHash));
         $hash = array_map('intval', str_split($cryptedHash));
         for($i = 0; $i < count($split); $i++) {
             $sum = $sum | ($hash[$i] ^ $split[$i]);
         }

         return 0 === $sum;
    }

    /**
     * Check if the CSRF token sent in the request is the same that the current
     * in session
     */
    public function checkToken($tokenKey = null, $tokenValue = null,
            bool $destroyIfValid = true) : bool
    {

        $session = $this->getLocalSession();

        if ($session && !$tokenKey) {
             $tokenKey = $session->get(
                $this->tokenKeySessionId
            );
        }

        /**
         * If tokenKey does not exist in session return false
         */
        if (!$tokenKey) {
            return false;
        }

        if (!$tokenValue) {
             $request = $this->getLocalRequest();

            /**
             * We always check if the value is correct in post
             */
             $userToken = $request->getPost($tokenKey, "string");
        } else {
             $userToken = $tokenValue;
        }

        /**
         * The value is the same?
         */
         $knownToken = $this->getRequestToken();

        if (null === $knownToken) {
            return false;
        }

         $equals = hash_equals($knownToken, $userToken);

        /**
         * Remove the key and value of the CSRF token in session
         */
        if ($equals && $destroyIfValid) {
            $this->destroyToken();
        }

        return $knownToken;
    }

    /**
     * Computes a HMAC
     */
    public function computeHmac(string $data, string $key, string $algo, bool $aw = false) : string
    {

         $hmac = hash_hmac($algo, $data, $key, $raw);

        if (!$hmac) {
            throw new Exception(
                sprintf(
                    "Unknown hashing algorithm: %s",
                    $algo
                )
            );
        }

        return $hmac;
    }

    /**
     * Removes the value of the CSRF token and key from session
     */
    public function destroyToken() : Security
    {

         $session = $this->getLocalSession();

        if ($session) {
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
      */
    public function getDefaultHash() : ?int
    {
        return $this->defaultHash;
    }

    /**
     * Returns a secure random number generator instance
     */
    public function getRandom() : Random
    {
        return $this->random;
    }

    /**
     * Returns a number of bytes to be generated by the openssl pseudo random
     * generator
     */
    public function getRandomBytes() : int
    {
        return $this->numberBytes;
    }

    /**
     * Returns the value of the CSRF token for the current request.
     */
    public function getRequestToken() : ?string
    {
        if (empty($this->requestToken)) {
            return $this->getSessionToken();
        }

        return $this->requestToken;
    }

    /**
     * Returns the value of the CSRF token in session
     */
    public function getSessionToken() : ?string
    {

         $session = $this->getLocalSession();

        if ($session) {
            return $session->get($this->tokenValueSessionId);
        }

        return null;
    }

    /**
     * Generate a >22-length pseudo random string to be used as salt for
     * passwords
     */
    public function getSaltBytes(int $numberBytes = 0) : string
    {

        if (!$numberBytes) {
             $numberBytes = (int) $this->numberBytes;
        }

        while(true) {
             $safeBytes = $this->random->base64Safe($numberBytes);

            if ($safeBytes && strlen($safeBytes) >= $numberBytes) {
                break;
            }
        }

        return $safeBytes;
    }

    /**
     * Generates a pseudo random token value to be used as input's value in a
     * CSRF check
     */
    public function getToken() : string
    {

        if (null === $this->token) {
             $this->requestToken = $this->getSessionToken();
                $this->token        = $this->random->base64Safe($this->numberBytes);


             $session = $this->getLocalSession();

            if ($session) {
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
     */
    public function getTokenKey() : string
    {

        if (null === $this->tokenKey) {
             $session = $this->getLocalSession();

            if ($session) {
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
     * Creates a password hash using bcrypt with a pseudo random salt
     */
    public function hash(string $password, int $workFactor = 0) : string
    {
        if (!$workFactor) {
             $workFactor = (int) $this->workFactor;
        }

         $hash = (int) $this->defaultHash;

        switch($hash) {

            case self::CRYPT_BLOWFISH_A:
                 $variant = "a";
                break;

            case self::CRYPT_BLOWFISH_X:
                 $variant = "x";
                break;

            case self::CRYPT_BLOWFISH_Y:
                 $variant = "y";
                break;

            case self::CRYPT_MD5:
                 $variant = "1";
                break;

            case self::CRYPT_SHA256:
                 $variant = "5";
                break;

            case self::CRYPT_SHA512:
                 $variant = "6";
                break;

            case self::CRYPT_DEFAULT:
            default:
                 $variant = "y";
                break;
        }

        switch($hash) {

            case self::CRYPT_STD_DES:
            case self::CRYPT_EXT_DES:

                /*
                 * Standard DES-based hash with a two character salt from the
                 * alphabet "./0-9A-Za-z".
                 */

                if ($hash == self::CRYPT_EXT_DES) {
                     $saltBytes = "_" . $this->getSaltBytes(8);
                } else {
                     $saltBytes = $this->getSaltBytes(2);
                }

                if (!is_string($saltBytes)) {
                    throw new Exception(
                        "Unable to get random bytes for the salt"
                    );
                }

                return crypt($password, $saltBytes);

            case self::CRYPT_MD5:
            case self::CRYPT_SHA256:
            case self::CRYPT_SHA512:

                /*
                 * MD5 hashing with a twelve character salt
                 * SHA-256/SHA-512 hash with a sixteen character salt.
                 */

                 $saltBytes = $this->getSaltBytes($hash == self::CRYPT_MD5 ? 12 : 16);

                if (!is_string($saltBytes)) {
                    throw new Exception(
                        "Unable to get random bytes for the salt"
                    );
                }

                return crypt(
                    $password, "$" . $variant . "$"  . $saltBytes . "$"
                );

            case self::CRYPT_DEFAULT:
            case self::CRYPT_BLOWFISH:
            case self::CRYPT_BLOWFISH_X:
            case self::CRYPT_BLOWFISH_Y:
            default:

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

                 $saltBytes = $this->getSaltBytes(22);

                if (!is_string($saltBytes)) {
                    throw new Exception(
                        "Unable to get random bytes for the salt"
                    );
                }

                if ($workFactor < 4) {
                     $workFactor = 4;
                } elseif ($workFactor > 31) {
                     $workFactor = 31;
                }

                return crypt(
                    $password,
                    "$2" . $variant . "$" . sprintf("%02s", $workFactor) . "$" . $saltBytes . "$"
                );
        }

        return "";
    }

    /**
     * Checks if a password hash is a valid bcrypt's hash
     */
    public function isLegacyHash(string $passwordHash) : bool
    {
        return starts_with($passwordHash, "$2a$");
    }

    /**
      * Sets the default hash
      */
    public function setDefaultHash(int $defaultHash) : Security
    {
         $this->defaultHash = $defaultHash;

        return $this;
    }

    /**
     * Sets a number of bytes to be generated by the openssl pseudo random
     * generator
     */
    public function setRandomBytes(int $randomBytes) : Security
    {
         $this->numberBytes = $randomBytes;

        return $this;
    }

    /**
     * Sets the work factor
     */
    public function setWorkFactor(int $workFactor) : Security
    {
         $this->workFactor = $workFactor;

        return $this;
    }

    private function getLocalRequest() : ?RequestInterface
    {

        if ($this->localRequest) {
            return $this->localRequest;
        }

         $container = $this->container;
        if (!is_object($container)) {
            throw new Exception(
                Exception::containerServiceNotFound("the 'request' service")
            );
        }

        if ($container->has("request")) {
            return $container->getShared("request");
        }

        return null;
    }

    private function getLocalSession() : ?SessionInterface
    {

        if ($this->localSession) {
            return $this->localSession;
        }

         $container = $this->container;
        if (!is_object($container)) {
            throw new Exception(
                Exception::containerServiceNotFound("the 'session' service")
            );
        }

        if ($container->has("session")) {
            return $container->getShared("session");
        }

        return null;
    }
}
