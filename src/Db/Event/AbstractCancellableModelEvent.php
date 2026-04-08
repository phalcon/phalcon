<?php

namespace Phalcon\Db\Event;

use Psr\EventDispatcher\StoppableEventInterface;

abstract class AbstractCancellableModelEvent extends AbstractModelEvent implements StoppableEventInterface
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
