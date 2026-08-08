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
use Phalcon\Filter\FilterInterface;
use Phalcon\Http\Cookie\CookieInterface;
use Phalcon\Http\Cookie\Exceptions\CookieKeyTooShort;
use Phalcon\Http\Cookie\Exceptions\CryptInterfaceRequired;
use Phalcon\Http\Cookie\Exceptions\CryptServiceUnavailable;
use Phalcon\Http\Cookie\Exceptions\FilterServiceUnavailable;
use Phalcon\Http\Traits\EncryptionAwareTrait;
use Phalcon\Session\ManagerInterface as SessionManagerInterface;
use Phalcon\Traits\Support\Helper\Arr\GetTrait;
use Stringable;

use function array_filter;
use function is_object;
use function is_string;
use function time;

/**
 * Provide OO wrappers to manage a HTTP cookie.
 */
class Cookie extends AbstractInjectionAware implements CookieInterface, Stringable
{
    use EncryptionAwareTrait;
    use GetTrait;

    protected FilterInterface | null $filter = null;
    protected bool $isRead = false;
    protected bool $isRestored = false;

    /**
     * The cookie's sign key.
     */
    protected string | null $signKey = null;
    protected mixed $value = null;

    /**
     * Phalcon\Http\Cookie constructor.
     */
    public function __construct(
        protected string $name,
        mixed $value = null,
        protected int $expire = 0,
        protected string $path = '/',
        protected bool $secure = true,
        protected string $domain = '',
        protected bool $httpOnly = false,
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
     */
    public function delete(): void
    {
        $session = $this->getStartedSession();
        if (null !== $session) {
            $session->remove($this->getSessionKey());
        }

        $this->value = null;
        $options     = $this->getCookieOptions(time() - 691200);

        setcookie($this->name, "", $options);
    }

    /**
     * Returns the domain that the cookie is available to
     */
    public function getDomain(): string
    {
        $this->checkRestored();

        return $this->domain;
    }

    /**
     * Returns the current expiration time
     */
    public function getExpiration(): int
    {
        $this->checkRestored();

        return $this->expire;
    }

    /**
     * Returns if the cookie is accessible only through the HTTP protocol
     */
    public function getHttpOnly(): bool
    {
        $this->checkRestored();

        return $this->httpOnly;
    }

    /**
     * Returns the current cookie's name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the current cookie's options
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Returns the current cookie's path
     */
    public function getPath(): string
    {
        $this->checkRestored();

        return $this->path;
    }

    /**
     * Returns whether the cookie must only be sent when the connection is
     * secure (HTTPS)
     */
    public function getSecure(): bool
    {
        $this->checkRestored();

        return $this->secure;
    }

    /**
     * Returns the cookie's value.
     *
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
                    throw new CryptServiceUnavailable();
                }

                $crypt = $this->container->getShared("crypt");

                if (!is_object($crypt)) {
                    throw new CryptInterfaceRequired();
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
                        throw new FilterServiceUnavailable();
                    }

                    /** @var FilterInterface $filter */
                    $filter = $this->container->getShared('filter');
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
     * Reads the cookie-related info from the SESSION to restore the cookie as
     * it was set.
     *
     * This method is automatically called internally so normally you don't
     * need to call it.
     */
    public function restore(): CookieInterface
    {
        if (true !== $this->isRestored) {
            $session = $this->getStartedSession();
            if (null !== $session) {
                $definition = $session->get($this->getSessionKEy());

                $this->expire   = $definition['expire'] ?? $this->expire;
                $this->domain   = $definition['domain'] ?? $this->domain;
                $this->path     = $definition['path'] ?? $this->path;
                $this->secure   = $definition['secure'] ?? $this->secure;
                $this->httpOnly = $definition['httpOnly'] ?? $this->httpOnly;
                $this->options  = $definition['options'] ?? $this->options;
            }

            $this->isRestored = true;
        }

        return $this;
    }

    /**
     * Sends the cookie to the HTTP client.
     *
     * Stores the cookie definition in session.
     */
    public function send(): CookieInterface
    {
        $definition             = [];
        $definition['expire']   = $this->expire;
        $definition['path']     = $this->path;
        $definition['domain']   = $this->domain;
        $definition['secure']   = $this->secure;
        $definition['httpOnly'] = $this->httpOnly;
        $definition['options']  = $this->options;

        /**
         * Remove all the empty elements
         */
        $definition = array_filter($definition);

        /**
         * The definition is stored in session
         */
        if (!empty($definition)) {
            $session = $this->getStartedSession();
            if (null !== $session) {
                $session->set($this->getSessionKey(), $definition);
            }
        }

        $encryptValue = $this->value;
        if (true === $this->useEncryption && !empty($this->value)) {
            if (null === $this->container) {
                throw new FilterServiceUnavailable();
            }

            $crypt = $this->container->getShared("crypt");

            if (!is_object($crypt)) {
                throw new CryptInterfaceRequired();
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
        $options = $this->getCookieOptions($this->expire);

        setcookie($this->name, $encryptValue, $options);

        return $this;
    }

    /**
     * Sets the domain that the cookie is available to
     */
    public function setDomain(string $domain): CookieInterface
    {
        $this->checkRestored();

        $this->domain = $domain;

        return $this;
    }

    /**
     * Sets the cookie's expiration time
     */
    public function setExpiration(int $expire): CookieInterface
    {
        $this->checkRestored();

        $this->expire = $expire;

        return $this;
    }

    /**
     * Sets if the cookie is accessible only through the HTTP protocol
     */
    public function setHttpOnly(bool $httpOnly): CookieInterface
    {
        $this->checkRestored();

        $this->httpOnly = $httpOnly;

        return $this;
    }

    /**
     * Sets the cookie's options
     */
    public function setOptions(array $options): CookieInterface
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Sets the cookie's path
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
     */
    public function setValue(mixed $value): CookieInterface
    {
        $this->value  = $value;
        $this->isRead = true;

        return $this;
    }

    /**
     * Sets if the cookie must be encrypted/decrypted automatically
     */
    public function useEncryption(bool $useEncryption): CookieInterface
    {
        $this->useEncryption = $useEncryption;

        return $this;
    }

    /**
     * Assert the cookie's key is enough long.
     *
     * @throws \Phalcon\Http\Cookie\Exception
     */
    protected function assertSignKeyIsLongEnough(string $signKey): void
    {
        $length = mb_strlen($signKey);

        if ($length < 32) {
            throw new CookieKeyTooShort($length);
        }
    }

    /**
     * Check if the cookie is restored and restore it if not
     */
    private function checkRestored(): void
    {
        if (true !== $this->isRestored) {
            $this->restore();
        }
    }

    private function getCookieOptions(int $expiresDefault): array
    {
        $options             = $this->options;
        $options['expires']  = $this->getArrVal($options, 'expires', $expiresDefault);
        $options['domain']   = $this->getArrVal($options, 'domain', $this->domain);
        $options['path']     = $this->getArrVal($options, 'path', $this->path);
        $options['secure']   = $this->getArrVal($options, 'secure', $this->secure);
        $options['httponly'] = $this->getArrVal($options, 'httponly', $this->httpOnly);

        return $options;
    }

    /**
     * The session key under which this cookie's definition is stored
     */
    private function getSessionKey(): string
    {
        return "_PHCOOKIE_" . $this->name;
    }

    /**
     * Returns the session manager from the container when the service is
     * available and the session has been started; `null` otherwise
     */
    private function getStartedSession(): SessionManagerInterface | null
    {
        if (
            null === $this->container ||
            true !== $this->container->has('session')
        ) {
            return null;
        }

        $session = $this->container->getShared("session");

        if (true !== $session->exists()) {
            return null;
        }

        return $session;
    }
}
