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

namespace Phalcon\Events;

/**
 * This abstract class offers access to the events manager
 */
abstract class AbstractEventsAware
{
    /**
     * @var ManagerInterface|null
     */
    protected ManagerInterface | null $eventsManager = null;

    /**
     * Returns the internal event manager
     *
     * @return ManagerInterface|null
     */
    public function getEventsManager(): ManagerInterface | null
    {
        return $this->eventsManager;
    }

    /**
     * Sets the events manager
     *
     * @param ManagerInterface $eventsManager
     *
     * @return void
     */
    public function setEventsManager(ManagerInterface $eventsManager): void
    {
        $this->eventsManager = $eventsManager;
    }

    /**
     * Helper method to fire an event
     *
     * @param string     $eventName
     * @param mixed|null $data
     * @param bool       $cancellable
     *
     * @return mixed|bool
     */
    protected function fireManagerEvent(
        string $eventName,
        mixed $data = null,
        bool $cancellable = true
    ): mixed {
        if (null !== $this->eventsManager) {
            return $this->eventsManager->fire($eventName, $this, $data, $cancellable);
        }

        return true;
    }
}
