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

/**
 * Immutable lifetime bounds for a Worker. A value of 0 means "no limit".
 * The worker stops on whichever bound trips first.
 */
class WorkerOptions
{
    /**
     * @param int $maxMessages Maximum number of messages to process (0 = no limit).
     * @param int $maxSeconds  Maximum run time in seconds (0 = no limit).
     * @param int $maxMemory   Memory ceiling in megabytes (0 = no limit).
     * @param int $jitter      Seconds added to maxSeconds (randomised per worker),
     *                         so a pool does not restart in lockstep.
     */
    public function __construct(
        protected int $maxMessages = 0,
        protected int $maxSeconds = 0,
        protected int $maxMemory = 0,
        protected int $jitter = 0
    ) {
    }

    public function getJitter(): int
    {
        return $this->jitter;
    }

    public function getMaxMemory(): int
    {
        return $this->maxMemory;
    }

    public function getMaxMessages(): int
    {
        return $this->maxMessages;
    }

    public function getMaxSeconds(): int
    {
        return $this->maxSeconds;
    }
}
