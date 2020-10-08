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
 * Phalcon\Events\EventInterface
 *
 * Interface for Phalcon\Events\Event class
 */
interface EventInterface
{
    /**
     * Gets event data
     *
     * @return mixed
     */
    public function getData();

    /**
     * Gets event type
     *
     * @return mixed
     */
    public function getType();

    /**
     * Check whether the event is cancelable
     *
     * @return bool
     */
    public function isCancelable(): bool;

    /**
     * Check whether the event is currently stopped
     *
     * @return bool
     */
    public function isStopped(): bool;

    /**
     * Sets event data
     *
     * @param mixed|null $data
     *
     * @return EventInterface
     */
    public function setData($data = null): EventInterface;

    /**
     * Sets event type
     *
     * @param string $type
     *
     * @return EventInterface
     */
    public function setType(string $type): EventInterface;

    /**
     * Stops the event preventing propagation
     *
     * @return EventInterface
     */
    public function stop(): EventInterface;
}
