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

use Phalcon\Events\Exception as EventsException;
use Phalcon\Events\ManagerInterface;

use function property_exists;

trait EventsAwareTrait
{
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
     */
    public function setEventsManager(ManagerInterface $eventsManager): void
    {
        if (
            true === property_exists($this, 'container') &&
            null !== $this->container
        ) {
            $this->container->set('eventsManager', $eventsManager, true);
        }

        $this->eventsManager = $eventsManager;
    }

    /**
     * Helper method to fire an event
     *
     * @param string $eventName
     * @param mixed  $data
     * @param bool   $cancellable
     *
     * @return bool|mixed|null
     * @throws EventsException
     */
    protected function fireManagerEvent(
        string $eventName,
        mixed $data = null,
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
