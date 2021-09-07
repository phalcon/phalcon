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

namespace Phalcon\Events\Traits;

use Phalcon\Events\ManagerInterface;

/**
 * Trait EventsAwareTrait
 *
 * @package Phalcon\Events\Traits
 *
 * @property ?ManagerInterface $eventsManager
 */
trait EventsAwareTrait
{
    protected ?ManagerInterface $eventsManager = null;

    /**
     * Returns the internal event manager
     *
     * @return ManagerInterface|null
     */
    public function getEventsManager(): ?ManagerInterface
    {
        return $this->eventsManager;
    }

    /**
     * Sets the events manager
     *
     * @param ManagerInterface $eventsManager
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
    protected function fireEvent(
        string $eventName,
        $data = null,
        bool $cancellable = true
    ) {
        if (null !== $this->eventsManager) {
            return $this
                ->eventsManager
                ->fire($eventName, $this, $data, $cancellable)
            ;
        }

        return true;
    }
}
