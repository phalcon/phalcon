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

namespace Phalcon\Http\Response;

use Phalcon\Di\AbstractInjectionAware;
use Phalcon\Http\Cookie;
use Phalcon\Http\Cookie\CookieInterface;
use Phalcon\Http\Cookie\Exception;
use Phalcon\Http\ResponseInterface;

use function headers_sent;

/**
 * This class is a bag to manage the cookies.
 *
 * A cookies bag is automatically registered as part of the 'response' service
 * in the DI. By default, cookies are automatically encrypted before being sent
 * to the client and are decrypted when retrieved from the user. To set sign
 * key used to generate a message authentication code use
 * `Phalcon\Http\Response\Cookies::setSignKey()`.
 *
 * ```php
 * use Phalcon\Di\Di;
 * use Phalcon\Encryption\Crypt;
 * use Phalcon\Http\Response\Cookies;
 *
 * $di = new Di();
 *
 * $di->set(
 *     'crypt',
 *     function () {
 *         $crypt = new Crypt();
 *
 *         // The `$key' should have been previously generated in a
 *         // cryptographically safe way.
 *         $key =
 *         "T4\xb1\x8d\xa9\x98\x05\\\x8c\xbe\x1d\x07&[\x99\x18\xa4~Lc1\xbeW\xb3";
 *
 *         $crypt->setKey($key);
 *
 *         return $crypt;
 *     }
 * );
 *
 * $di->set(
 *     'cookies',
 *     function () {
 *         $cookies = new Cookies();
 *
 *         // The `$key' MUST be at least 32 characters long and generated
 *         // using a cryptographically secure pseudo random generator.
 *         $key =
 *         "#1dj8$=dp?.ak//j1V$~%*0XaK\xb1\x8d\xa9\x98\x054t7w!z%C*F-Jk\x98\x05\\\x5c";
 *
 *         $cookies->setSignKey($key);
 *
 *         return $cookies;
 *     }
 * );
 * ```
 */
class Cookies extends AbstractInjectionAware implements CookiesInterface
{
    /**
     * @var array
     */
    protected array $cookies = [];
    /**
     * @var bool
     */
    protected bool $isRegistered = false;
    /**
     * @var bool
     */
    protected bool $isSent = false;
    /**
     * The cookie's sign key.
     *
     * @var string|null
     */
    protected string | null $signKey = null;

    /**
     * Constructor
     */
    public function __construct(
        protected bool $useEncryption = true,
        string | null $signKey = null
    ) {
        $this->setSignKey($signKey);
    }

    /**
     * Deletes a cookie by its name
     * This method does not remove cookies from the _COOKIE super-global
     *
     * @param string $name
     *
     * @return bool
     */
    public function delete(string $name): bool
    {
        if (!isset($this->cookies[$name])) {
            return false;
        }

        $cookie = $this->cookies[$name];
        $cookie->delete();

        return true;
    }

    /**
     * Gets a cookie from the bag
     *
     * @param string $name
     *
     * @return CookieInterface
     * @throws Exception
     */
    public function get(string $name): CookieInterface
    {
        /**
         * Gets cookie from the cookies service. They will be sent with response.
         */
        if (isset($this->cookies[$name])) {
            return $this->cookies[$name];
        }

        /**
         * Create the cookie if it does not exist.
         * It's value come from $_COOKIE with request, so it shouldn't be saved
         * to _cookies property, otherwise it will always be resent after get.
         */
        $cookie = new Cookie($name);
        if (null !== $this->container) {
            /**
             * Pass the DI to created cookies
             */
            $cookie->setDi($this->container);

            /**
             * Enable encryption in the cookie
             */
            if (true === $this->useEncryption) {
                $cookie->useEncryption($this->useEncryption);
                $cookie->setSignKey($this->signKey);
            }
        }

        return $cookie;
    }

