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
 * Canonical contract for Phalcon\Events\Event.
 */
interface Event
{
    /**
     * Gets event data
     *
     * @return mixed
     */
    public function getData(): mixed;

    /**
     * Gets event type
     *
     * @return mixed
     */
    public function getType(): mixed;

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
     * @param mixed $data
     *
     * @return Event
     */
    public function setData(mixed $data = null): Event;

    /**
     * Sets event type
     *
     * @param string $type
     *
     * @return Event
     */
    public function setType(string $type): Event;

    /**
     * Stops the event preventing propagation
     *
     * @return Event
     */
    public function stop(): Event;
}
