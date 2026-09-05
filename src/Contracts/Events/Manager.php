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
     */
    public function addSubscriber(Subscriber $subscriber): void;

    /**
     * Returns whether priority ordering is currently enabled.
     */
    public function arePrioritiesEnabled(): bool;

    /**
     * Attach a listener to the events manager.
     */
    public function attach(
        string $eventType,
        callable | object $handler,
        int $priority = self::DEFAULT_PRIORITY
    ): void;

    /**
     * Removes every registered subscriber and detaches each listener they
     * contributed.
     */
    public function clearSubscribers(): void;

    /**
     * Toggle response collection on/off.
     */
    public function collectResponses(bool $collect): void;

    /**
     * Detach a listener from the events manager.
     */
    public function detach(string $eventType, callable | object $handler): void;

    /**
     * Removes all listeners -- globally or for a single event type.
     */
    public function detachAll(string | null $type = null): void;

    /**
     * Toggle priority ordering on/off.
     */
    public function enablePriorities(bool $enablePriorities): void;

    /**
     * Fires an event, notifying the active listeners.
     */
    public function fire(
        string $eventType,
        object $source,
        mixed $data = null,
        bool $cancelable = true
    ): mixed;

    /**
     * Returns all listeners attached to the given event type.
     */
    public function getListeners(string $type): array;

    /**
     * Returns the responses recorded during the last fire (when collecting).
     */
    public function getResponses(): array;

    /**
     * Returns the list of registered subscriber instances.
     */
    public function getSubscribers(): array;

    /**
     * Check whether the given event type has any listeners.
     */
    public function hasListeners(string $type): bool;

    /**
     * Check whether the manager is currently collecting responses.
     */
    public function isCollecting(): bool;

    /**
     * Returns true when the given handler is an object or callable.
     */
    public function isValidHandler(mixed $handler): bool;

    /**
     * Removes a previously registered subscriber.
     */
    public function removeSubscriber(Subscriber $subscriber): void;
}
