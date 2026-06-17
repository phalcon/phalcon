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

namespace Phalcon\Session;

use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Di\Traits\InjectionAwareTrait;
use Phalcon\Session\Exceptions\InvalidSessionAdapter;
use Phalcon\Session\Exceptions\InvalidSessionId;
use Phalcon\Session\Exceptions\InvalidSessionName;
use Phalcon\Session\Exceptions\SessionAlreadyStarted;
use Phalcon\Session\Exceptions\SessionModificationDenied;
use SessionHandlerInterface;

use function headers_sent;
use function preg_match;
use function session_destroy;
use function session_id;
use function session_name;
use function session_regenerate_id;
use function session_start;
use function session_status;

/**
 * Phalcon\Session\Manager
 *
 * Session manager class
 *
 * @property SessionHandlerInterface|null $adapter
 * @property string                       $name
 * @property array                        $options
 * @property string                       $uniqueId
 */
class Manager implements InjectionAwareInterface, ManagerInterface
{
    use InjectionAwareTrait;

    /**
     * @var SessionHandlerInterface|null
     */
    private SessionHandlerInterface | null $adapter = null;

    /**
     * @var string
     */
    private string $name = '';

    /**
     * @var array
     */
    private array $options = [];

    /**
     * @var string
     */
    private string $uniqueId = '';

    /**
     * Manager constructor.
     *
     * @param array $options = [
     *                       'uniqueId' => null
     *                       ]
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * Alias: Gets a session variable from an application context
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get(string $key): mixed
    {
        return $this->get($key);
    }

    /**
     * Alias: Check whether a session variable is set in an application context
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return $this->has($key);
    }

    /**
     * Alias: Sets a session variable in an application context
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set(string $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Alias: Removes a session variable from an application context
     *
     * @param string $key
     */
    public function __unset(string $key): void
    {
        $this->remove($key);
    }

    /**
     * Destroy/end a session
     */
    public function destroy(): void
    {
        if (true === $this->exists()) {
            session_destroy();

            $_SESSION = [];
        }
    }

    /**
     * Check whether the session has been started
     *
     * @return bool
     */
    public function exists(): bool
    {
        return session_status() === self::SESSION_ACTIVE;
    }

    /**
     * Gets a session variable from an application context
     *
     * @param string     $key
     * @param mixed|null $defaultValue
     * @param bool       $remove
     *
     * @return mixed|null
     */
    public function get(
        string $key,
        mixed $defaultValue = null,
        bool $remove = false
    ): mixed {
        $value = null;

        if ($this->exists()) {
            $uniqueKey = $this->getUniqueKey($key);
            $value     = $_SESSION[$uniqueKey] ?? $defaultValue;

            if (true === $remove) {
                unset($_SESSION[$uniqueKey]);
            }
        }

        return $value;
    }

    /**
     * Returns the stored session adapter
     *
     * @return SessionHandlerInterface|null
     */
    public function getAdapter(): SessionHandlerInterface | null
    {
        return $this->adapter;
    }

    /**
     * Returns the session id
     *
     * @return string
     */
    public function getId(): string
    {
        return session_id();
    }

    /**
     * Returns the name of the session
     *
     * @return string
     */
    public function getName(): string
    {
        if ('' === $this->name) {
            $this->name = session_name();
        }

        return $this->name;
    }

    /**
     * Get internal options
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Check whether a session variable is set in an application context
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        if (false === $this->exists()) {
            // To use $_SESSION variable we need to start session first
            return false;
        }

        $uniqueKey = $this->getUniqueKey($key);

        return isset($_SESSION[$uniqueKey]);
    }

    /**
     * Regenerates the session id via `session_regenerate_id()` (when the
     * session is active). The registered save handler persists the data
     * under the new id.
     *
     * @param bool $deleteOldSession
     *
     * @return ManagerInterface
     */
    public function regenerateId(bool $deleteOldSession = true): ManagerInterface
    {
        if (true === $this->exists()) {
            session_regenerate_id($deleteOldSession);
        }

        return $this;
    }

