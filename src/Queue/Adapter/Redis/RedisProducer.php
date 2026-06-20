<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this component has been inspired by the queue-interop and
 * enqueue projects.
 *
 * @link    https://github.com/queue-interop/queue-interop
 * @license https://github.com/queue-interop/queue-interop/blob/master/LICENSE
 *
 * @link    https://github.com/php-enqueue/enqueue-dev
 * @license https://github.com/php-enqueue/enqueue-dev/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Phalcon\Queue\Adapter\Redis;

use Phalcon\Contracts\Queue\Destination as DestinationInterface;
use Phalcon\Contracts\Queue\Message as MessageInterface;
use Phalcon\Contracts\Queue\Producer as ProducerInterface;
use Phalcon\Contracts\Queue\Queue as QueueInterface;
use Phalcon\Queue\Exceptions\InvalidDestinationException;
use Phalcon\Queue\Exceptions\PriorityNotSupportedException;
use Phalcon\Queue\Exceptions\TimeToLiveNotSupportedException;

/**
 * Sends messages to a Redis queue. Delivery delay is supported (via the
 * delayed sorted set); priority and time to live are not.
 */
class RedisProducer implements ProducerInterface
{
    protected ?int $deliveryDelay = null;

    public function __construct(protected RedisContext $context)
    {
    }

    public function getDeliveryDelay(): ?int
    {
        return $this->deliveryDelay;
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
                "The Redis transport can only send to a Queue destination"
            );
        }

        $delay = $this->deliveryDelay ?? 0;

        $this->context->pushMessage($destination->getQueueName(), $message, $delay);
    }

    public function setDeliveryDelay(mixed $deliveryDelay = null): ProducerInterface
    {
        $this->deliveryDelay = $deliveryDelay === null ? null : (int) $deliveryDelay;

        return $this;
    }

    public function setPriority(mixed $priority = null): ProducerInterface
    {
        if ($priority !== null) {
            throw new PriorityNotSupportedException(
                "The Redis transport does not support message priority"
            );
        }

        return $this;
    }

    public function setTimeToLive(mixed $timeToLive = null): ProducerInterface
    {
        if ($timeToLive !== null) {
            throw new TimeToLiveNotSupportedException(
                "The Redis transport does not support a time to live"
            );
        }

        return $this;
    }
}
