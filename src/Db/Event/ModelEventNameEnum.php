<?php

namespace Phalcon\Db\Event;

enum ModelEventNameEnum: string
{
    case AFTER_CREATE = 'afterCreate';
    case AFTER_DELETE = 'afterDelete';
    case AFTER_FETCH = 'afterFetch';
    case AFTER_SAVE = 'afterSave';
    case AFTER_UPDATE = 'afterUpdate';
    case NOT_DELETED = 'notDeleted';
    case NOT_SAVED = 'notSaved';
    case ON_VALIDATION_FAILS = 'onValidationFails';
    case PREPARE_SAVE = 'prepareSave';

    /**
     * Get the event class associated with this event type
     *
     * @return string The fully qualified class name
     */
    public static function getEventClass($eventName): string
    {
        return match ($eventName) {
            self::AFTER_CREATE->value => AfterCreateEvent::class,
            self::AFTER_DELETE->value => AfterDeleteEvent::class,
            self::AFTER_FETCH->value => AfterFetchEvent::class,
            self::AFTER_SAVE->value => AfterSaveEvent::class,
            self::AFTER_UPDATE->value => AfterUpdateEvent::class,
            self::NOT_DELETED->value => NotDeletedEvent::class,
            self::NOT_SAVED->value => NotSavedEvent::class,
            self::ON_VALIDATION_FAILS->value => OnValidationFailsEvent::class,
            self::PREPARE_SAVE->value => PrepareSaveEvent::class,
            default => throw new UnknownEventTypeException($eventName),
        };
    }

    /**
     * Get an enum case from event class name
     *
     * @param string $eventClassName
     * @return self
     * @throws UnknownEventTypeException
     */
    public static function fromEventClass(string $eventClassName): self
    {
        // Extract a class name without a namespace
        $shortClassName = substr($eventClassName, strrpos($eventClassName, '\\') + 1);

        return match ($shortClassName) {
            'AfterCreateEvent' => self::AFTER_CREATE,
            'AfterDeleteEvent' => self::AFTER_DELETE,
            'AfterFetchEvent' => self::AFTER_FETCH,
            'AfterSaveEvent' => self::AFTER_SAVE,
            'AfterUpdateEvent' => self::AFTER_UPDATE,
            'NotDeletedEvent' => self::NOT_DELETED,
            'NotSavedEvent' => self::NOT_SAVED,
            'OnValidationFailsEvent' => self::ON_VALIDATION_FAILS,
            'PrepareSaveEvent' => self::PREPARE_SAVE,
            default => throw new UnknownEventTypeException($shortClassName),
        };
    }
}
