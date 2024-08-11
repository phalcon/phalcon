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

namespace Phalcon\Tests\Unit\Events\Manager;

use Phalcon\Events\Exception;
use Phalcon\Events\Manager;
use Phalcon\Tests\Fixtures\Events\ComponentOne;
use Phalcon\Tests\Fixtures\Listener\OneListener;
use Phalcon\Tests\Fixtures\Listener\ThreeListener;
use Phalcon\Tests\Fixtures\Listener\TwoListener;
use Phalcon\Tests\AbstractUnitTestCase;
use stdClass;

final class FireTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Events\Manager :: fire()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEventsManagerFire(): void
    {
        $manager = new Manager();
        $one     = new OneListener();
        $two     = new TwoListener();
        $three   = new ThreeListener();

        $manager->enablePriorities(false);
        $manager->collectResponses(true);

        $manager->attach('ab', $three, 10);
        $manager->attach('ab', $two, 20);
        $manager->attach('ab:beforeAction', $one, 30);

        $component = new ComponentOne();
        $component->setEventsManager($manager);

        $component->doAction();

        $expected = ['three', 'two', 'one'];
        $actual   = $component->getEventsManager()
                              ->getResponses()
        ;
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Events\Manager :: fire() - exception
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEventsManagerFireException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid event type unknown');

        $manager = new Manager();
        $manager->attach(
            'someEvent',
            function () {
                return true;
            }
        );

        $manager->fire('unknown', new stdClass());
    }

    /**
     * Tests Phalcon\Events\Manager :: fire() - no events
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEventsManagerFireNoEvents(): void
    {
        $manager = new Manager();
        $actual  = $manager->fire('someEvent', new stdClass());
        $this->assertNull($actual);
    }

    /**
     * Tests Phalcon\Events\Manager :: fire() - with priorities
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEventsManagerFireWithPriorities(): void
    {
        $manager = new Manager();
        $one     = new OneListener();
        $two     = new TwoListener();
        $three   = new ThreeListener();

        $manager->enablePriorities(true);
        $manager->collectResponses(true);

        $manager->attach('ab', $three, 10);
        $manager->attach('ab', $two, 20);
        $manager->attach('ab', $one, 30);

        $component = new ComponentOne();
        $component->setEventsManager($manager);

        $component->doAction();

        $expected = ['one', 'two', 'three'];
        $actual   = $component->getEventsManager()
                              ->getResponses()
        ;
        $this->assertSame($expected, $actual);
    }
}
