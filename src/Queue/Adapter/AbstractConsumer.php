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

use Phalcon\Contracts\Queue\Consumer as ConsumerInterface;
use Phalcon\Contracts\Queue\Message as MessageInterface;
use Phalcon\Contracts\Queue\Queue as QueueInterface;

use function microtime;
use function usleep;

/**
 * Shared consumer base. Implements the blocking `receive()` as a polling loop
 * on top of the abstract `receiveNoWait()`; concrete consumers provide the
 * transport-specific `receiveNoWait`, `acknowledge`, `reject` and `getQueue`.
 *
 * Transports with a native blocking receive (Redis BRPOP, Beanstalk reserve)
 * override `receive()` instead of polling.
 */
abstract class AbstractConsumer implements ConsumerInterface
{
    protected int $pollInterval = 200;
    protected QueueInterface $queue;

    abstract public function acknowledge(MessageInterface $message): void;

    public function getQueue(): QueueInterface
    {
        return $this->queue;
    }

    public function receive(int $timeout = 0): ?MessageInterface
    {
        $sleep     = $this->pollInterval * 1000;
        $startTime = (int) (microtime(true) * 1000);

        while (true) {
            $message = $this->receiveNoWait();

            if ($message !== null) {
                return $message;
            }

            if ($timeout > 0 && ((int) (microtime(true) * 1000)) - $startTime >= $timeout) {
                return null;
            }

            usleep($sleep);
        }
    }

    abstract public function receiveNoWait(): ?MessageInterface;

    abstract public function reject(MessageInterface $message, bool $requeue = false): void;

    public function setPollInterval(int $pollInterval): void
    {
        $this->pollInterval = $pollInterval;
    }
}
