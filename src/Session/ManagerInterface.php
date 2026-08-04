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

use InvalidArgumentException;
use SessionHandlerInterface;

/**
 * Interface for the Phalcon\Session\Manager
 */
interface ManagerInterface
{
    public const SESSION_ACTIVE   = 2;
    public const SESSION_DISABLED = 0;
    public const SESSION_NONE     = 1;

    /**
     * Alias: Gets a session variable from an application context
     */
    public function __get(string $key);

    /**
     * Alias: Check whether a session variable is set in an application context
     */
    public function __isset(string $key): bool;

    /**
     * Alias: Sets a session variable in an application context
     */
    public function __set(string $key, $value): void;

    /**
     * Alias: Removes a session variable from an application context
     */
    public function __unset(string $key): void;

    /**
     * Destroy/end a session
     */
    public function destroy(): void;

    /**
     * Check whether the session has been started
     */
    public function exists(): bool;

    /**
     * Gets a session variable from an application context
     */
    public function get(string $key, $defaultValue = null, bool $remove = false);

    /**
     * Returns the stored session adapter
     */
    public function getAdapter(): ?SessionHandlerInterface;

    /**
     * Returns the session id
     */
    public function getId(): string;

    /**
     * Returns the name of the session
     */
    public function getName(): string;

    /**
     * Get internal options
     */
    public function getOptions(): array;

    /**
     * Check whether a session variable is set in an application context
     */
    public function has(string $key): bool;

    /**
     * Regenerates the session id using the adapter.
     */
    public function regenerateId(bool $deleteOldSession = true): ManagerInterface;

    /**
     * Removes a session variable from an application context
     */
    public function remove(string $key): void;

    /**
     * Sets a session variable in an application context
     */
    public function set(string $key, $value): void;

    /**
     * Set the adapter for the session
     */
    public function setAdapter(SessionHandlerInterface $adapter): ManagerInterface;

    /**
     * Set session Id
     */
    public function setId(string $sessionId): ManagerInterface;

    /**
     * Set the session name. Throw exception if the session has started
     * and do not allow poop names
     *
     * @throws InvalidArgumentException
     */
    public function setName(string $name): ManagerInterface;

    /**
     * Sets session's options
     */
    public function setOptions(array $options): void;

    /**
     * Starts the session (if headers are already sent the session will not be
     * started)
     */
    public function start(): bool;

    /**
     * Returns the status of the current session.
     */
    public function status(): int;
}
