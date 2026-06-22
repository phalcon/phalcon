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

use Phalcon\Contracts\Queue\Queue as QueueInterface;
use Phalcon\Queue\Adapter\AbstractPollingConsumer;
use Phalcon\Queue\Adapter\PointToPointStorage;

/**
 * Receives messages from a single filesystem queue. Behavior comes from
 * AbstractPollingConsumer; the blocking `receive()` is the polling loop from
 * AbstractConsumer, driven by the configured poll interval.
 */
class StreamConsumer extends AbstractPollingConsumer
{
    public function __construct(PointToPointStorage $context, QueueInterface $queue, int $pollInterval = 200)
    {
        parent::__construct($context, $queue);

        $this->pollInterval = $pollInterval;
    }
}
