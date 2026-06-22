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
use Phalcon\Contracts\Queue\Destination as DestinationInterface;
use Phalcon\Contracts\Queue\Message as MessageInterface;
use Phalcon\Contracts\Queue\Producer as ProducerInterface;
use Phalcon\Contracts\Queue\Queue as QueueInterface;
use Phalcon\Contracts\Queue\SubscriptionConsumer as SubscriptionConsumerInterface;
use Phalcon\Queue\Adapter\AbstractContext;
use Phalcon\Queue\Adapter\MessageEnvelope;
use Phalcon\Queue\Adapter\PointToPointStorage;

use function array_filter;
use function array_shift;
use function base64_decode;
use function base64_encode;
use function count;
use function explode;
use function fclose;
use function file_exists;
use function file_put_contents;
use function flock;
use function fopen;
use function ftruncate;
use function fwrite;
use function implode;
use function is_dir;
use function md5;
use function mkdir;
use function preg_replace;
use function rewind;
use function rtrim;
use function stream_get_contents;
use function strlen;
use function substr;
use function unlink;

use const FILE_APPEND;
use const LOCK_EX;
use const LOCK_UN;
use const PHP_EOL;

/**
 * Filesystem transport session. Each queue is one append-only file under the
 * configured directory; cross-process safety comes from flock. One message
 * per line, stored as base64(serialize([...])) so bodies with newlines are
 * safe.
 */
class StreamContext extends AbstractContext implements PointToPointStorage
{
    protected string $storageDir = "";

    public function __construct(string $storageDir, protected int $pollInterval = 200)
    {
        $this->storageDir = rtrim($storageDir, "/") . "/";
    }

    public function close(): void
    {
        $this->purgeTemporaryQueues();
    }

    public function createConsumer(DestinationInterface $destination): ConsumerInterface
    {
        $queue = $this->assertQueueDestination($destination, "consume from");

        return new StreamConsumer($this, $queue, $this->pollInterval);
    }

    public function createMessage(string $body = "", array $properties = [], array $headers = []): MessageInterface
    {
        return new StreamMessage($body, $properties, $headers);
    }

    public function createProducer(): ProducerInterface
    {
        return new StreamProducer($this);
    }

    public function createSubscriptionConsumer(): SubscriptionConsumerInterface
    {
        return new StreamSubscriptionConsumer($this, $this->pollInterval);
    }

    /**
     * Removes the front message from a queue file, or null when it is empty.
     * Internal transport API used by StreamConsumer.
     */
    public function popMessage(string $queueName): ?MessageInterface
    {
        $filepath = $this->getFilepath($queueName);

        if (!file_exists($filepath)) {
            return null;
        }

        $pointer = fopen($filepath, "c+");

        if ($pointer === false) {
            return null;
        }

        if (!flock($pointer, LOCK_EX)) {
            fclose($pointer);

            return null;
        }

        $contents = stream_get_contents($pointer);
        $lines    = array_filter(explode(PHP_EOL, $contents));

        if (empty($lines)) {
            flock($pointer, LOCK_UN);
            fclose($pointer);

            return null;
        }

        $line      = array_shift($lines);
        $remaining = count($lines) > 0 ? implode(PHP_EOL, $lines) . PHP_EOL : "";

        /**
         * Write the surviving messages back before shortening the file, so an
         * interruption can never leave the queue empty (truncate-then-write
         * would). At worst a stale trailing line remains, which the next pop
         * discards as unparseable.
         */
        rewind($pointer);
        fwrite($pointer, $remaining);
        ftruncate($pointer, strlen($remaining));
        flock($pointer, LOCK_UN);
        fclose($pointer);

        return MessageEnvelope::decode(
            (string) base64_decode($line),
            static fn (string $body, array $properties, array $headers): StreamMessage
                => new StreamMessage($body, $properties, $headers)
        );
    }

    public function purgeQueue(QueueInterface $queue): void
    {
        $filepath = $this->getFilepath($queue->getQueueName());

        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    /**
     * Appends a message to the back of a queue file.
     * Internal transport API used by StreamProducer.
     */
    public function pushMessage(string $queueName, MessageInterface $message): void
    {
        $filepath = $this->getFilepath($queueName);

        $this->ensureDir();

        $line = base64_encode(MessageEnvelope::encode($message)) . PHP_EOL;

        file_put_contents($filepath, $line, FILE_APPEND | LOCK_EX);
    }

    protected function getTransportName(): string
    {
        return "Stream";
    }

    private function ensureDir(): void
    {
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0700, true);
        }
    }

    /**
     * Maps a queue name to its file. A short hash of the original name is
     * appended so distinct names that sanitize to the same string (for example
     * "a.b" and "a:b") never share a file.
     */
    private function getFilepath(string $queueName): string
    {
        return $this->storageDir
            . preg_replace("/[^a-zA-Z0-9_-]/", "_", $queueName)
            . "-" . substr(md5($queueName), 0, 8)
            . ".queue";
    }
}
