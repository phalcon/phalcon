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

namespace Phalcon\Http;

use Phalcon\Di\AbstractInjectionAware;
use Phalcon\Encryption\Crypt\CryptInterface;
use Phalcon\Filter\FilterInterface;
use Phalcon\Http\Cookie\CookieInterface;
use Phalcon\Http\Cookie\Exception as CookieException;
use Phalcon\Http\Response\Exception;
use Phalcon\Session\ManagerInterface as SessionManagerInterface;
use Stringable;

use function array_filter;
use function is_object;
use function is_string;
use function sprintf;
use function time;

/**
 * Provide OO wrappers to manage a HTTP cookie.
 */
class Cookie extends AbstractInjectionAware implements
    CookieInterface,
    Stringable
{
    private const COOKIE_PREFIX = '_PHCOOKIE_';

    /**
     * @var FilterInterface|null
     */
    protected FilterInterface | null $filter = null;

    /**
     * @var bool
     */
    protected bool $isRead = false;

    /**
     * @var bool
     */
    protected bool $isRestored = false;

    /**
     * The cookie's sign key.
     *
     * @var string|null
     */
    protected string | null $signKey = null;

    /**
     * @var bool
     */
    protected bool $useEncryption = false;

    /**
     * @var mixed|null
     */
    protected mixed $value = null;

    /**
     * Phalcon\Http\Cookie constructor.
     */
    public function __construct(
        protected string $name,
        mixed $value = null,
        protected int $expire = 0,
        protected string $path = '/',
        protected bool | null $secure = null,
        protected string | null $domain = null,
        protected bool | null $httpOnly = null,
        protected array $options = []
    ) {
        if (null !== $value) {
            $this->setValue($value);
        }
    }

    /**
     * Magic __toString method converts the cookie's value to string
     */
    public function __toString(): string
    {
        return (string)$this->getValue();
    }

    /**
     * Deletes the cookie by setting an expiration time in the past
     *
     * @return void
     */
    public function delete(): void
    {
        if (
            null !== $this->container &&
            true === $this->container->has('session')
        ) {
            /** @var SessionManagerInterface $session */
            $session = $this->container->getShared('session');

            if (true === $session->exists()) {
                $session->remove(self::COOKIE_PREFIX . $this->name);
            }
        }

        $this->value = null;
        $options     = $this->getCookieOptions();

        setcookie($this->name, "", $options);
    }

    /**
     * Returns the domain that the cookie is available to
     *
     * @return string
     */
    public function getDomain(): string
    {
        $this->checkRestored();

        return $this->domain;
    }

    /**
     * Returns the current expiration time
     */
    /**
     * @return int
     */
    public function getExpiration(): int
    {
        $this->checkRestored();

        return $this->expire;
    }

    /**
     * Returns if the cookie is accessible only through the HTTP protocol
     *
     * @return bool
     */
    public function getHttpOnly(): bool
    {
        $this->checkRestored();

        return $this->httpOnly;
    }

    /**
     * Returns the current cookie's name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the current cookie's options
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Returns the current cookie's path
     *
     * @return string
     */
    public function getPath(): string
    {
        $this->checkRestored();

        return $this->path;
    }

    /**
     * Returns whether the cookie must only be sent when the connection is
     * secure (HTTPS)
     *
     * @return bool
     */
    public function getSecure(): bool
    {
        $this->checkRestored();

        return $this->secure;
    }

    /**
     * Returns the cookie's value.
     *
     * @param mixed|null $filters
     * @param mixed|null $defaultValue
     *
     * @return mixed
     * @throws Exception
     * @todo filters needs to be array/string
     */
    public function getValue(
        mixed $filters = null,
        mixed $defaultValue = null
    ): mixed {
        $this->checkRestored();

        if (true !== $this->isRead) {
            if (!isset($_COOKIE[$this->name])) {
                return $defaultValue;
            }

            $value          = $_COOKIE[$this->name];
            $decryptedValue = $value;
            if (true === $this->useEncryption) {
                if (null === $this->container) {
                    throw new Exception(
                        "A dependency injection container is required "
                        . "to access the 'filter' and 'crypt' services"
                    );
                }

                /** @var CryptInterface $crypt */
                $crypt = $this->container->getShared('crypt');

                if (!is_object($crypt)) {
                    throw new Exception(
                        'A dependency which implements CryptInterface '
                        . 'is required to use encryption'
                    );
                }

                /**
                 * Verify the cookie's value if the sign key was set
                 */
                if (null !== $this->signKey) {
                    /**
                     * Decrypt the value also decoding it with base64
                     */
                    $decryptedValue = $crypt->decryptBase64(
                        $value,
                        $this->signKey
                    );
                } else {
                    /**
                     * Decrypt the value also decoding it with base64
                     */
                    $decryptedValue = $crypt->decryptBase64($value);
                }
            }

            /**
             * Update the decrypted value
             */
            $this->value = $decryptedValue;

            if (null !== $filters) {
                if (null === $this->filter) {
                    if (null === $this->container) {
                        throw new Exception(
                            "A dependency injection container is "
                            . "required to access the 'filter' service"
                        );
                    }

                    /** @var FilterInterface $filter */
                    $filter       = $this->container->getShared('filter');
                    $this->filter = $filter;
                }

                return $this->filter->sanitize($decryptedValue, $filters);
            }

            /**
             * Return the value without filtering
             */
            return $decryptedValue;
        }

        return $this->value;
    }

    /**
     * Check if the cookie is using implicit encryption
     *
     * @return bool
     */
    public function isUsingEncryption(): bool
    {
        return $this->useEncryption;
    }

    /**
     * Reads the cookie-related info from the SESSION to restore the cookie as
     * it was set.
     *
     * This method is automatically called internally so normally you don't
     * need to call it.
     *
     * @return CookieInterface
     */
    public function restore(): CookieInterface
    {
        if (true !== $this->isRestored) {
            if (
                null !== $this->container &&
                true === $this->container->has('session')
            ) {
                /** @var SessionManagerInterface $session */
                $session = $this->container->getShared('session');

                if (true === $session->exists()) {
                    $definition = $session->get(
                        self::COOKIE_PREFIX . $this->name
                    );

                    $this->expire   = $definition['expire'] ?? $this->expire;
                    $this->domain   = $definition['domain'] ?? $this->domain;
                    $this->path     = $definition['path'] ?? $this->path;
                    $this->secure   = $definition['secure'] ?? $this->secure;
                    $this->httpOnly = $definition['httpOnly'] ?? $this->httpOnly;
                    $this->options  = $definition['options'] ?? $this->options;
                }
            }

            $this->isRestored = true;
        }

        return $this;
    }

    /**
     * Sends the cookie to the HTTP client.
     *
     * Stores the cookie definition in session.
     *
     * @return CookieInterface
     * @throws Exception
     */
    public function send(): CookieInterface
    {
        $definition             = [];
        $definition['expire']   = $this->expire;
        $definition['path']     = $this->path;
        $definition['domain']   = $this->domain ?? null;
        $definition['secure']   = $this->secure ?? null;
        $definition['httpOnly'] = $this->httpOnly ?? null;
        $definition['options']  = $this->options;

        /**
         * Remove all the empty elements
         */
        $definition = array_filter($definition);

        /**
         * The definition is stored in session
         */
        if (
            !empty($definition) &&
            null !== $this->container &&
            true === $this->container->has('session')
        ) {
            /** @var SessionManagerInterface $session */
            $session = $this->container->getShared('session');

            if (true === $session->exists()) {
                $session->set(self::COOKIE_PREFIX . $this->name, $definition);
            }
        }

        $encryptValue = $this->value;
        if (true === $this->useEncryption && !empty($this->value)) {
            if (null === $this->container) {
                throw new Exception(
                    "A dependency injection container is required to "
                    . "access the 'filter' service"
                );
            }

            /** @var CryptInterface $crypt */
            $crypt = $this->container->getShared('crypt');

            if (!is_object($crypt)) {
                throw new Exception(
                    'A dependency which implements CryptInterface '
                    . 'is required to use encryption'
                );
            }

            /**
             * Encrypt the value also coding it with base64.
             * Sign the cookie's value if the sign key was set
             */
            if (is_string($this->signKey)) {
                $encryptValue = $crypt->encryptBase64(
                    (string)$this->value,
                    $this->signKey
                );
            } else {
                $encryptValue = $crypt->encryptBase64((string)$this->value);
            }
        }

        /**
         * Sets the cookie using the standard 'setcookie' function
         */
        $options = $this->getCookieOptions();

        setcookie($this->name, $encryptValue, $options);

        return $this;
    }

    /**
     * Sets the domain that the cookie is available to
     *
     * @param string $domain
     *
     * @return CookieInterface
     */
    public function setDomain(string $domain): CookieInterface
    {
        $this->checkRestored();

        $this->domain = $domain;

        return $this;
    }

    /**
     * Sets the cookie's expiration time
     *
     * @param int $expire
     *
     * @return CookieInterface
     */
    public function setExpiration(int $expire): CookieInterface
    {
        $this->checkRestored();

        $this->expire = $expire;

        return $this;
    }

    /**
     * Sets if the cookie is accessible only through the HTTP protocol
     *
     * @param bool $httpOnly
     *
     * @return CookieInterface
     */
    public function setHttpOnly(bool $httpOnly): CookieInterface
    {
        $this->checkRestored();

        $this->httpOnly = $httpOnly;

        return $this;
    }

    /**
     * Sets the cookie's options
     *
     * @param array $options
     *
     * @return CookieInterface
     */
    public function setOptions(array $options): CookieInterface
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Sets the cookie's path
     *
     * @param string $path
     *
     * @return CookieInterface
     */
    public function setPath(string $path): CookieInterface
    {
        $this->checkRestored();

        $this->path = $path;

        return $this;
    }

    /**
     * Sets if the cookie must only be sent when the connection is secure
     * (HTTPS)
     *
     * @param bool $secure
     *
     * @return CookieInterface
     */
    public function setSecure(bool $secure): CookieInterface
    {
        $this->checkRestored();

        $this->secure = $secure;

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
     * @return CookieInterface
     * @throws CookieException
     * @see \Phalcon\Encryption\Security\Random
     */
    public function setSignKey(string | null $signKey = null): CookieInterface
    {
        if (null !== $signKey) {
            $this->assertSignKeyIsLongEnough($signKey);
        }

        $this->signKey = $signKey;

        return $this;
    }

    /**
     * Sets the cookie's value
     *
     * @param mixed $value
     *
     * @return CookieInterface
     */
    public function setValue(mixed $value): CookieInterface
    {
        $this->value  = $value;
        $this->isRead = true;

        return $this;
    }

    /**
     * Sets if the cookie must be encrypted/decrypted automatically
     *
     * @param bool $useEncryption
     *
     * @return CookieInterface
     */
    public function useEncryption(bool $useEncryption): CookieInterface
    {
        $this->useEncryption = $useEncryption;

        return $this;
    }

    /**
     * Assert the cookie's key is enough long.
     *
     * @param string $signKey
     *
     * @return void
     * @throws CookieException
     */
    protected function assertSignKeyIsLongEnough(string $signKey): void
    {
        $length = mb_strlen($signKey);

        if ($length < 32) {
            throw new CookieException(
                sprintf(
                    "The cookie's key should be at least 32 characters long. "
                    . "Current length is %d.",
                    $length
                )
            );
        }
    }

    /**
     * Check if the cookie is restored and restore it if not
     *
     * @return void
     */
    private function checkRestored(): void
    {
        if (true !== $this->isRestored) {
            $this->restore();
        }
    }

    /**
     * @return array
     */
    private function getCookieOptions(): array
    {
        $options             = $this->options;
        $options['expires']  = $options['expires'] ?? time() - 691200;
        $options['domain']   = $options['domain'] ?? $this->domain;
        $options['path']     = $options['path'] ?? $this->path;
        $options['secure']   = $options['secure'] ?? $this->secure;
        $options['httponly'] = $options['httponly'] ?? $this->httpOnly;

        return $options;
    }
}
