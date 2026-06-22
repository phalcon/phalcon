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

namespace Phalcon\Queue\Adapter\Beanstalk;

use Phalcon\Contracts\Queue\Destination as DestinationInterface;
use Phalcon\Contracts\Queue\Message as MessageInterface;
use Phalcon\Contracts\Queue\Producer as ProducerInterface;
use Phalcon\Queue\Adapter\AbstractProducer;
use Phalcon\Queue\Adapter\MessageEnvelope;

/**
 * Sends messages to a Beanstalkd tube. Delivery delay (rounded down to whole
 * seconds) and message priority are supported natively; Beanstalkd has no
 * message expiry, so time to live is not (handled by AbstractProducer).
 */
class BeanstalkProducer extends AbstractProducer
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

    public function send(DestinationInterface $destination, MessageInterface $message): void
    {
        $queue = $this->assertQueueDestination($destination, "send to");

        $payload  = MessageEnvelope::encode($message);
        $priority = $this->priority ?? self::DEFAULT_PRIORITY;
        $delay    = $this->deliveryDelay === null ? 0 : (int) ($this->deliveryDelay / 1000);

        $this->context->putMessage(
            $queue->getQueueName(),
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

    protected function getTransportName(): string
    {
        return "Beanstalk";
    }
}
