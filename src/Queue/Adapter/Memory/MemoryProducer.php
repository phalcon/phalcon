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

namespace Phalcon\Queue\Adapter\Memory;

use Phalcon\Contracts\Queue\Destination as DestinationInterface;
use Phalcon\Contracts\Queue\Message as MessageInterface;
use Phalcon\Contracts\Queue\Producer as ProducerInterface;
use Phalcon\Contracts\Queue\Queue as QueueInterface;
use Phalcon\Queue\Exceptions\DeliveryDelayNotSupportedException;
use Phalcon\Queue\Exceptions\InvalidDestinationException;
use Phalcon\Queue\Exceptions\PriorityNotSupportedException;
use Phalcon\Queue\Exceptions\TimeToLiveNotSupportedException;

/**
 * Sends messages into an in-process queue. The Memory transport delivers
 * immediately and in-process, so delivery delay, priority and time to live
 * are not supported.
 */
class MemoryProducer implements ProducerInterface
{
    public function __construct(protected MemoryContext $context)
    {
    }

    public function getDeliveryDelay(): ?int
    {
        return null;
    }

    public function getPriority(): ?int
    {
        return null;
    }

    public function getTimeToLive(): ?int
    {
        return null;
    }

    public function send(DestinationInterface $destination, MessageInterface $message): void
    {
        if (!($destination instanceof QueueInterface)) {
            throw new InvalidDestinationException(
                "The Memory transport can only send to a Queue destination"
            );
        }

        $this->context->pushMessage($destination->getQueueName(), $message);
    }

    public function setDeliveryDelay(mixed $deliveryDelay = null): ProducerInterface
    {
        if ($deliveryDelay !== null) {
            throw new DeliveryDelayNotSupportedException(
                "The Memory transport does not support a delivery delay"
            );
        }

        return $this;
    }

    public function setPriority(mixed $priority = null): ProducerInterface
    {
        if ($priority !== null) {
            throw new PriorityNotSupportedException(
                "The Memory transport does not support message priority"
            );
        }

        return $this;
    }

    public function setTimeToLive(mixed $timeToLive = null): ProducerInterface
    {
        if ($timeToLive !== null) {
            throw new TimeToLiveNotSupportedException(
                "The Memory transport does not support a time to live"
            );
        }

        return $this;
    }
}
