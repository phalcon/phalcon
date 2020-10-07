<?php

declare(strict_types=1);

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Events;

/**
 * Phalcon\Events\ManagerInterface
 *
 * Interface for Phalcon\Events managers.
 */
interface ManagerInterface
{
    public const DEFAULT_PRIORITY = 100;

    /**
     * Attach a listener to the events manager
     *
     * @param string          $eventType
     * @param object|callable $handler
     * @param int             $priority
     */
    public function attach(
        string $eventType,
        $handler,
        int $priority = self::DEFAULT_PRIORITY
    ): void;

    /**
     * Detach the listener from the events manager
     *
     * @param string $eventType
     * @param object $handler
     */
    public function detach(string $eventType, $handler): void;

    /**
     * Removes all events from the EventsManager
     *
     * @param string|null $type
     */
    public function detachAll(string $type = null): void;

    /**
     * Fires an event in the events manager causing the active listeners to be
     * notified about it
     *
     * @param string     $eventType
     * @param object     $source
     * @param mixed|null $data
     * @param bool       $cancelable
     *
     * @return mixed
     */
    public function fire(
        string $eventType,
        object $source,
        $data = null,
        bool $cancelable = true
    );

    /**
     * Returns all the attached listeners of a certain type
     *
     * @param string $type
     *
     * @return array
     */
    public function getListeners(string $type): array;

    /**
     * Check whether certain type of event has listeners
     *
     * @param string $type
     *
     * @return bool
     */
    public function hasListeners(string $type): bool;
}
