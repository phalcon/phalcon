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

/**
 * Internal storage contract for transports whose consumer simply pops the next
 * message and pushes one back to requeue. Implemented by the contexts that back
 * an AbstractPollingConsumer; it is not part of the public Context API.
 */
interface PointToPointStorage
{
    /**
     * Removes and returns the front message of a queue, or null when empty.
     */
    public function popMessage(string $queueName): ?MessageInterface;

    /**
     * Appends a message to the back of a queue.
     */
    public function pushMessage(string $queueName, MessageInterface $message): void;
}
