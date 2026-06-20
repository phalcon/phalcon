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
    protected int $jitter = 0;
    protected int $maxMemory = 0;
    protected int $maxMessages = 0;
    protected int $maxSeconds = 0;

    public function __construct(int $maxMessages = 0, int $maxSeconds = 0, int $maxMemory = 0, int $jitter = 0)
    {
        $this->maxMessages = $maxMessages;
        $this->maxSeconds  = $maxSeconds;
        $this->maxMemory   = $maxMemory;
        $this->jitter      = $jitter;
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
