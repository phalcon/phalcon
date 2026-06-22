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

namespace Phalcon\Queue\Adapter\Stream;

use Phalcon\Contracts\Queue\Destination as DestinationInterface;
use Phalcon\Contracts\Queue\Message as MessageInterface;
use Phalcon\Queue\Adapter\AbstractProducer;

/**
 * Appends messages to a filesystem queue. The Stream transport delivers in
 * insertion order with no scheduling, so delivery delay, priority and time
 * to live are not supported (handled by AbstractProducer).
 */
class StreamProducer extends AbstractProducer
{
    public function __construct(protected StreamContext $context)
    {
    }

    public function send(DestinationInterface $destination, MessageInterface $message): void
    {
        $queue = $this->assertQueueDestination($destination, "send to");

        $this->context->pushMessage($queue->getQueueName(), $message);
    }

    protected function getTransportName(): string
    {
        return "Stream";
    }
}
