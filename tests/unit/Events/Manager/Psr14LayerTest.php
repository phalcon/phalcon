<?php

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Events\Manager;

use Phalcon\Events\Manager;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Events\Fake\CancelableEventObject;
use Phalcon\Tests\Unit\Events\Fake\EmptyEventObject;

final class Psr14LayerTest extends AbstractUnitTestCase
{
    public function testAllListenersCalledWithoutCancel(): void
    {
        $manager   = new Manager();
        $callOrder = [];

        $manager->attach('cancelable', function (CancelableEventObject $event) use (&$callOrder) {
            $callOrder[] = 'first';
        });

        $manager->attach('cancelable', function (CancelableEventObject $event) use (&$callOrder) {
            $callOrder[] = 'second';
        });

        $manager->dispatch(new CancelableEventObject(), 'cancelable');

        $this->assertSame(['first', 'second'], $callOrder, 'Both listeners should have been called');
    }

    public function testCancelByClassNameDispatch(): void
    {
        $manager   = new Manager();
        $callOrder = [];

        $manager->attach(CancelableEventObject::class, function (CancelableEventObject $event) use (&$callOrder) {
            $callOrder[] = 'first';
            $event->cancel();
        });

        $manager->attach(CancelableEventObject::class, function (CancelableEventObject $event) use (&$callOrder) {
            $callOrder[] = 'second';
        });

        $manager->dispatch(new CancelableEventObject());

        $this->assertSame(['first'], $callOrder, 'Second listener should not have been called after cancel()');
    }

    public function testCancelStopsPropagationToSubsequentListeners(): void
    {
        $manager   = new Manager();
        $callOrder = [];

        $manager->attach('cancelable', function (CancelableEventObject $event) use (&$callOrder) {
            $callOrder[] = 'first';
            $event->cancel();
        });

        $manager->attach('cancelable', function (CancelableEventObject $event) use (&$callOrder) {
            $callOrder[] = 'second';
        });

        $manager->dispatch(new CancelableEventObject(), 'cancelable');

        $this->assertSame(['first'], $callOrder, 'Second listener should not have been called after cancel()');
    }

    public function testDispatchNamedEvent(): void
    {
        $result  = false;
        $manager = new Manager();
        $manager->attach('test', function ($model) use (&$result) {
            $this->assertTrue(true, 'Event was not dispatched');
            $result = true;
        });

        $manager->dispatch(new EmptyEventObject(), 'test');
        $this->assertTrue($result, 'Event was dispatched');
    }

    /**
     * Tests Phalcon\Events\Manager :: dispatch() - returns null when no
     * matching listener (L270)
     *
     * When events are registered but none match the dispatched event name
     * or class, dispatch() falls through to return null.
     */
    public function testDispatchReturnsNullWhenNoMatch(): void
    {
        $manager = new Manager();
        $manager->attach('known', function () {
            return 'called';
        });

        // Dispatch with a name that doesn't match any registered event
        $result = $manager->dispatch(new EmptyEventObject(), 'unknown');

        $this->assertNull($result);
    }

    /**
     * Tests Phalcon\Events\Manager :: dispatch() - array name (L258)
     *
     * When dispatch() is called with an array $name, it joins the parts
     * with ':' to form the event type string.
     */
    public function testDispatchWithArrayName(): void
    {
        $called  = false;
        $manager = new Manager();
        $manager->attach('group:action', function () use (&$called) {
            $called = true;
        });

        $manager->dispatch(new EmptyEventObject(), ['group', 'action']);

        $this->assertTrue($called);
    }

    public function testDispatchWithObject(): void
    {
        $result  = false;
        $manager = new Manager();
        $manager->attach(EmptyEventObject::class, function ($model) use (&$result) {
            $this->assertTrue(true, 'Event was not dispatched');
            $result = true;
        });

        $manager->dispatch(new EmptyEventObject());
        $this->assertTrue($result, 'Event was dispatched');
    }
    public function testDispatchWithStringName(): void
    {
        $result  = false;
        $manager = new Manager();
        $manager->attach('test', function () use (&$result) {
            $this->assertTrue(true, 'Event was not dispatched');
            $result = true;
        });

        $manager->dispatch(new \stdClass(), 'test');
        $this->assertTrue($result, 'Event was dispatched');
    }

    public function testIsPropagationStoppedReflectsCancelState(): void
    {
        $event = new CancelableEventObject();

        $this->assertFalse($event->isPropagationStopped(), 'New event should not be stopped');

        $event->cancel();

        $this->assertTrue($event->isPropagationStopped(), 'Cancelled event should be stopped');
    }

    public function testNonCancelableEventIsNotAffectedByStoppableCheck(): void
    {
        $manager   = new Manager();
        $callOrder = [];

        $manager->attach('noncancelable', function (EmptyEventObject $event) use (&$callOrder) {
            $callOrder[] = 'first';
        });

        $manager->attach('noncancelable', function (EmptyEventObject $event) use (&$callOrder) {
            $callOrder[] = 'second';
        });

        $manager->dispatch(new EmptyEventObject(), 'noncancelable');

        $this->assertSame(['first', 'second'], $callOrder, 'Both listeners should run for non-cancelable events');
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
}
