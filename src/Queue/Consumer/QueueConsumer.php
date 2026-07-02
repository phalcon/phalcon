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

namespace Phalcon\Queue\Consumer;

use Phalcon\Contracts\Queue\Consumer as ConsumerInterface;
use Phalcon\Contracts\Queue\Context as ContextInterface;
use Phalcon\Contracts\Queue\Message as MessageInterface;
use Phalcon\Contracts\Queue\Processor as ProcessorInterface;
use Phalcon\Contracts\Queue\Queue as QueueInterface;
use Phalcon\Events\AbstractEventsAware;
use Phalcon\Events\EventsAwareInterface;
use Throwable;

use function microtime;
use function usleep;

/**
 * Lean consumption runner. Binds processors to queues, polls each bound queue
 * round-robin, and dispatches messages to their processors while firing the
 * lifecycle events on Phalcon\Queue\Consumer\Events through the events
 * manager. The long-running operational shell (lifetime, signals) lives in
 * Phalcon\Queue\Consumer\Worker, which drives consumeOnce() and shares the
 * stop signal through stop() / isStopRequested().
 */
class QueueConsumer extends AbstractEventsAware implements EventsAwareInterface
{
    /**
     * Bound processors keyed by queue name.
     *
     * @var array
     */
    protected array $bindings = [];
    protected int $pollInterval = 200;
    protected bool $shouldStop = false;

    public function __construct(protected ContextInterface $context)
    {
    }

    public function bind(QueueInterface $queue, ProcessorInterface $processor): QueueConsumer
    {
        $this->bindings[$queue->getQueueName()] = new BoundProcessor(
            $queue,
            $processor,
            $this->context->createConsumer($queue)
        );

        return $this;
    }

    public function consume(int $timeout = 0): void
    {
        if (!$this->start()) {
            return;
        }

        $startTime = (int) (microtime(true) * 1000);

        while (true) {
            $this->consumeOnce();

            if ($this->shouldStop) {
                break;
            }

            if ($timeout > 0 && ((int) (microtime(true) * 1000)) - $startTime >= $timeout) {
                break;
            }
        }

        $this->end();
    }

    /**
     * Polls every bound queue once, dispatching any messages found. Returns
     * the number of messages processed in this pass, so callers (the Worker)
     * can apply a message-count limit across several bound queues.
     */
    public function consumeOnce(): int
    {
        $processed = 0;

        foreach ($this->bindings as $binding) {
            if ($this->fireManagerEvent(Events::BEFORE_RECEIVE, $binding) === false) {
                continue;
            }

            $consumer = $binding->getConsumer();
            $message  = $consumer->receiveNoWait();

            if ($message === null) {
                continue;
            }

            if ($this->fireManagerEvent(Events::AFTER_RECEIVE, $message) === false) {
                $this->shouldStop = true;

                return $processed;
            }

            $this->process($binding, $message);

            $processed++;
        }

        if ($processed === 0) {
            usleep($this->pollInterval * 1000);
        }

        return $processed;
    }

    public function end(): void
    {
        $this->fireManagerEvent(Events::AFTER_END, null, false);
    }

    public function isStopRequested(): bool
    {
        return $this->shouldStop;
    }

    public function setPollInterval(int $pollInterval): void
    {
        $this->pollInterval = $pollInterval;
    }

    public function start(): bool
    {
        $this->shouldStop = false;

        return $this->fireManagerEvent(Events::BEFORE_START) !== false;
    }

    public function stop(): void
    {
        $this->shouldStop = true;
    }

    private function handleResult(ConsumerInterface $consumer, MessageInterface $message, mixed $result): void
    {
        $outcome = (string) $result;

        if ($outcome === ProcessorInterface::ACK) {
            $consumer->acknowledge($message);
        } elseif ($outcome === ProcessorInterface::REQUEUE) {
            $consumer->reject($message, true);
        } else {
            $consumer->reject($message, false);
        }
    }

    private function process(BoundProcessor $binding, MessageInterface $message): void
    {
        $consumer  = $binding->getConsumer();
        $processor = $binding->getProcessor();

        if ($this->fireManagerEvent(Events::BEFORE_PROCESS, $message) === false) {
            return;
        }

        try {
            $result = $processor->process($message, $this->context);

            $this->handleResult($consumer, $message, $result);

            $this->fireManagerEvent(Events::AFTER_PROCESS, $message, false);
        } catch (Throwable $exception) {
            $this->fireManagerEvent(Events::PROCESSOR_EXCEPTION, $exception, false);

            $consumer->reject($message, false);
        }
    }
}
