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

namespace Phalcon\Tests\Unit\Queue\Consumer;

use Phalcon\Contracts\Queue\Context;
use Phalcon\Contracts\Queue\Message;
use Phalcon\Contracts\Queue\Processor;
use Phalcon\Events\Manager;
use Phalcon\Queue\Adapter\Memory\MemoryConnectionFactory;
use Phalcon\Queue\Consumer\QueueConsumer;
use Phalcon\Queue\Consumer\Worker;
use Phalcon\Queue\Consumer\WorkerOptions;
use Phalcon\Tests\AbstractUnitTestCase;
use RuntimeException;

final class QueueConsumerTest extends AbstractUnitTestCase
{
    public function testAfterReceiveFiresOnlyWhenAMessageArrives(): void
    {
        $context = (new MemoryConnectionFactory())->createContext();
        $queue   = $context->createQueue('jobs');

        $received = 0;
        $manager  = new Manager();
        $manager->attach(
            'queue',
            function ($event) use (&$received) {
                if ($event->getType() === 'afterReceive') {
                    $received++;
                }
            }
        );

        $consumer = new QueueConsumer($context);
        $consumer->setEventsManager($manager);
        $consumer->bind($queue, $this->collectingProcessor());

        // Empty queue: afterReceive must not fire.
        $consumer->consumeOnce();
        $this->assertSame(0, $received);

        // One message: afterReceive fires exactly once.
        $context->createProducer()->send($queue, $context->createMessage('payload'));
        $consumer->consumeOnce();
        $this->assertSame(1, $received);
    }

    public function testProcessorExceptionRejectsAndFiresEvent(): void
    {
        $context = (new MemoryConnectionFactory())->createContext();
        $queue   = $context->createQueue('jobs');
        $context->createProducer()->send($queue, $context->createMessage('boom'));

        $processor = new class implements Processor {
            public function process(Message $message, Context $context): string | object
            {
                throw new RuntimeException('processor failed');
            }
        };

        $fired   = false;
        $manager = new Manager();
        $manager->attach(
            'queue',
            function ($event) use (&$fired) {
                if ($event->getType() === 'processorException') {
                    $fired = true;
                }
            }
        );

        $consumer = new QueueConsumer($context);
        $consumer->setEventsManager($manager);
        $consumer->bind($queue, $processor);

        $consumer->consumeOnce();

        $this->assertTrue($fired);
        $this->assertNull($context->createConsumer($queue)->receiveNoWait());
    }

    public function testRequeueRedeliversTheMessage(): void
    {
        $context = (new MemoryConnectionFactory())->createContext();
        $queue   = $context->createQueue('jobs');
        $context->createProducer()->send($queue, $context->createMessage('payload'));

        $processor         = $this->collectingProcessor();
        $processor->return = Processor::REQUEUE;

        $consumer = new QueueConsumer($context);
        $consumer->bind($queue, $processor);

        $consumer->consumeOnce();
        $consumer->consumeOnce();

        $this->assertSame(['payload', 'payload'], $processor->seen);
    }

    public function testWorkerMaxMessagesCountsMessagesAcrossBindings(): void
    {
        $context  = (new MemoryConnectionFactory())->createContext();
        $queueOne = $context->createQueue('queue-one');
        $queueTwo = $context->createQueue('queue-two');
        $producer = $context->createProducer();

        $producer->send($queueOne, $context->createMessage('one'));
        $producer->send($queueTwo, $context->createMessage('two'));

        $consumer = new QueueConsumer($context);
        $consumer->bind($queueOne, $this->collectingProcessor());
        $consumer->bind($queueTwo, $this->collectingProcessor());

        $processed = (new Worker($consumer, new WorkerOptions(maxMessages: 2, maxSeconds: 2)))->run();

        $this->assertSame(2, $processed);
    }

    public function testWorkerProcessesUpToMaxMessages(): void
    {
        $context  = (new MemoryConnectionFactory())->createContext();
        $queue    = $context->createQueue('jobs');
        $producer = $context->createProducer();

        for ($i = 0; $i < 5; $i++) {
            $producer->send($queue, $context->createMessage((string) $i));
        }

        $processor = $this->collectingProcessor();

        $consumer = new QueueConsumer($context);
        $consumer->bind($queue, $processor);

        $processed = (new Worker($consumer, new WorkerOptions(maxMessages: 5)))->run();

        $this->assertSame(5, $processed);
        $this->assertSame(['0', '1', '2', '3', '4'], $processor->seen);
    }

    private function collectingProcessor(): Processor
    {
        return new class implements Processor {
            public array $seen = [];
            public string $return = Processor::ACK;

            public function process(Message $message, Context $context): string | object
            {
                $this->seen[] = $message->getBody();

                return $this->return;
            }
        };
    }
}
