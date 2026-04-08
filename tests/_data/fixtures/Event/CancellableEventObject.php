<?php

namespace Phalcon\Tests\Fixtures\Event;

use Psr\EventDispatcher\StoppableEventInterface;

class CancellableEventObject implements StoppableEventInterface
{
    private bool $cancelled = false;

    public function cancel(): void
    {
        $this->cancelled = true;
    }

    public function isPropagationStopped(): bool
    {
        return $this->cancelled;
    }
}
