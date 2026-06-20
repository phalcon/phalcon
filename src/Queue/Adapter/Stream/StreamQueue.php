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

namespace Phalcon\Queue\Adapter\Stream;

use Phalcon\Contracts\Queue\Queue as QueueInterface;

/**
 * A named filesystem queue destination.
 */
class StreamQueue implements QueueInterface
{
    public function __construct(protected string $queueName)
    {
    }

    public function getQueueName(): string
    {
        return $this->queueName;
    }
}
