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

namespace Phalcon\Queue\Adapter\Redis;

use Phalcon\Contracts\Queue\Consumer as ConsumerInterface;
use Phalcon\Contracts\Queue\Destination as DestinationInterface;
use Phalcon\Contracts\Queue\Message as MessageInterface;
use Phalcon\Contracts\Queue\Producer as ProducerInterface;
use Phalcon\Contracts\Queue\Queue as QueueInterface;
use Phalcon\Contracts\Queue\SubscriptionConsumer as SubscriptionConsumerInterface;
use Phalcon\Queue\Adapter\AbstractContext;
use Phalcon\Queue\Adapter\MessageEnvelope;
use Phalcon\Queue\Adapter\QueueDestinationGuard;
use Redis as RedisService;

use function count;
use function is_array;
use function is_string;
use function microtime;
use function strpos;
use function substr;
use function uniqid;

/**
 * Redis transport session (ext-redis). Each queue is a Redis list; messages
 * are LPUSHed on send and RPOP/BRPOPed on receive, giving FIFO delivery.
 * Delayed messages live in a companion sorted set (`<key>:delayed`) scored by
 * their due time in milliseconds, and are promoted into the list once due. The
 * destination factories come from AbstractContext.
 */
class RedisContext extends AbstractContext
{
    public function __construct(
        protected RedisService $redis,
        protected string $prefix = "phalcon_queue:",
        protected int $pollInterval = 200
    ) {
    }

    /**
     * Blocking pop from the back of a queue list. Promotes any due delayed
     * messages first, then blocks up to timeout seconds. Internal transport
     * API used by RedisConsumer.
     */
    public function blockingPop(string $queueName, int $timeout): ?MessageInterface
    {
        $this->promote($queueName);

        $result = $this->redis->brPop([$this->listKey($queueName)], $timeout);

        if (is_array($result) && count($result) >= 2) {
            return $this->buildMessage((string) $result[1]);
        }

        return null;
    }

    public function close(): void
    {
    }

    public function createConsumer(DestinationInterface $destination): ConsumerInterface
    {
        QueueDestinationGuard::assertQueue($destination, "consume from");

        return new RedisConsumer($this, $destination);
    }

    public function createMessage(string $body = "", array $properties = [], array $headers = []): MessageInterface
    {
        return new RedisMessage($body, $properties, $headers);
    }

    public function createProducer(): ProducerInterface
    {
        return new RedisProducer($this);
    }

    public function createSubscriptionConsumer(): SubscriptionConsumerInterface
    {
        return new RedisSubscriptionConsumer($this, $this->pollInterval);
    }

    /**
     * Non-blocking pop from the back of a queue list, or null when empty.
     * Promotes any due delayed messages first. Internal transport API used
     * by RedisConsumer.
     */
    public function popMessage(string $queueName): ?MessageInterface
    {
        $this->promote($queueName);

        $payload = $this->redis->rPop($this->listKey($queueName));

        if (!is_string($payload)) {
            return null;
        }

        return $this->buildMessage($payload);
    }

    public function purgeQueue(QueueInterface $queue): void
    {
        $queueName = $queue->getQueueName();

        $this->redis->del($this->listKey($queueName));
        $this->redis->del($this->delayedKey($queueName));
    }

    /**
     * Sends a message to a queue. With a positive delay (milliseconds) the
     * message is parked in the delayed set; otherwise it is pushed onto the
     * front of the list. Internal transport API used by RedisProducer.
     */
    public function pushMessage(string $queueName, MessageInterface $message, int $delay = 0): void
    {
        $payload = MessageEnvelope::encode($message);

        if ($delay > 0) {
            $score  = $this->now() + $delay;
            $member = uniqid("", true) . "|" . $payload;

            $this->redis->zAdd($this->delayedKey($queueName), $score, $member);

            return;
        }

        $this->redis->lPush($this->listKey($queueName), $payload);
    }

    private function buildMessage(string $payload): ?MessageInterface
    {
        $data = MessageEnvelope::decode($payload);

        if ($data === null) {
            return null;
        }

        return new RedisMessage((string) $data["body"], $data["properties"], $data["headers"]);
    }

    private function delayedKey(string $queueName): string
    {
        return $this->prefix . $queueName . ":delayed";
    }

    private function listKey(string $queueName): string
    {
        return $this->prefix . $queueName;
    }

    private function now(): int
    {
        return (int) (microtime(true) * 1000);
    }

    /**
     * Moves every due message from the delayed set onto the queue list. The
     * ZREM acts as an atomic claim, so a message promoted by one process is
     * never promoted again by another.
     */
    private function promote(string $queueName): void
    {
        $delayedKey = $this->delayedKey($queueName);
        $listKey    = $this->listKey($queueName);
        $due        = $this->redis->zRangeByScore($delayedKey, "0", (string) $this->now());

        if (!is_array($due)) {
            return;
        }

        foreach ($due as $member) {
            if (1 === $this->redis->zRem($delayedKey, $member)) {
                $position = strpos($member, "|");
                $payload  = substr($member, $position + 1);

                $this->redis->lPush($listKey, $payload);
            }
        }
    }
}
