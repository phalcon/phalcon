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

namespace Phalcon\Tests\Database\Mvc\Application;

use Phalcon\Di\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Application\Exception as ApplicationException;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\View;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Support\Modules\Backend\Module;

use function dataDir;

final class RegisterModulesTest extends AbstractDatabaseTestCase
{
    /**
     * @return void
     */
    public function tearDown(): void
    {
        Di::reset();
    }

    /**
     * Tests Phalcon\Mvc\Application :: registerModules() - standard definition
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-05-15
     *
     * @group mysql
     */
    public function testMvcApplicationRegisterModulesDefinition(): void
    {
        Di::reset();

        $di = new FactoryDefault();

        $di->set(
            'router',
            function () {
                $router = new Router(false);

                $router->add(
                    '/index',
                    [
                        'controller' => 'index',
                        'module'     => 'frontend',
                        'namespace'  => 'Phalcon\Tests\Support\Modules\Frontend\Controllers',
                    ]
                );

                return $router;
            }
        );

        $application = new Application();

        $application->registerModules(
            [
                'frontend' => [
                    'path'      => supportDir('Modules/Frontend/Module.php'),
                    'className' => \Phalcon\Tests\Support\Modules\Frontend\Module::class,
                ],
                'backend'  => [
                    'path'      => supportDir('Modules/Backend/Module.php'),
                    'className' => Module::class,
                ],
            ]
        );

        $application->setDI($di);

        $response = $application->handle('/index');

        $expected = '<html>here</html>' . PHP_EOL;
        $actual   = $response->getContent();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Mvc\Application :: registerModules() - closure
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-05-15
     *
     * @group mysql
     */
    public function testMvcApplicationRegisterModulesClosure(): void
    {
        Di::reset();

        $di = new FactoryDefault();

        $di->set(
            'router',
            function () {
                $router = new Router(false);

                $router->add(
                    '/index',
                    [
                        'controller' => 'index',
                        'module'     => 'frontend',
                        'namespace'  => 'Phalcon\Tests\Support\Modules\Frontend\Controllers',
                    ]
                );

                $router->add(
                    '/login',
                    [
                        'controller' => 'login',
                        'module'     => 'backend',
                        'namespace'  => 'Phalcon\Tests\Support\Modules\Backend\Controllers',
                    ]
                );

                return $router;
            }
        );

        $application = new Application();
        $view        = new View();

        $application->registerModules(
            [
                'frontend' => function (DiInterface $di) use ($view) {
                    $di->set(
                        'view',
                        function () use ($view) {
                            $view->setViewsDir(
                                supportDir('Modules/Frontend/views/')
                            );

                            return $view;
                        }
                    );
                },
                'backend'  => function (DiInterface $di) use ($view) {
                    $di->set(
                        'view',
                        function () use ($view) {
                            $view->setViewsDir(
                                supportDir('Modules/Backend/views/')
                            );

                            return $view;
                        }
                    );
                },
            ]
        );

        $application->setDI($di);

        $response = $application->handle('/login');

        $expected = '<html>here</html>' . PHP_EOL;
        $actual   = $response->getContent();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Mvc\Application :: registerModules() - bad path throws exception
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-05-15
     *
     * @group mysql
     */
    public function testMvcApplicationRegisterModulesBadPathThrowsAnException(): void
    {
        Di::reset();

        $di = new FactoryDefault();

        $di->set(
            'router',
            function () {
                $router = new Router(false);

                $router->add(
                    '/index',
                    [
                        'controller' => 'index',
                        'module'     => 'frontend',
                        'namespace'  => 'Phalcon\Tests\Support\Modules\Frontend\Controllers',
                    ]
                );

                return $router;
            }
        );

        $application = new Application();

        $application->registerModules(
            [
                'frontend' => [
                    'path'      => dataDir('not-a-real-file.php'),
                    'className' => \Phalcon\Tests\Support\Modules\Frontend\Module::class,
                ],
            ]
        );

        $application->setDI($di);

        $this->expectException(ApplicationException::class);
        $this->expectExceptionMessage(
            "Module definition path '" . dataDir('not-a-real-file.php') . "' does not exist"
        );

        $application->handle('/index');
    }
}
