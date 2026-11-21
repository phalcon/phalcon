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

namespace Phalcon\Tests\Unit\Mvc;

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Binder;
use Phalcon\Mvc\Model\Binder\BindableInterface;
use Phalcon\Mvc\Model\Exceptions\MissingModelClassName;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Url;
use Phalcon\Mvc\Url\Exceptions\RouteNotFound;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php;
use Phalcon\Mvc\View\Exceptions\InvalidEngineRegistration;
use Phalcon\Mvc\View\Simple;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Talon\Talon;
use stdClass;

use function ob_end_clean;
use function ob_get_level;
use function restore_error_handler;
use function set_error_handler;

/**
 * cphalcon uses the PHP weak type rules for an int value that goes to a string
 * target, thus the int becomes a string.
 *
 * @issue https://github.com/phalcon/phalcon/issues/885
 */
final class WeakModeIntValuesTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testMvcDispatcherForwardInt(): void
    {
        $dispatcher = new Dispatcher();
        $dispatcher->forward(['controller' => 7, 'action' => 404]);

        $this->assertSame('7', $dispatcher->getControllerName());
        $this->assertSame('404', $dispatcher->getActionName());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testMvcMicroArrayAccessIntOffset(): void
    {
        $application      = new Micro(new FactoryDefault());
        $application[123] = function () {
            return new stdClass();
        };

        $this->assertTrue(isset($application[123]));
        $this->assertInstanceOf(stdClass::class, $application[123]);
    }

    /**
     * @issue  https://github.com/phalcon/phalcon/issues/899
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testMvcModelBinderBindToHandlerListParams(): void
    {
        $handler = new class () implements BindableInterface {
            public function getModelName(): array | string
            {
                return ['robot' => Model::class];
            }

            public function showAction(Model $robot): void
            {
            }
        };

        $this->expectException(MissingModelClassName::class);
        $this->expectExceptionMessage('You should provide model class name for 0 parameter');

        (new Binder())->bindToHandler($handler, [1], 'weak-mode-list', 'showAction');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testMvcUrlGetEmptyPatternWithHostname(): void
    {
        $router = new Router(false);
        $router->setDI(new FactoryDefault());
        $router->add('', ['controller' => 'home'])
               ->setName('home')
               ->setHostname('example.com')
        ;

        $url = new Url($router);
        $url->setBaseUri('/');

        $this->assertSame('//example.com/', $url->get(['for' => 'home']));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testMvcUrlGetForInt(): void
    {
        $router = new Router(false);
        $router->setDI(new FactoryDefault());
        $router->add('/blog', ['controller' => 'blog'])->setName('123');

        $url = new Url($router);
        $url->setBaseUri('/');

        $this->assertSame('/blog', $url->get(['for' => 123]));

        $this->expectException(RouteNotFound::class);
        $this->expectExceptionMessage("Cannot obtain a route using the name '999'");

        $url->get(['for' => 999]);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testMvcViewPickInt(): void
    {
        $view = new View();
        $view->setViewsDir(Talon::settings()->supportPath('assets/views/'));

        $view->start();
        $view->pick([123]);
        $view->render('simple', 'index');
        $view->finish();

        $this->assertSame('', $view->getContent());

        /**
         * cphalcon gives a warning for an int view name
         */
        set_error_handler(
            function (): bool {
                return true;
            }
        );

        try {
            $view->start();
            $view->pick(123);
            $view->render('simple', 'index');
            $view->finish();
        } finally {
            restore_error_handler();
        }

        $this->assertSame('', $view->getContent());
    }

    /**
     * @issue  https://github.com/phalcon/phalcon/issues/899
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testMvcViewRegisterEnginesListBadEntry(): void
    {
        $view = new View();
        $view->setDI(new FactoryDefault());
        $view->setViewsDir(Talon::settings()->supportPath('assets/views/'));
        $view->registerEngines([123]);

        $this->expectException(InvalidEngineRegistration::class);
        $this->expectExceptionMessage('Invalid template engine registration for extension: 0');

        $view->start();
        try {
            $view->render('simple', 'index');
        } finally {
            $view->finish();
        }
    }

    /**
     * @issue  https://github.com/phalcon/phalcon/issues/899
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testMvcViewSimpleRegisterEnginesList(): void
    {
        $container = new FactoryDefault();
        $view      = new Simple();
        $view->setDI($container);
        $view->setViewsDir(Talon::settings()->supportPath('assets/views/'));

        $engine = new Php($view, $container);
        $view->registerEngines([$engine, '.phtml' => $engine]);

        $this->assertSame('We are here', $view->render('simple/index'));
    }

    /**
     * @issue  https://github.com/phalcon/phalcon/issues/899
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testMvcViewSimpleRegisterEnginesListBadEntry(): void
    {
        $view = new Simple();
        $view->setDI(new FactoryDefault());
        $view->setViewsDir(Talon::settings()->supportPath('assets/views/'));
        $view->registerEngines([123]);

        $this->expectException(InvalidEngineRegistration::class);
        $this->expectExceptionMessage('Invalid template engine registration for extension: 0');

        $level = ob_get_level();
        try {
            $view->render('simple/index');
        } finally {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
        }
    }
}
