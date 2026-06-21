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

use Phalcon\Contracts\Queue\Message as MessageInterface;
use Phalcon\Contracts\Queue\Queue as QueueInterface;
use Phalcon\Contracts\Queue\VisibilityAware;
use Phalcon\Queue\Adapter\AbstractConsumer;

use function intdiv;
use function is_array;
use function unserialize;

/**
 * Receives messages from a single Beanstalkd tube over its own connection.
 * `receive()` is overridden to use the native blocking reserve. Implements
 * VisibilityAware: a reserved job has a time-to-run window that `touch()`
 * extends; acknowledging deletes the job, rejecting releases it (requeue) or
 * buries it.
 */
class BeanstalkConsumer extends AbstractConsumer implements VisibilityAware
{
    /**
     * Default Beanstalkd priority used when releasing or burying.
     */
    public const DEFAULT_PRIORITY = 100;

    public function __construct(
        protected BeanstalkConnection $connection,
        QueueInterface $queue
    ) {
        $this->queue = $queue;

        $tube = $queue->getQueueName();

        $connection->watchTube($tube);

        if ($tube !== "default") {
            $connection->ignoreTube("default");
        }
    }

    public function acknowledge(MessageInterface $message): void
    {
        $this->connection->deleteJob($this->resolveJobId($message));
    }

    public function receive(int $timeout = 0): ?MessageInterface
    {
        if ($timeout <= 0) {
            $seconds = null;
        } else {
            $seconds = intdiv($timeout + 999, 1000);
        }

        return $this->buildMessage($this->connection->reserve($seconds));
    }

    public function receiveNoWait(): ?MessageInterface
    {
        return $this->buildMessage($this->connection->reserve(0));
    }

    public function reject(MessageInterface $message, bool $requeue = false): void
    {
        $id = $this->resolveJobId($message);

        if ($requeue) {
            $this->connection->releaseJob($id, self::DEFAULT_PRIORITY, 0);
        } else {
            $this->connection->buryJob($id, self::DEFAULT_PRIORITY);
        }
    }

    /**
     * Extends the time-to-run window of a reserved job (VisibilityAware).
     */
    public function touch(MessageInterface $message): bool
    {
        return $this->connection->touchJob($this->resolveJobId($message));
    }

    /**
     * Builds a BeanstalkMessage from a reserved [id, body] pair, or null.
     *
     * @param array{0: string, 1: string|false}|null $job
     */
    private function buildMessage(?array $job): ?MessageInterface
    {
        if ($job === null) {
            return null;
        }

        $data = unserialize((string) $job[1], ["allowed_classes" => false]);

        if (!is_array($data)) {
            return null;
        }

        $message = new BeanstalkMessage($data["body"], $data["properties"], $data["headers"]);

        $message->setJobId($job[0]);

        return $message;
    }

    /**
     * Resolves a message's Beanstalkd job id.
     */
    private function resolveJobId(MessageInterface $message): string
    {
        if ($message instanceof BeanstalkMessage) {
            return (string) $message->getJobId();
        }

        return "";
    }
}
