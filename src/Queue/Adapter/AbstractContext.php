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

use Phalcon\Contracts\Queue\Context as ContextInterface;
use Phalcon\Contracts\Queue\Queue as QueueInterface;
use Phalcon\Contracts\Queue\Topic as TopicInterface;

use function uniqid;

/**
 * Shared context base. A destination only ever carries a name, so the named
 * queue/topic factories and the temporary-queue bookkeeping are identical for
 * every transport and live here. Concrete contexts implement the transport
 * specific parts (consumers, producers, messages, purge, close) and call
 * purgeTemporaryQueues() from their close(). The queue-destination guard used
 * by createConsumer comes from QueueDestinationGuard.
 */
abstract class AbstractContext implements ContextInterface
{
    use QueueDestinationGuard;

    /**
     * Names of temporary queues created by this context, purged on close().
     *
     * @var string[]
     */
    protected array $temporaryQueues = [];

    public function createQueue(string $queueName): QueueInterface
    {
        return new GenericQueue($queueName);
    }

    public function createTemporaryQueue(): QueueInterface
    {
        $queueName = uniqid("phalcon_queue_", true);

        $this->temporaryQueues[] = $queueName;

        return new GenericQueue($queueName);
    }

    public function createTopic(string $topicName): TopicInterface
    {
        return new GenericTopic($topicName);
    }

    /**
     * Purges and forgets every temporary queue this context created. Concrete
     * contexts call this from close().
     */
    protected function purgeTemporaryQueues(): void
    {
        foreach ($this->temporaryQueues as $queueName) {
            $this->purgeQueue(new GenericQueue($queueName));
        }

        $this->temporaryQueues = [];
    }
}
