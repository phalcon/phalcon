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

    public function create($eventName, Model $model): PsrEventInterface
    {
        // @todo use Di ?
        return match ($eventName) {
            'afterCreate' => new AfterCreateEvent($model),
            'afterDelete' => new AfterDeleteEvent($model),
            'afterFetch' => new AfterFetchEvent($model),
            'afterSave' => new AfterSaveEvent($model),
            'afterUpdate' => new AfterUpdateEvent($model),
            'notDeleted' => new NotDeletedEvent($model),
            'notSaved' => new NotSavedEvent($model),
            'onValidationFails' => new OnValidationFailsEvent($model),
            'prepareSave' => new PrepareSaveEvent($model),
            default => throw new UnknownEventTypeException($eventName),
        };
    }
}
