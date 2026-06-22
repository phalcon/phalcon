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

namespace Phalcon\Queue\Adapter;

use Phalcon\Contracts\Queue\Destination as DestinationInterface;
use Phalcon\Contracts\Queue\Message as MessageInterface;
use Phalcon\Contracts\Queue\Producer as ProducerInterface;
use Phalcon\Queue\Exceptions\DeliveryDelayNotSupportedException;
use Phalcon\Queue\Exceptions\PriorityNotSupportedException;
use Phalcon\Queue\Exceptions\TimeToLiveNotSupportedException;

/**
 * Shared producer base. Provides the capability-negation defaults every
 * transport repeats: each scheduling feature (delivery delay, priority, time
 * to live) reports "not set" and rejects any non-null value with the matching
 * typed exception. A transport that supports a feature overrides just those
 * two accessors. The destination check shared by every `send()` comes from
 * QueueDestinationGuard; concrete producers implement only `send()` and the
 * transport name.
 */
abstract class AbstractProducer implements ProducerInterface
{
    use QueueDestinationGuard;

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

    abstract public function send(DestinationInterface $destination, MessageInterface $message): void;

    public function setDeliveryDelay(mixed $deliveryDelay = null): ProducerInterface
    {
        if ($deliveryDelay !== null) {
            throw new DeliveryDelayNotSupportedException(
                "The " . $this->getTransportName() . " transport does not support a delivery delay"
            );
        }

        return $this;
    }

    public function setPriority(mixed $priority = null): ProducerInterface
    {
        if ($priority !== null) {
            throw new PriorityNotSupportedException(
                "The " . $this->getTransportName() . " transport does not support message priority"
            );
        }

        return $this;
    }

    public function setTimeToLive(mixed $timeToLive = null): ProducerInterface
    {
        if ($timeToLive !== null) {
            throw new TimeToLiveNotSupportedException(
                "The " . $this->getTransportName() . " transport does not support a time to live"
            );
        }

        return $this;
    }
}
