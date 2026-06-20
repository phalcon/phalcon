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

namespace Phalcon\Contracts\Queue;

/**
 * Processes a single message. The return value tells the consumer what to
 * do next: acknowledge, reject, or requeue.
 *
 * The literal constant values are kept compatible with the wider interop
 * ecosystem.
 */
interface Processor
{
    public const ACK     = "enqueue.ack";
    public const REJECT  = "enqueue.reject";
    public const REQUEUE = "enqueue.requeue";

    /**
     * Processes the message and returns one of the ACK / REJECT / REQUEUE
     * constants, or an object whose string form is one of those values.
     *
     * @return string|object
     */
    public function process(Message $message, Context $context): string|object;
}
