<?php

namespace Phalcon\Tests\Unit\Events\Manager;

use Phalcon\Events\Manager;
use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Tests\Fixtures\Event\CancellableEventObject;
use Phalcon\Tests\Fixtures\Event\EmptyEventObject;

final class Psr14LayerTest extends AbstractUnitTestCase
{
    public function testDispatchWithStringName(): void
    {
        $result = false;
        $manager = new Manager();
        $manager->attach('test', function () use (&$result) {
            $this->assertTrue(true, 'Event was not dispatched');
            $result = true;
        });

        $manager->dispatch(new \stdClass(), 'test');
        $this->assertTrue($result, 'Event was dispatched');
    }

    public function testDispatchWithObject(): void
    {
        $result = false;
        $manager = new Manager();
        $manager->attach(EmptyEventObject::class, function ($model) use (&$result) {
            $this->assertTrue(true, 'Event was not dispatched');
            $result = true;
        });

        $manager->dispatch(new EmptyEventObject());
        $this->assertTrue($result, 'Event was dispatched');
    }

    public function testDispatchNamedEvent(): void
    {
        $result = false;
        $manager = new Manager();
        $manager->attach('test', function ($model) use (&$result) {
            $this->assertTrue(true, 'Event was not dispatched');
            $result = true;
        });

        $manager->dispatch(new EmptyEventObject(), 'test');
        $this->assertTrue($result, 'Event was dispatched');
    }


    public function testOldStyleAttachAndNewStyleDispatch(): void
    {
        $manager = new Manager();
        $counter = 0;

        $manager->attach('group:test', new class ($counter) {
            public function __construct(private &$c)
            {
            }
            public function __invoke()
            {
                $this->c++;
            }
        });

        $manager->fire('group:test', $this);
        $manager->dispatch(new EmptyEventObject(), 'group:test');

        $this->assertEquals(2, $counter, 'Event was not dispatched twice');
    }

    public function testCancelStopsPropagationToSubsequentListeners(): void
    {
        $manager = new Manager();
        $callOrder = [];

        $manager->attach('cancellable', function (CancellableEventObject $event) use (&$callOrder) {
            $callOrder[] = 'first';
            $event->cancel();
        });

        $manager->attach('cancellable', function (CancellableEventObject $event) use (&$callOrder) {
            $callOrder[] = 'second';
        });

        $manager->dispatch(new CancellableEventObject(), 'cancellable');

        $this->assertSame(['first'], $callOrder, 'Second listener should not have been called after cancel()');
    }

    public function testAllListenersCalledWithoutCancel(): void
    {
        $manager = new Manager();
        $callOrder = [];

        $manager->attach('cancellable', function (CancellableEventObject $event) use (&$callOrder) {
            $callOrder[] = 'first';
        });

        $manager->attach('cancellable', function (CancellableEventObject $event) use (&$callOrder) {
            $callOrder[] = 'second';
        });

        $manager->dispatch(new CancellableEventObject(), 'cancellable');

        $this->assertSame(['first', 'second'], $callOrder, 'Both listeners should have been called');
    }

    public function testCancelByClassNameDispatch(): void
    {
        $manager = new Manager();
        $callOrder = [];

        $manager->attach(CancellableEventObject::class, function (CancellableEventObject $event) use (&$callOrder) {
            $callOrder[] = 'first';
            $event->cancel();
        });

        $manager->attach(CancellableEventObject::class, function (CancellableEventObject $event) use (&$callOrder) {
            $callOrder[] = 'second';
        });

        $manager->dispatch(new CancellableEventObject());

        $this->assertSame(['first'], $callOrder, 'Second listener should not have been called after cancel()');
    }

    public function testIsPropagationStoppedReflectsCancelState(): void
    {
        $event = new CancellableEventObject();

        $this->assertFalse($event->isPropagationStopped(), 'New event should not be stopped');

        $event->cancel();

        $this->assertTrue($event->isPropagationStopped(), 'Cancelled event should be stopped');
    }

    public function testNonCancellableEventIsNotAffectedByStoppableCheck(): void
    {
        $manager = new Manager();
        $callOrder = [];

        $manager->attach('noncancellable', function (EmptyEventObject $event) use (&$callOrder) {
            $callOrder[] = 'first';
        });

        $manager->attach('noncancellable', function (EmptyEventObject $event) use (&$callOrder) {
            $callOrder[] = 'second';
        });

        $manager->dispatch(new EmptyEventObject(), 'noncancellable');

        $this->assertSame(['first', 'second'], $callOrder, 'Both listeners should run for non-cancellable events');
    }
}
