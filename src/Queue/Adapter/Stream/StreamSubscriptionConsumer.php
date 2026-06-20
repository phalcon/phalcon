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

use Phalcon\Contracts\Queue\Consumer as ConsumerInterface;
use Phalcon\Contracts\Queue\SubscriptionConsumer as SubscriptionConsumerInterface;

use function call_user_func;
use function microtime;
use function usleep;

/**
 * Consumes from several filesystem queues at once, round-robin polling each
 * subscribed consumer and dispatching messages to its callback. A callback
 * returning false stops consumption.
 */
class StreamSubscriptionConsumer implements SubscriptionConsumerInterface
{
    protected int $pollInterval = 200;

    /**
     * Subscriptions keyed by queue name: [consumer, callback].
     *
     * @var array
     */
    protected array $subscriptions = [];

    public function __construct(protected StreamContext $context)
    {
    }

    public function consume(int $timeout = 0): void
    {
        if (empty($this->subscriptions)) {
            return;
        }

        $sleep     = $this->pollInterval * 1000;
        $startTime = (int) (microtime(true) * 1000);

        while (true) {
            foreach ($this->subscriptions as $subscription) {
                $consumer = $subscription[0];
                $callback = $subscription[1];
                $message  = $consumer->receiveNoWait();

                if ($message !== null) {
                    $result = call_user_func($callback, $message, $consumer);

                    if ($result === false) {
                        return;
                    }
                }
            }

            if ($timeout > 0 && ((int) (microtime(true) * 1000)) - $startTime >= $timeout) {
                return;
            }

            usleep($sleep);
        }
    }

    public function subscribe(ConsumerInterface $consumer, callable $callback): void
    {
        $this->subscriptions[$consumer->getQueue()->getQueueName()] = [$consumer, $callback];
    }

    public function unsubscribe(ConsumerInterface $consumer): void
    {
        unset($this->subscriptions[$consumer->getQueue()->getQueueName()]);
    }

    public function unsubscribeAll(): void
    {
        $this->subscriptions = [];
    }
}
