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
use Phalcon\Queue\Adapter\AbstractProducer;
use Phalcon\Queue\Adapter\QueueDestinationGuard;

/**
 * Sends messages to a Redis queue. Delivery delay is supported (via the
 * delayed sorted set); priority and time to live are not (the defaults from
 * AbstractProducer reject them).
 */
class RedisProducer extends AbstractProducer
{
    protected ?int $deliveryDelay = null;

    public function __construct(protected RedisContext $context)
    {
    }

    public function getDeliveryDelay(): ?int
    {
        return $this->deliveryDelay;
    }

    public function send(DestinationInterface $destination, MessageInterface $message): void
    {
        QueueDestinationGuard::assertQueue($destination, "send to");

        $delay = $this->deliveryDelay ?? 0;

        $this->context->pushMessage($destination->getQueueName(), $message, $delay);
    }

    public function setDeliveryDelay(mixed $deliveryDelay = null): ProducerInterface
    {
        $this->deliveryDelay = $deliveryDelay === null ? null : (int) $deliveryDelay;

        return $this;
    }
}
