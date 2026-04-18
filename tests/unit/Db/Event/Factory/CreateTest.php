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

namespace Phalcon\Tests\Unit\Db\Event\Factory;

use Phalcon\Db\Event\AfterCreateEvent;
use Phalcon\Db\Event\AfterDeleteEvent;
use Phalcon\Db\Event\AfterSaveEvent;
use Phalcon\Db\Event\BeforeCreateEvent;
use Phalcon\Db\Event\BeforeDeleteEvent;
use Phalcon\Db\Event\BeforeSaveEvent;
use Phalcon\Db\Event\Factory;
use Phalcon\Events\PsrEventInterface;
use Phalcon\Mvc\Model;
use Phalcon\Tests\AbstractUnitTestCase;

final class CreateTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testDbEventFactoryCreateReturnsNullForUnknownEvent(): void
    {
        $factory = new Factory();
        $model   = $this->createMock(Model::class);

        $result = $factory->create('unknownEvent', $model);

        $this->assertNull($result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testDbEventFactoryCreateReturnsCorrectEventClass(): void
    {
        $factory = new Factory();
        $model   = $this->createMock(Model::class);

        $this->assertInstanceOf(AfterCreateEvent::class, $factory->create('afterCreate', $model));
        $this->assertInstanceOf(AfterDeleteEvent::class, $factory->create('afterDelete', $model));
        $this->assertInstanceOf(AfterSaveEvent::class, $factory->create('afterSave', $model));
        $this->assertInstanceOf(BeforeCreateEvent::class, $factory->create('beforeCreate', $model));
        $this->assertInstanceOf(BeforeDeleteEvent::class, $factory->create('beforeDelete', $model));
        $this->assertInstanceOf(BeforeSaveEvent::class, $factory->create('beforeSave', $model));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testDbEventFactoryCreateImplementsPsrEventInterface(): void
    {
        $factory = new Factory();
        $model   = $this->createMock(Model::class);

        $event = $factory->create('afterCreate', $model);

        $this->assertInstanceOf(PsrEventInterface::class, $event);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testDbEventFactoryCreatePassesModelToEvent(): void
    {
        $factory = new Factory();
        $model   = $this->createMock(Model::class);

        /** @var AfterCreateEvent $event */
        $event = $factory->create('afterCreate', $model);

        $this->assertSame($model, $event->model);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testDbEventFactoryHasNoConstructorDependencies(): void
    {
        $factory = new Factory();
        $this->assertInstanceOf(Factory::class, $factory);
    }
}
