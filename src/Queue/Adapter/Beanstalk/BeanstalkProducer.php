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

namespace Phalcon\Queue\Adapter\Beanstalk;

use Phalcon\Contracts\Queue\Destination as DestinationInterface;
use Phalcon\Contracts\Queue\Message as MessageInterface;
use Phalcon\Contracts\Queue\Producer as ProducerInterface;
use Phalcon\Contracts\Queue\Queue as QueueInterface;
use Phalcon\Queue\Exceptions\InvalidDestinationException;
use Phalcon\Queue\Exceptions\TimeToLiveNotSupportedException;

use function serialize;

/**
 * Sends messages to a Beanstalkd tube. Delivery delay (rounded down to whole
 * seconds) and message priority are supported natively; Beanstalkd has no
 * message expiry, so time to live is not.
 */
class BeanstalkProducer implements ProducerInterface
{
    /**
     * Default Beanstalkd priority (0 = most urgent).
     */
    public const DEFAULT_PRIORITY = 100;

    protected ?int $deliveryDelay = null;
    protected ?int $priority      = null;

    public function __construct(protected BeanstalkContext $context)
    {
    }

    public function getDeliveryDelay(): ?int
    {
        return $this->deliveryDelay;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function getTimeToLive(): ?int
    {
        return null;
    }

    public function send(DestinationInterface $destination, MessageInterface $message): void
    {
        if (!($destination instanceof QueueInterface)) {
            throw new InvalidDestinationException(
                "The Beanstalk transport can only send to a Queue destination"
            );
        }

        $payload = serialize(
            [
                "body"       => $message->getBody(),
                "properties" => $message->getProperties(),
                "headers"    => $message->getHeaders(),
            ]
        );

        $priority = $this->priority ?? self::DEFAULT_PRIORITY;
        $delay    = $this->deliveryDelay === null ? 0 : (int) ($this->deliveryDelay / 1000);

        $this->context->putMessage(
            $destination->getQueueName(),
            $payload,
            $priority,
            $delay,
            $this->context->getTtr()
        );
    }

    public function setDeliveryDelay(mixed $deliveryDelay = null): ProducerInterface
    {
        $this->deliveryDelay = $deliveryDelay === null ? null : (int) $deliveryDelay;

        return $this;
    }

    public function setPriority(mixed $priority = null): ProducerInterface
    {
        $this->priority = $priority === null ? null : (int) $priority;

        return $this;
    }

    public function setTimeToLive(mixed $timeToLive = null): ProducerInterface
    {
        if ($timeToLive !== null) {
            throw new TimeToLiveNotSupportedException(
                "The Beanstalk transport does not support a time to live"
            );
        }

        return $this;
    }
}
