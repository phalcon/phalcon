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

namespace Phalcon\Queue\Adapter\Traits;

use Phalcon\Contracts\Queue\Consumer as ConsumerInterface;

use function call_user_func;
use function microtime;
use function usleep;

/**
 * Shared subscription-consumer implementation. Implements the round-robin poll
 * loop that dispatches each subscribed consumer's messages to its callback; a
 * callback returning false stops consumption. The loop relies only on the
 * consumer's `receiveNoWait()`, so it is transport-agnostic. Concrete adapters
 * keep just the constructor that captures their context and poll interval.
 */
trait SubscriptionConsumerTrait
{
    protected int $pollInterval = 200;

    /**
     * Subscriptions keyed by queue name: [consumer, callback].
     *
     * @var array
     */
    protected array $subscriptions = [];

    /**
     * Polls every subscription, dispatching each message to its callback,
     * blocking up to timeout milliseconds (0 = block until a callback
     * returns false).
     */
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

    /**
     * Subscribes a consumer; the callback receives each delivered message.
     */
    public function subscribe(ConsumerInterface $consumer, callable $callback): void
    {
        $this->subscriptions[$this->resolveQueueName($consumer)] = [$consumer, $callback];
    }

    /**
     * Removes a previously subscribed consumer.
     */
    public function unsubscribe(ConsumerInterface $consumer): void
    {
        unset($this->subscriptions[$this->resolveQueueName($consumer)]);
    }

    /**
     * Removes every subscribed consumer.
     */
    public function unsubscribeAll(): void
    {
        $this->subscriptions = [];
    }

    /**
     * Resolves a consumer's queue name.
     */
    private function resolveQueueName(ConsumerInterface $consumer): string
    {
        return $consumer->getQueue()->getQueueName();
    }
}
