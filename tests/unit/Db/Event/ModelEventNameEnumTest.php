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

namespace Phalcon\Tests\Unit\Db\Event;

use Phalcon\Db\Event\ModelEventNameEnum;
use Phalcon\Db\Event\UnknownEventTypeException;
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ModelEventNameEnumTest extends AbstractUnitTestCase
{
    private const NS = 'Phalcon\Db\Event\\';

    /**
     * @return array<array{string, string}>
     */
    public static function getCases(): array
    {
        return [
            ['afterCreate', self::NS . 'AfterCreateEvent'],
            ['afterDelete', self::NS . 'AfterDeleteEvent'],
            ['afterFetch', self::NS . 'AfterFetchEvent'],
            ['afterSave', self::NS . 'AfterSaveEvent'],
            ['afterUpdate', self::NS . 'AfterUpdateEvent'],
            ['afterValidation', self::NS . 'AfterValidationEvent'],
            ['afterValidationOnCreate', self::NS . 'AfterValidationOnCreateEvent'],
            ['afterValidationOnUpdate', self::NS . 'AfterValidationOnUpdateEvent'],
            ['beforeCreate', self::NS . 'BeforeCreateEvent'],
            ['beforeDelete', self::NS . 'BeforeDeleteEvent'],
            ['beforeSave', self::NS . 'BeforeSaveEvent'],
            ['beforeUpdate', self::NS . 'BeforeUpdateEvent'],
            ['beforeValidation', self::NS . 'BeforeValidationEvent'],
            ['beforeValidationOnCreate', self::NS . 'BeforeValidationOnCreateEvent'],
            ['beforeValidationOnUpdate', self::NS . 'BeforeValidationOnUpdateEvent'],
            ['notDeleted', self::NS . 'NotDeletedEvent'],
            ['notSaved', self::NS . 'NotSavedEvent'],
            ['onValidationFails', self::NS . 'OnValidationFailsEvent'],
            ['prepareSave', self::NS . 'PrepareSaveEvent'],
            ['validation', self::NS . 'ValidationEvent'],
        ];
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-06-06
     */
    #[DataProvider('getCases')]
    public function testFromEventClass(string $eventName, string $eventClass): void
    {
        $this->assertSame(
            $eventName,
            ModelEventNameEnum::fromEventClass($eventClass)->value
        );
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-06-06
     */
    public function testFromEventClassThrowsOnUnknown(): void
    {
        $this->expectException(UnknownEventTypeException::class);

        ModelEventNameEnum::fromEventClass(self::NS . 'NotARealEvent');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-06-06
     */
    #[DataProvider('getCases')]
    public function testGetEventClass(string $eventName, string $eventClass): void
    {
        $this->assertSame(
            $eventClass,
            ModelEventNameEnum::getEventClass($eventName)
        );
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-06-06
     */
    public function testGetEventClassThrowsOnUnknown(): void
    {
        $this->expectException(UnknownEventTypeException::class);

        ModelEventNameEnum::getEventClass('unknownEvent');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-06-06
     */
    #[DataProvider('getCases')]
    public function testTryFromEventClass(string $eventName, string $eventClass): void
    {
        $enum = ModelEventNameEnum::tryFromEventClass($eventClass);

        $this->assertInstanceOf(ModelEventNameEnum::class, $enum);
        $this->assertSame($eventName, $enum->value);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-06-06
     */
    public function testTryFromEventClassReturnsNullOnUnknown(): void
    {
        $this->assertNull(
            ModelEventNameEnum::tryFromEventClass(self::NS . 'NotARealEvent')
        );
    }
}
