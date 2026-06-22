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

namespace Phalcon\Queue\Adapter\Memory;

use Phalcon\Contracts\Queue\Consumer as ConsumerInterface;
use Phalcon\Contracts\Queue\Destination as DestinationInterface;
use Phalcon\Contracts\Queue\Message as MessageInterface;
use Phalcon\Contracts\Queue\Producer as ProducerInterface;
use Phalcon\Contracts\Queue\Queue as QueueInterface;
use Phalcon\Contracts\Queue\SubscriptionConsumer as SubscriptionConsumerInterface;
use Phalcon\Queue\Adapter\AbstractContext;
use Phalcon\Queue\Adapter\PointToPointStorage;

use function array_shift;

/**
 * In-process transport session. Owns the named FIFO queues that this
 * context's producers and consumers share. The named queue/topic factories and
 * temporary-queue handling come from AbstractContext.
 */
class MemoryContext extends AbstractContext implements PointToPointStorage
{
    /**
     * Named queues: queue name => list of messages (FIFO).
     *
     * @var array
     */
    protected array $queues = [];

    public function close(): void
    {
        $this->purgeTemporaryQueues();

        $this->queues = [];
    }

    public function createConsumer(DestinationInterface $destination): ConsumerInterface
    {
        $queue = $this->assertQueueDestination($destination, "consume from");

        return new MemoryConsumer($this, $queue);
    }

    public function createMessage(string $body = "", array $properties = [], array $headers = []): MessageInterface
    {
        return new MemoryMessage($body, $properties, $headers);
    }

    public function createProducer(): ProducerInterface
    {
        return new MemoryProducer($this);
    }

    public function createSubscriptionConsumer(): SubscriptionConsumerInterface
    {
        return new MemorySubscriptionConsumer($this);
    }

    /**
     * Removes the front message from a queue, or null when it is empty.
     * Internal transport API used by MemoryConsumer.
     */
    public function popMessage(string $queueName): ?MessageInterface
    {
        if (empty($this->queues[$queueName])) {
            return null;
        }

        return array_shift($this->queues[$queueName]);
    }

    public function purgeQueue(QueueInterface $queue): void
    {
        $this->queues[$queue->getQueueName()] = [];
    }

    /**
     * Appends a message to the back of a queue.
     * Internal transport API used by MemoryProducer.
     */
    public function pushMessage(string $queueName, MessageInterface $message): void
    {
        $this->queues[$queueName][] = $message;
    }

    protected function getTransportName(): string
    {
        return "Memory";
    }
}
