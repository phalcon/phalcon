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
use Phalcon\Tests\Fixtures\Listener\TwoListener;
use Phalcon\Tests\AbstractUnitTestCase;

final class AttachTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Events\Manager :: attach() - by name after detatch all
     *
     * @return void
     *
     * @author @author Kamil Skowron <kamil@hedonsoftware.com>
     * @since  2020-09-09
     */
    public function testEventsManagerAttachByNameAfterDetatchAll(): void
    {
        $first         = new OneListener();
        $second        = new TwoListener();
        $component     = new ComponentOne();
        $eventsManager = new Manager();

        $component->setEventsManager($eventsManager);

        $eventsManager->attach('log', $first);
        $logListeners = $component->getEventsManager()
                                  ->getListeners('log')
        ;
        $this->assertCount(1, $logListeners);

        $expected = OneListener::class;
        $actual   = $logListeners[0];
        $this->assertInstanceOf($expected, $actual);

        $component->getEventsManager()
                  ->attach('log', $second)
        ;
        $logListeners = $component->getEventsManager()
                                  ->getListeners('log')
        ;
        $this->assertCount(2, $logListeners);

        $expected = OneListener::class;
        $actual   = $logListeners[0];
        $this->assertInstanceOf($expected, $actual);

        $expected = TwoListener::class;
        $actual   = $logListeners[1];
        $this->assertInstanceOf($expected, $actual);

        $component->getEventsManager()
                  ->detachAll('log')
        ;
        $logListeners = $component->getEventsManager()
                                  ->getListeners('log')
        ;
        $this->assertEmpty($logListeners);

        $component->getEventsManager()
                  ->attach('log', $second)
        ;
        $logListeners = $component->getEventsManager()
                                  ->getListeners('log')
        ;
        $this->assertCount(1, $logListeners);

        $expected = TwoListener::class;
        $actual   = $logListeners[0];
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * Tests Phalcon\Events\Manager :: attach() - exception
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEventsManagerAttachException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Event handler must be an Object or Callable');

        $manager = new Manager();
        $manager->attach('test:detachable', false);
    }
}
