<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Mvc\Controller;

use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Controller;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Tests\Support\Controllers\ViewRequestController;
use Phalcon\Tests\Support\Traits\DiTrait;

class GetEventsManagerTest extends AbstractUnitTestCase
{
    use DiTrait;

    /**
     * @author Cameron Hall <me@chall.id.au>
     * @since  2019-10-03
     */
    public function testMvcControllerGetEventsManager(): void
    {
        $di = $this->newService('factoryDefault');

        $controller = new ViewRequestController();

        $controller->setDI($di);

        $this->assertInstanceOf(
            EventsManager::class,
            $controller->eventsManager
        );
    }

    /**
     * Inside the controller, $this->eventsManager and getEventsManager()
     * return the "eventsManager" service of the container, as in cphalcon.
     *
     * @issue  https://github.com/phalcon/phalcon/issues/835
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-18
     */
    public function testMvcControllerGetEventsManagerInsideController(): void
    {
        $di = $this->newService('factoryDefault');

        $controller = new class () extends Controller {
            public function readEventsManager(): mixed
            {
                return $this->eventsManager;
            }
        };

        $controller->setDI($di);

        $expected = $di->getShared('eventsManager');
        $this->assertSame($expected, $controller->readEventsManager());
        $this->assertSame($expected, $controller->getEventsManager());
    }
}
