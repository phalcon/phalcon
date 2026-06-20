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

namespace Phalcon\Queue\Adapter\Stream;

use Phalcon\Contracts\Queue\Message as MessageInterface;
use Phalcon\Contracts\Queue\Queue as QueueInterface;
use Phalcon\Queue\Adapter\AbstractConsumer;

/**
 * Receives messages from a single filesystem queue. `receive()` is the
 * polling loop inherited from AbstractConsumer.
 */
class StreamConsumer extends AbstractConsumer
{
    protected StreamContext $context;
    protected QueueInterface $queue;

    public function __construct(StreamContext $context, QueueInterface $queue, int $pollInterval = 200)
    {
        $this->context      = $context;
        $this->queue        = $queue;
        $this->pollInterval = $pollInterval;
    }

    /**
     * No-op: a received message has already been removed from the queue file.
     */
    public function acknowledge(MessageInterface $message): void
    {
    }

    public function getQueue(): QueueInterface
    {
        return $this->queue;
    }

    public function receiveNoWait(): ?MessageInterface
    {
        return $this->context->popMessage($this->queue->getQueueName());
    }

    public function reject(MessageInterface $message, bool $requeue = false): void
    {
        if ($requeue) {
            $this->context->pushMessage($this->queue->getQueueName(), $message);
        }
    }
}
