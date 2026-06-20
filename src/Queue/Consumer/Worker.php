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

namespace Phalcon\Queue\Consumer;

use function function_exists;
use function memory_get_usage;
use function pcntl_async_signals;
use function pcntl_signal;
use function random_int;
use function time;

/**
 * Long-running operational shell around a QueueConsumer. Owns the outer loop,
 * the bounded lifetime (max messages / seconds / memory, plus jitter) and -
 * when ext-pcntl is available - graceful shutdown on SIGTERM/SIGINT/SIGQUIT.
 * The current message always finishes before the loop stops (drain, not
 * guillotine), because the stop flag is only checked between iterations.
 */
class Worker
{
    protected WorkerOptions $options;

    public function __construct(protected QueueConsumer $consumer, ?WorkerOptions $options = null)
    {
        $this->options = $options ?? new WorkerOptions();
    }

    /**
     * Signal handler: requests a graceful stop.
     */
    public function handleSignal(int $signal): void
    {
        $this->consumer->stop();
    }

    /**
     * Runs the worker until a lifetime bound trips or a stop is requested.
     * Returns the number of messages processed.
     */
    public function run(): int
    {
        $options     = $this->options;
        $processed   = 0;
        $maxMessages = $options->getMaxMessages();
        $maxSeconds  = $options->getMaxSeconds();
        $maxMemory   = $options->getMaxMemory();
        $jitter      = $options->getJitter();

        if (!$this->consumer->start()) {
            return 0;
        }

        $deadline = 0;

        if ($maxSeconds > 0) {
            $deadline = time() + $maxSeconds;

            if ($jitter > 0) {
                $deadline += random_int(0, $jitter);
            }
        }

        $this->installSignalHandlers();

        while (true) {
            if ($this->consumer->isStopRequested()) {
                break;
            }

            if ($this->consumer->consumeOnce()) {
                $processed++;

                if ($maxMessages > 0 && $processed >= $maxMessages) {
                    break;
                }
            }

            if ($deadline > 0 && time() >= $deadline) {
                break;
            }

            if ($maxMemory > 0 && memory_get_usage(true) >= $maxMemory * 1048576) {
                break;
            }
        }

        $this->consumer->end();

        return $processed;
    }

    private function installSignalHandlers(): void
    {
        if (!function_exists('pcntl_async_signals')) {
            return;
        }

        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, [$this, 'handleSignal']);
        pcntl_signal(SIGINT, [$this, 'handleSignal']);
        pcntl_signal(SIGQUIT, [$this, 'handleSignal']);
    }
}
