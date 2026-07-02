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

namespace Phalcon\Queue\Cli;

use Phalcon\Cli\Dispatcher;
use Phalcon\Cli\Task;
use Phalcon\Queue\Consumer\QueueConsumer;
use Phalcon\Queue\Consumer\Worker;
use Phalcon\Queue\Consumer\WorkerOptions;
use Phalcon\Queue\QueueFactory;

/**
 * Optional CLI runner for a queue worker - the only class coupled to
 * Phalcon\Cli. A thin adapter: it resolves the context from the `queueFactory`
 * service, binds one queue to one processor (both given as command arguments),
 * and runs a Worker whose lifetime bounds come from CLI options. Users not on
 * Phalcon\Cli use Worker directly.
 *
 * Usage:
 *     <task> <queueName> <processorServiceId> \
 *         [--max-messages=N] [--max-time=SECONDS] \
 *         [--max-memory=MB] [--jitter=SECONDS]
 *
 * Register it in your own Phalcon\Cli\Console; it is not auto-wired into
 * FactoryDefault.
 */
class ConsumerTask extends Task
{
    public function mainAction(): int
    {
        $di = $this->getDI();

        /** @var Dispatcher $dispatcher */
        $dispatcher = $di->get('dispatcher');
        /** @var QueueFactory $queueFactory */
        $queueFactory = $di->get('queueFactory');

        $params    = $dispatcher->getParams();
        $queueName = (string) ($params[0] ?? '');
        $processor = (string) ($params[1] ?? '');

        $context  = $queueFactory->load($di->get('config')->queue);
        $consumer = new QueueConsumer($context);

        $consumer->bind(
            $context->createQueue($queueName),
            $di->get($processor)
        );

        $options = new WorkerOptions(
            (int) $dispatcher->getOption('max-messages', null, 0),
            (int) $dispatcher->getOption('max-time', null, 0),
            (int) $dispatcher->getOption('max-memory', null, 0),
            (int) $dispatcher->getOption('jitter', null, 0),
        );

        (new Worker($consumer, $options))->run();

        return 0;
    }
}
