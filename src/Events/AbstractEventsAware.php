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
     * @param bool       $stopOnFalse Make a listener's `false` final for
     *                                this call (concrete Manager only)
     *
     * @return bool|mixed
     */
    protected function fireManagerEvent(
        string $eventName,
        mixed $data = null,
        bool $cancellable = true,
        bool $stopOnFalse = false
    ): mixed {
        if (null !== $this->eventsManager) {
            /**
             * A security boundary asks for stop-on-false so a listener's
             * denial cannot be overwritten by a later listener. Only the
             * concrete Manager knows the per-call override; a custom
             * ManagerInterface keeps its own semantics.
             */
            if ($stopOnFalse && $this->eventsManager instanceof Manager) {
                return $this->eventsManager->fire($eventName, $this, $data, $cancellable, true);
            }

            return $this->eventsManager->fire($eventName, $this, $data, $cancellable);
        }

        return true;
    }
}
