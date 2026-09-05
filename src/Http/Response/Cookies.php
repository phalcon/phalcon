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

use Phalcon\Contracts\Http\HttpTypes;
use Phalcon\Di\AbstractInjectionAware;
use Phalcon\Di\DiInterface;
use Phalcon\Http\Cookie;
use Phalcon\Http\Cookie\CookieInterface;
use Phalcon\Http\Response as HttpResponse;
use Phalcon\Http\Response\Exceptions\ResponseServiceUnavailable;
use Phalcon\Http\Traits\EncryptionAwareTrait;

use function headers_sent;

/**
 * This class is a bag to manage the cookies.
 *
 * A cookies bag is automatically registered as part of the 'response' service
 * in the DI. By default, cookies are automatically encrypted before being sent
 * to the client and are decrypted when retrieved from the user. To set sign key
 * used to generate a message authentication code use
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
 *
 * @phpstan-import-type http_cookie_bag from HttpTypes
 * @phpstan-import-type http_cookie_options from HttpTypes
 */
class Cookies extends AbstractInjectionAware implements CookiesInterface
{
    use EncryptionAwareTrait;

    /**
     * @phpstan-var http_cookie_bag
     */
    protected array $cookies     = [];
    protected bool $isRegistered = false;
    protected bool $isSent       = false;
    /**
     * The cookie's sign key.
     */
    protected string | null $signKey = null;

    /**
     * Constructor
     */
    public function __construct(
        bool $useEncryption = true,
        string | null $signKey = null
    ) {
        $this->useEncryption = $useEncryption;

        $this->setSignKey($signKey);
    }

    /**
     * Deletes a cookie by its name
     * This method does not remove cookies from the _COOKIE super-global
     */
    public function delete(string $name): bool
    {
        /**
         * Check the internal bag. Cookies that arrived with the request are
         * not in it, so fall back to the _COOKIE superglobal.
         */
        $cookie = $this->cookies[$name] ?? null;

        if (null === $cookie) {
            if (true !== isset($_COOKIE[$name])) {
                return false;
            }

            $cookie = new Cookie($name);

            /**
             * Pass the DI to the created cookie when one is available, so that
             * the cookie definition stored in the session can be cleared. A
             * container is not required to delete a cookie.
             */
            if ($this->container !== null) {
                $cookie->setDI($this->container);
            }
        }

        $cookie->delete();

        return true;
    }

    /**
     * Gets a cookie from the bag
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
         * Its value comes from $_COOKIE with request, so it shouldn't be saved
         * to _cookies property, otherwise it will always be resent after get.
         */
        $container = $this->checkGetContainer();

        /** @var Cookie $cookie */
        $cookie = $container->get("Phalcon\\Http\\Cookie", [$name]);

        /**
         * Pass the DI to created cookies
         */
        $cookie->setDI($container);

        /**
         * Enable encryption in the cookie
         */
        if (true === $this->useEncryption) {
            $cookie->useEncryption($this->useEncryption);
            $cookie->setSignKey($this->signKey);
        }

        return $cookie;
    }

    /**
     * Gets all cookies from the bag
     *
     * @phpstan-return http_cookie_bag
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * Check if a cookie is defined in the bag or exists in the _COOKIE
     * super-global
     */
    public function has(string $name): bool
    {
        return isset($this->cookies[$name]) || isset($_COOKIE[$name]);
    }

    /**
     * Returns if the headers have already been sent
     */
    public function isSent(): bool
    {
        return $this->isSent;
    }

    /**
     * Reset set cookies
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
     *
     * @phpstan-param http_cookie_options $options
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
        $encryption = $this->useEncryption;

        if (!isset($this->cookies[$name])) {
            /** @var DiInterface $container */
            $container = $this->container;

            /** @var Cookie $cookie */
            $cookie = $container->get(
                "Phalcon\\Http\\Cookie",
                [$name, $value, $expire, $path, $secure, $domain, $httpOnly, $options]
            );

            /**
             * Pass the DI to created cookies
             */
            $cookie->setDI($container);

            /**
             * Enable encryption in the cookie
             */
            if ($encryption) {
                $cookie->useEncryption($encryption);
                $cookie->setSignKey($this->signKey);
            }

            $this->cookies[$name] = $cookie;
        } else {
            /** @var Cookie $cookie */
            $cookie = $this->cookies[$name];
            /**
             * Override any settings in the cookie
             */
            $cookie->setValue($value);
            $cookie->setExpiration($expire);
            $cookie->setPath($path);
            $cookie->setSecure($secure);
            $cookie->setDomain($domain);
            $cookie->setHttpOnly($httpOnly);
            $cookie->setOptions($options);
            $cookie->setSignKey($this->signKey);
        }

        /**
         * Register the cookies bag in the response
         */
        if (true !== $this->isRegistered) {
            $container = $this->checkGetContainer();

            /** @var HttpResponse $response */
            $response = $container->getShared('response');

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
     * @see \Phalcon\Encryption\Security\Random
     */
    public function setSignKey(string | null $signKey = null): CookiesInterface
    {
        $this->signKey = $signKey;

        return $this;
    }

    /**
     * Set if cookies in the bag must be automatically encrypted/decrypted
     */
    public function useEncryption(bool $useEncryption): CookiesInterface
    {
        $this->useEncryption = $useEncryption;

        return $this;
    }

    private function checkGetContainer(): DiInterface
    {
        $container = $this->container;

        if (null === $container) {
            throw new ResponseServiceUnavailable();
        }

        return $container;
    }
}
