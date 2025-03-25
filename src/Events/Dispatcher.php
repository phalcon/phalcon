<?php

namespace Phalcon\Events;

use Psr\EventDispatcher\EventDispatcherInterface;

class Dispatcher implements EventDispatcherInterface
{
    public function __construct(
        protected Manager $eventsManager,
    ) {
    }

    public function dispatch(object $event, object $source = null)
    {
        if ($event instanceof Event) {
            return $this->eventsManager->fire(
                $event->getType(),
                $event->getSource() ?? $this,
                $event,
                $event->isCancelable()
            );
        }

        return $this->eventsManager->fire($event::class, $source ?? $this, $event);
    }
}
