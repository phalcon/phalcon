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

use Phalcon\Contracts\Queue\Message as MessageInterface;
use Phalcon\Contracts\Queue\Queue as QueueInterface;

/**
 * Consumer base for transports whose receive simply pops the next message from
 * a PointToPointStorage. Receiving already removes the message, so acknowledge
 * is a no-op and reject only re-pushes when requeueing. The blocking receive()
 * is the polling loop inherited from AbstractConsumer.
 */
abstract class AbstractPollingConsumer extends AbstractConsumer
{
    public function __construct(protected PointToPointStorage $context, QueueInterface $queue)
    {
        $this->queue = $queue;
    }

    /**
     * No-op: a received message has already been removed from the queue.
     */
    public function acknowledge(MessageInterface $message): void
    {
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