    /**
     * Removes a session variable from an application context
     *
     * @param string $key
     */
    public function remove(string $key): void
    {
        // To use $_SESSION variable we need to start session first
        if (true === $this->exists()) {
            $uniqueKey = $this->getUniqueKey($key);

            unset($_SESSION[$uniqueKey]);
        }
    }

    /**
     * Sets a session variable in an application context
     *
     * @param string $key
     * @param mixed  $value
     */
    public function set(string $key, $value): void
    {
        // To use $_SESSION variable we need to start session first
        if (true === $this->exists()) {
            $uniqueKey = $this->getUniqueKey($key);

            $_SESSION[$uniqueKey] = $value;
        }
    }

    /**
     * Set the adapter for the session
     *
     * @param SessionHandlerInterface $adapter
     *
     * @return ManagerInterface
     */
    public function setAdapter(SessionHandlerInterface $adapter): ManagerInterface
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Set session Id
     *
     * @param string $sessionId
     *
     * @return ManagerInterface
     * @throws InvalidSessionId
     * @throws SessionAlreadyStarted
     */
    public function setId(string $sessionId): ManagerInterface
    {
        if (true === $this->exists()) {
            throw new SessionAlreadyStarted();
        }

        if (!preg_match('/^[a-zA-Z0-9,-]+$/D', $sessionId)) {
            throw new InvalidSessionId();
        }

        session_id($sessionId);

        return $this;
    }

    /**
     * Set the session name. Throw exception if the session has started
     * and do not allow poop names
     *
     * @param string $name
     *
     * @return ManagerInterface
     * @throws InvalidSessionName
     * @throws SessionModificationDenied
     */
    public function setName(string $name): ManagerInterface
    {
        if (true === $this->exists()) {
            throw new SessionModificationDenied();
        }

        if (
            !preg_match('/^[\p{L}\p{N}_-]+$/u', $name) ||
            preg_match('/^[0-9]+$/', $name)
        ) {
            throw new InvalidSessionName();
        }

        $this->name = $name;

        session_name($name);

        return $this;
    }

    /**
     * Sets session's options
     *
     * @param array $options
     */
    public function setOptions(array $options): void
    {
        $this->uniqueId = $options['uniqueId'] ?? '';
        $this->options  = $options;
    }

    /**
     * Starts the session (if headers are already sent the session will not be
     * started)
     *
     * @return bool
     */
    public function start(): bool
    {
        /**
         * Check if the session exists
         */
        if (true === $this->exists()) {
            return true;
        }

        /**
         * Cannot start this - headers already sent
         */
        if (true === $this->phpHeadersSent()) {
            return false;
        }

        /**
         * Verify that the session cookie value uses the PHP session ID
         * alphabet ([a-zA-Z0-9,-], depending on session.sid_bits_per_character),
         * otherwise we unset the cookie to allow it to be created by
         * session_start().
         */
        $name = $this->getName();
        if (isset($_COOKIE[$name])) {
            $value = $_COOKIE[$name];
            if (!preg_match("/^[a-zA-Z0-9,-]+$/D", $value)) {
                unset($_COOKIE[$name]);
            }
        }

        if (!($this->adapter instanceof SessionHandlerInterface)) {
            throw new InvalidSessionAdapter();
        }

        /**
         * Register the adapter
         */
        session_set_save_handler($this->adapter);

        /**
         * Start the session
         */
        return session_start();
    }

    /**
     * Returns the status of the current session.
     *
     * @return int
     */
    public function status(): int
    {
        return match (session_status()) {
            PHP_SESSION_DISABLED => self::SESSION_DISABLED,
            PHP_SESSION_ACTIVE   => self::SESSION_ACTIVE,
            default              => self::SESSION_NONE,
        };
    }

    /**
     * Checks if or where headers have been sent
     *
     * @return bool
     *
     * @link https://php.net/manual/en/function.headers-sent.php
     */
    protected function phpHeadersSent(): bool
    {
        return headers_sent();
    }

    /**
     * Returns the key prefixed
     *
     * @param string $key
     *
     * @return string
     */
    private function getUniqueKey(string $key): string
    {
        $prefix = (!empty($this->uniqueId)) ? $this->uniqueId . '#' : '';

        return $prefix . $key;
    }
}
