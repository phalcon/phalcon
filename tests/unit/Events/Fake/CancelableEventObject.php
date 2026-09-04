<?php

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Events\Fake;

use Psr\EventDispatcher\StoppableEventInterface;

class CancelableEventObject implements StoppableEventInterface
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
