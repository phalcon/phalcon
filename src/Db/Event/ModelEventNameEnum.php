<?php

namespace Phalcon\Db\Event;

use Phalcon\Events\UnknownEventTypeException;

enum ModelEventNameEnum: string
{
    case AFTER_CREATE = 'afterCreate';
    case AFTER_DELETE = 'afterDelete';
    case AFTER_FETCH = 'afterFetch';
    case AFTER_SAVE = 'afterSave';
    case AFTER_UPDATE = 'afterUpdate';
    case AFTER_VALIDATION = 'afterValidation';
    case AFTER_VALIDATION_ON_CREATE = 'afterValidationOnCreate';
    case AFTER_VALIDATION_ON_UPDATE = 'afterValidationOnUpdate';
    case BEFORE_CREATE = 'beforeCreate';
    case BEFORE_DELETE = 'beforeDelete';
    case BEFORE_SAVE = 'beforeSave';
    case BEFORE_UPDATE = 'beforeUpdate';
    case BEFORE_VALIDATION = 'beforeValidation';
    case BEFORE_VALIDATION_ON_CREATE = 'beforeValidationOnCreate';
    case BEFORE_VALIDATION_ON_UPDATE = 'beforeValidationOnUpdate';
    case NOT_DELETED = 'notDeleted';
    case NOT_SAVED = 'notSaved';
    case ON_VALIDATION_FAILS = 'onValidationFails';
    case PREPARE_SAVE = 'prepareSave';
    case VALIDATION = 'validation';

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
            self::AFTER_VALIDATION->value => AfterValidationEvent::class,
            self::AFTER_VALIDATION_ON_CREATE->value => AfterValidationOnCreateEvent::class,
            self::AFTER_VALIDATION_ON_UPDATE->value => AfterValidationOnUpdateEvent::class,
            self::BEFORE_CREATE->value => BeforeCreateEvent::class,
            self::BEFORE_DELETE->value => BeforeDeleteEvent::class,
            self::BEFORE_SAVE->value => BeforeSaveEvent::class,
            self::BEFORE_UPDATE->value => BeforeUpdateEvent::class,
            self::BEFORE_VALIDATION->value => BeforeValidationEvent::class,
            self::BEFORE_VALIDATION_ON_CREATE->value => BeforeValidationOnCreateEvent::class,
            self::BEFORE_VALIDATION_ON_UPDATE->value => BeforeValidationOnUpdateEvent::class,
            self::NOT_DELETED->value => NotDeletedEvent::class,
            self::NOT_SAVED->value => NotSavedEvent::class,
            self::ON_VALIDATION_FAILS->value => OnValidationFailsEvent::class,
            self::PREPARE_SAVE->value => PrepareSaveEvent::class,
            self::VALIDATION->value => ValidationEvent::class,
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
            'AfterValidationEvent' => self::AFTER_VALIDATION,
            'AfterValidationOnCreateEvent' => self::AFTER_VALIDATION_ON_CREATE,
            'AfterValidationOnUpdateEvent' => self::AFTER_VALIDATION_ON_UPDATE,
            'BeforeCreateEvent' => self::BEFORE_CREATE,
            'BeforeDeleteEvent' => self::BEFORE_DELETE,
            'BeforeSaveEvent' => self::BEFORE_SAVE,
            'BeforeUpdateEvent' => self::BEFORE_UPDATE,
            'BeforeValidationEvent' => self::BEFORE_VALIDATION,
            'BeforeValidationOnCreateEvent' => self::BEFORE_VALIDATION_ON_CREATE,
            'BeforeValidationOnUpdateEvent' => self::BEFORE_VALIDATION_ON_UPDATE,
            'NotDeletedEvent' => self::NOT_DELETED,
            'NotSavedEvent' => self::NOT_SAVED,
            'OnValidationFailsEvent' => self::ON_VALIDATION_FAILS,
            'PrepareSaveEvent' => self::PREPARE_SAVE,
            'ValidationEvent' => self::VALIDATION,
            default => throw new UnknownEventTypeException($shortClassName),
        };
    }

    public static function tryFromEventClass(string $eventClassName): self|null
    {
        try {
            return self::fromEventClass($eventClassName);
        } catch (UnknownEventTypeException) {
        }

        return null;
    }
}
