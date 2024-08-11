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

namespace Phalcon\Tests\Unit\Mvc\Application;

use Exception;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\View;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\AbstractUnitTestCase;

class HandleTest extends AbstractUnitTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Mvc\Application :: handle() - exception handling
     * using Dispatcher and Events\Manager
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-10-17
     */
    public function dispatcherException(): void
    {
        $this->markTestSkipped('TODO: Check this');
        $this->wantTo('Phalcon\Mvc\Application :: handle() - exception handling');

        $this->setNewFactoryDefault();

        $this->container->set(
            'view',
            function () {
                $view = new View();

                $view->setViewsDir(
                    dataDir('fixtures/views/simple/')
                );

                return $view;
            },
            true
        );

        $eventsManager = $this->container->getEventsManager();

        $this->container->set(
            'dispatcher',
            function () use ($eventsManager) {
                $dispatcher = new Dispatcher();
                $dispatcher->setDefaultNamespace(
                    'Phalcon\Tests\Controllers'
                );

                $eventsManager->attach(
                    'dispatch:beforeException',
                    function ($event, $dispatcher, $exception) {
                        $dispatcher->setReturnedValue(
                            'whoops: ' . $exception->getMessage()
                        );

                        return false;
                    }
                );

                $dispatcher->setEventsManager($eventsManager);

                return $dispatcher;
            }
        );

        $application = new Application();
        $application->setDI($this->container);

        $response = $application->handle('/exception');

        $this->assertEquals(
            'whoops: whups bad controller',
            $response->getContent()
        );
    }

    /**
     * Tests Phalcon\Mvc\Application :: handle() - exception handling
     * with forwarding using Dispatcher and Events\Manager
     *
     * @see    https://github.com/phalcon/cphalcon/issues/15117
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-10-17
     */
    public function dispatcherExceptionForward(): void
    {
        $this->setNewFactoryDefault();

        $this->container->set(
            'view',
            function () {
                $view = new View();

                $view->setViewsDir(
                    dataDir('fixtures/views/simple/')
                );

                return $view;
            },
            true
        );

        $eventsManager = $this->container->getShared('eventsManager');

        $this->container->set(
            'dispatcher',
            function () use ($eventsManager) {
                $dispatcher = new Dispatcher();
                $dispatcher->setDefaultNamespace(
                    'Phalcon\Tests\Controllers'
                );

                $eventsManager->attach(
                    'dispatch:beforeException',
                    function ($event, $dispatcher, $exception) {
                        switch ($exception->getCode()) {
                            case Dispatcher\Exception::EXCEPTION_HANDLER_NOT_FOUND:
                            case Dispatcher\Exception::EXCEPTION_ACTION_NOT_FOUND:
                                $dispatcher->forward([
                                    'controller' => 'init',
                                    'action'     => 'index',
                                ]);

                                return false;
                        }
                    }
                );

                $dispatcher->setEventsManager($eventsManager);

                return $dispatcher;
            }
        );

        $application = new Application();
        $application->setDI($this->container);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Initialize called');

        $application->handle('/not-found');
    }

    /**
     * Tests Phalcon\Mvc\Application :: handle() - single module
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-05-01
     */
    public function testMvcApplicationHandleSingleModule(): void
    {
        $this->markTestSkipped('TODO: Check this');
        $this->wantTo('Phalcon\Mvc\Application :: handle() - single module');

        $this->setNewFactoryDefault();

        $this->container->set(
            'view',
            function () {
                $view = new View();

                $view->setViewsDir(
                    dataDir('fixtures/views/simple/')
                );

                return $view;
            },
            true
        );

        $this->container->set(
            'dispatcher',
            function () {
                $dispatcher = new Dispatcher();
                $dispatcher->setDefaultNamespace(
                    'Phalcon\Tests\Controllers'
                );

                return $dispatcher;
            }
        );

        $application = new Application();
        $application->setDI($this->container);

        $response = $application->handle('/micro');

        $expected = 'We are here';
        $actual   = $response->getContent();
        $this->assertSame($expected, $actual);
    }
}
