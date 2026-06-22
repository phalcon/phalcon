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

use Phalcon\Contracts\Queue\Consumer as ConsumerInterface;
use Phalcon\Contracts\Queue\Destination as DestinationInterface;
use Phalcon\Contracts\Queue\Message as MessageInterface;
use Phalcon\Contracts\Queue\Producer as ProducerInterface;
use Phalcon\Contracts\Queue\Queue as QueueInterface;
use Phalcon\Contracts\Queue\SubscriptionConsumer as SubscriptionConsumerInterface;
use Phalcon\Queue\Adapter\AbstractContext;
use Phalcon\Queue\Adapter\QueueDestinationGuard;

/**
 * Beanstalkd transport session. A queue maps to a Beanstalkd tube. Producers
 * share the context connection (`use` + `put`); each consumer owns its own
 * connection, because Beanstalkd only lets the reserving connection delete,
 * release, bury or touch a job. The destination factories come from
 * AbstractContext.
 */
class BeanstalkContext extends AbstractContext
{
    protected ?BeanstalkConnection $connection = null;

    public function __construct(
        protected string $host,
        protected int $port,
        protected bool $persistent = false,
        protected int $ttr = 86400,
        protected int $pollInterval = 200
    ) {
    }

    public function close(): void
    {
        if ($this->connection !== null) {
            $this->connection->disconnect();

            $this->connection = null;
        }
    }

    public function createConsumer(DestinationInterface $destination): ConsumerInterface
    {
        QueueDestinationGuard::assertQueue($destination, "consume from");

        return new BeanstalkConsumer($this->newConnection(), $destination);
    }

    public function createMessage(string $body = "", array $properties = [], array $headers = []): MessageInterface
    {
        return new BeanstalkMessage($body, $properties, $headers);
    }

    public function createProducer(): ProducerInterface
    {
        return new BeanstalkProducer($this);
    }

    public function createSubscriptionConsumer(): SubscriptionConsumerInterface
    {
        return new BeanstalkSubscriptionConsumer($this, $this->pollInterval);
    }

    /**
     * Default time-to-run (seconds) for new jobs. Used by BeanstalkProducer.
     */
    public function getTtr(): int
    {
        return $this->ttr;
    }

    public function purgeQueue(QueueInterface $queue): void
    {
        $tube       = $queue->getQueueName();
        $connection = $this->newConnection();

        $connection->watchTube($tube);

        if ($tube !== "default") {
            $connection->ignoreTube("default");
        }

        while (true) {
            $job = $connection->reserve(0);

            if ($job === null) {
                break;
            }

            $connection->deleteJob($job[0]);
        }

        $connection->disconnect();
    }

    /**
     * Puts a serialized payload on a tube via the shared connection.
     * Internal transport API used by BeanstalkProducer.
     */
    public function putMessage(string $tube, string $payload, int $priority, int $delay, int $ttr): void
    {
        $connection = $this->getConnection();

        $connection->useTube($tube);
        $connection->put($payload, $priority, $delay, $ttr);
    }

    /**
     * Returns the shared producer/purge connection, connecting on first use.
     */
    private function getConnection(): BeanstalkConnection
    {
        if ($this->connection === null) {
            $this->connection = $this->newConnection();
        }

        return $this->connection;
    }

    /**
     * Builds and connects a fresh Beanstalkd connection.
     */
    private function newConnection(): BeanstalkConnection
    {
        $connection = new BeanstalkConnection($this->host, $this->port, $this->persistent);

        $connection->connect();

        return $connection;
    }
}
