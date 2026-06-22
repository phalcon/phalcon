<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Queue\Adapter\Stream;

use Phalcon\Contracts\Queue\Context as ContextInterface;
use Phalcon\Queue\Adapter\Stream\StreamConnectionFactory;
use Phalcon\Tests\AbstractUnitTestCase;

use function count;
use function fileperms;
use function glob;
use function is_dir;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

final class StreamStorageTest extends AbstractUnitTestCase
{
    private string $storageDir = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->storageDir = sys_get_temp_dir() . '/phalcon-queue-storage-' . uniqid('', true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->storageDir)) {
            foreach ((array) glob($this->storageDir . '/*') as $file) {
                unlink($file);
            }

            rmdir($this->storageDir);
        }

        parent::tearDown();
    }

    public function testCloseRemovesTemporaryQueues(): void
    {
        $context = $this->createContext();
        $queue   = $context->createTemporaryQueue();

        $context->createProducer()->send($queue, $context->createMessage('temp'));

        $this->assertCount(1, (array) glob($this->storageDir . '/*.queue'));

        $context->close();

        $this->assertCount(0, (array) glob($this->storageDir . '/*.queue'));
    }

    public function testQueueNamesThatSanitizeAliasDoNotCollide(): void
    {
        $context  = $this->createContext();
        $producer = $context->createProducer();

        // Both names sanitize to "a_b"; they must remain distinct queues.
        $producer->send($context->createQueue('a.b'), $context->createMessage('only-a-dot-b'));

        // The aliasing name must see an empty queue, not the other queue's message.
        $this->assertNull(
            $context->createConsumer($context->createQueue('a:b'))->receiveNoWait()
        );

        $this->assertSame(
            'only-a-dot-b',
            $context->createConsumer($context->createQueue('a.b'))->receiveNoWait()->getBody()
        );
    }

    public function testStorageDirectoryIsCreatedPrivately(): void
    {
        $context = $this->createContext();

        // Pushing the first message triggers directory creation.
        $context->createProducer()->send(
            $context->createQueue('perms'),
            $context->createMessage('payload')
        );

        $this->assertDirectoryExists($this->storageDir);
        $this->assertSame(0700, fileperms($this->storageDir) & 0777);
    }

    private function createContext(): ContextInterface
    {
        return (new StreamConnectionFactory(['storageDir' => $this->storageDir]))
            ->createContext();
    }
}
