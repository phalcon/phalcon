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
use Phalcon\Contracts\Queue\Queue as QueueInterface;
use Phalcon\Queue\Exceptions\InvalidDestinationException;

/**
 * Shared "destination must be a queue" guard. Producers (on send) and contexts
 * (on createConsumer) both reject any non-queue destination with the same
 * typed exception; this trait keeps that single rule in one place. The $action
 * verb ("send to", "consume from") tailors the message to the caller.
 */
trait QueueDestinationGuard
{
    /**
     * Ensures the destination is a queue, returning it narrowed to
     * QueueInterface. Throws for any other destination (for example a topic).
     */
    protected function assertQueueDestination(DestinationInterface $destination, string $action): QueueInterface
    {
        if (!($destination instanceof QueueInterface)) {
            throw new InvalidDestinationException(
                "The " . $this->getTransportName() . " transport can only " . $action . " a Queue destination"
            );
        }

        return $destination;
    }

    /**
     * Human-readable transport name used in the exception message.
     */
    abstract protected function getTransportName(): string;
}
