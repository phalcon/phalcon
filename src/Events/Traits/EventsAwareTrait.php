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
        if (
            true === property_exists($this, 'container') &&
            null !== $this->container
        ) {
            $this->container->set('eventsManager', $eventsManager);
        }

        $this->eventsManager = $eventsManager;
    }

    /**
     * Helper method to fire an event
     *
     * @param string $eventName
     * @param        $data
     * @param bool   $cancellable
     *
     * @return bool|mixed|null
     * @throws EventsException
     */
    protected function fireManagerEvent(
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
