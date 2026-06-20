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

namespace Phalcon\Contracts\Queue;

/**
 * Sends messages to a destination.
 */
interface Producer
{
    /**
     * Returns the delivery delay (in milliseconds) or null when not set.
     *
     * @return int|null
     */
    public function getDeliveryDelay(): ?int;

    /**
     * Returns the message priority or null when not set.
     *
     * @return int|null
     */
    public function getPriority(): ?int;

    /**
     * Returns the time to live (in milliseconds) or null when not set.
     *
     * @return int|null
     */
    public function getTimeToLive(): ?int;

    /**
     * Sends a message to the given destination.
     */
    public function send(Destination $destination, Message $message): void;

    /**
     * Sets the delivery delay (in milliseconds). Null clears it.
     *
     * @param mixed $deliveryDelay
     */
    public function setDeliveryDelay(mixed $deliveryDelay = null): Producer;

    /**
     * Sets the message priority. Null clears it.
     *
     * @param mixed $priority
     */
    public function setPriority(mixed $priority = null): Producer;

    /**
     * Sets the time to live (in milliseconds). Null clears it.
     *
     * @param mixed $timeToLive
     */
    public function setTimeToLive(mixed $timeToLive = null): Producer;
}
