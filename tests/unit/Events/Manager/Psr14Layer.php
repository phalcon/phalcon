<?php

namespace Phalcon\Tests\Unit\Events\Manager;

use Phalcon\Events\Manager;
use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Tests\Fixtures\Event\EmptyEventObject;

final class Psr14Layer extends AbstractUnitTestCase
{

    public function testDispatchWithStringName(): void
    {
        $manager = new Manager();
        $manager->attach('test', function () {
            $this->assertTrue(true, 'Event was not dispatched');
        });

        $manager->dispatch(new \stdClass(), 'test');
    }

    public function testDispatchWithObject(): void
    {
        $manager = new Manager();

        $manager->attach(EmptyEventObject::class, function ($model) {
            $this->assertTrue(true, 'Event was not dispatched');
        });

        $manager->dispatch(new EmptyEventObject());
    }

    public function testDispatchNamedEvent(): void
    {
        $manager = new Manager();

        $manager->attach('test', function ($model) {
            $this->assertTrue(true, 'Event was not dispatched');
        });

        $manager->dispatch(new EmptyEventObject(), 'test');
    }


    public function testOldStyleAttachAndNewStyleDispatch(): void
    {
        $manager = new Manager();
        $counter = 0;

        $manager->attach('group:test', new class($counter){
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