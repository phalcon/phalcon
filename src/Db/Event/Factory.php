<?php

namespace Phalcon\Db\Event;

use Phalcon\Di\Di;
use Phalcon\Events\PsrEventInterface;
use Phalcon\Mvc\Model;

class Factory
{
    public function __construct(protected Di $di)
    {
    }

    public function create($eventName, Model $model): ?PsrEventInterface
    {
        try {
            $className = ModelEventNameEnum::getEventClass($eventName);
            return $this->di->get($className, [$model]);
        } catch (UnknownEventTypeException $e) {
            return null;
        }
    }
}
