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

namespace Phalcon\Contracts\Events;

/**
 * Canonical contract for Phalcon\Events\Manager.
 */
interface Manager
{
    public const DEFAULT_PRIORITY = 100;

    /**
     * Registers an event subscriber.
     *
     * @param Subscriber $subscriber
     *
     * @return void
     */
    public function addSubscriber(Subscriber $subscriber): void;

    /**
     * Returns whether priority ordering is currently enabled.
     *
     * @return bool
     */
    public function arePrioritiesEnabled(): bool;

    /**
     * Attach a listener to the events manager.
     *
     * @param string          $eventType
     * @param object|callable $handler
     * @param int             $priority
     *
     * @return void
     */
    public function attach(
        string $eventType,
        object | callable $handler,
        int $priority = self::DEFAULT_PRIORITY
    ): void;

    /**
     * Removes every registered subscriber and detaches each listener they
     * contributed.
     *
     * @return void
     */
    public function clearSubscribers(): void;

    /**
     * Toggle response collection on/off.
     *
     * @param bool $collect
     *
     * @return void
     */
    public function collectResponses(bool $collect): void;

    /**
     * Detach a listener from the events manager.
     *
     * @param string          $eventType
     * @param object|callable $handler
     *
     * @return void
     */
    public function detach(string $eventType, object | callable $handler): void;

    /**
     * Removes all listeners -- globally or for a single event type.
     *
     * @param string|null $type
     *
     * @return void
     */
    public function detachAll(string | null $type = null): void;

    /**
     * Toggle priority ordering on/off.
     *
     * @param bool $enablePriorities
     *
     * @return void
     */
    public function enablePriorities(bool $enablePriorities): void;

    /**
     * Fires an event, notifying the active listeners.
     *
     * @param string $eventType
     * @param object $source
     * @param mixed  $data
     * @param bool   $cancelable
     *
     * @return mixed
     */
    public function fire(
        string $eventType,
        object $source,
        mixed $data = null,
        bool $cancelable = true
    ): mixed;

    /**
     * Returns all listeners attached to the given event type.
     *
     * @param string $type
     *
     * @return array
     */
    public function getListeners(string $type): array;

    /**
     * Returns the responses recorded during the last fire (when collecting).
     *
     * @return array
     */
    public function getResponses(): array;

    /**
     * Returns the list of registered subscriber instances.
     *
     * @return array
     */
    public function getSubscribers(): array;

    /**
     * Check whether the given event type has any listeners.
     *
     * @param string $type
     *
     * @return bool
     */
    public function hasListeners(string $type): bool;

    /**
     * Check whether the manager is currently collecting responses.
     *
     * @return bool
     */
    public function isCollecting(): bool;

    /**
     * Returns true when the given handler is an object or callable.
     *
     * @param mixed $handler
     *
     * @return bool
     */
    public function isValidHandler(mixed $handler): bool;

    /**
     * Removes a previously registered subscriber.
     *
     * @param Subscriber $subscriber
     *
     * @return void
     */
    public function removeSubscriber(Subscriber $subscriber): void;
}