    /**
     * Gets all cookies from the bag
     *
     * @return array
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * Check if a cookie is defined in the bag or exists in the _COOKIE
     * super-global
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->cookies[$name]) || isset($_COOKIE[$name]);
    }

    /**
     * Returns if the headers have already been sent
     *
     * @return bool
     */
    public function isSent(): bool
    {
        return $this->isSent;
    }

    /**
     * Returns if the bag is automatically encrypting/decrypting cookies
     *
     * @return bool
     */
    public function isUsingEncryption(): bool
    {
        return $this->useEncryption;
    }

    /**
     * Reset set cookies
     *
     * @return CookiesInterface
     */
    public function reset(): CookiesInterface
    {
        $this->cookies = [];

        return $this;
    }

    /**
     * Sends the cookies to the client
     * Cookies aren't sent if headers are sent in the current request
     */
    public function send(): bool
    {
        if (
            true === headers_sent() ||
            true === $this->isSent()
        ) {
            return false;
        }

        foreach ($this->cookies as $cookie) {
            $cookie->send();
        }

        $this->isSent = true;

        return true;
    }

    /**
     * Sets a cookie to be sent at the end of the request.
     *
     * This method overrides any cookie set before with the same name.
     *
     * ```php
     * use Phalcon\Http\Response\Cookies;
     *
     * $now = new DateTimeImmutable();
     * $tomorrow = $now->modify('tomorrow');
     *
     * $cookies = new Cookies();
     * $cookies->set(
     *     'remember-me',
     *     json_encode(['user_id' => 1]),
     *     (int) $tomorrow->format('U'),
     * );
     * ```
     */
    public function set(
        string $name,
        mixed $value = null,
        int $expire = 0,
        string $path = '/',
        bool $secure = false,
        string $domain = '',
        bool $httpOnly = false,
        array $options = []
    ): CookiesInterface {
        /**
         * Check if the cookie needs to be updated or
         */
        if (!isset($this->cookies[$name])) {
            $cookie = new Cookie(
                $name,
                $value,
                $expire,
                $path,
                $secure,
                $domain,
                $httpOnly,
                $options
            );

            /**
             * Pass the DI to created cookies
             */
            $cookie->setDi($this->container);

            /**
             * Enable encryption in the cookie
             */
            if (true === $this->useEncryption) {
                $cookie->useEncryption($this->useEncryption);
                $cookie->setSignKey($this->signKey);
            }

            $this->cookies[$name] = $cookie;
        } else {
            $cookie = $this->cookies[$name];
            /**
             * Override any settings in the cookie
             */
            $cookie
                ->setValue($value)
                ->setExpiration($expire)
                ->setPath($path)
                ->setSecure($secure)
                ->setDomain($domain)
                ->setHttpOnly($httpOnly)
                ->setOptions($options)
                ->setSignKey($this->signKey)
            ;
        }

        /**
         * Register the cookies bag in the response
         */
        if (true !== $this->isRegistered) {
            if (null === $this->container) {
                throw new Exception(
                    "A dependency injection container is required to "
                    . "access the 'response' service"
                );
            }

            /** @var ResponseInterface $response */
            $response = $this->container->getShared('response');

            /**
             * Pass the cookies bag to the response so it can send the headers
             * at the of the request
             */
            $response->setCookies($this);

            $this->isRegistered = true;
        }

        return $this;
    }

    /**
     * Sets the cookie's sign key.
     *
     * The `$signKey' MUST be at least 32 characters long
     * and generated using a cryptographically secure pseudo random generator.
     *
     * Use NULL to disable cookie signing.
     *
     * @param string|null $signKey
     *
     * @return CookiesInterface
     * @see \Phalcon\Encryption\Security\Random
     */
    public function setSignKey(string | null $signKey = null): CookiesInterface
    {
        $this->signKey = $signKey;

        return $this;
    }

    /**
     * Set if cookies in the bag must be automatically encrypted/decrypted
     *
     * @param bool $useEncryption
     *
     * @return CookiesInterface
     */
    public function useEncryption(bool $useEncryption): CookiesInterface
    {
        $this->useEncryption = $useEncryption;

        return $this;
    }
}
