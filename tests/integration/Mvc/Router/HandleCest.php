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

namespace Phalcon\Tests\Integration\Mvc\Router;

use Codeception\Example;
use IntegrationTester;
use Phalcon\Di\FactoryDefault;
use Phalcon\Http\Request;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Router\Group;
use Phalcon\Mvc\Router\Route;
use Phalcon\Tests\Fixtures\Traits\RouterTrait;

/**
 * Class HandleCest
 */
class HandleCest
{
    use RouterTrait;

    /**
     * Tests Phalcon\Mvc\Router :: handle()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-10-20
     */
    public function mvcRouterHandle(IntegrationTester $I)
    {
        $I->wantToTest('Mvc\Router - handle()');

        $router = $this->getRouter();

        $router->add(
            '/admin/invoices/list',
            [
                'controller' => 'invoices',
                'action'     => 'list',
            ]
        );

        $router->handle('/admin/invoices/list');

        $expected = 'invoices';
        $actual   = $router->getControllerName();
        $I->assertSame($expected, $actual);

        $expected = 'list';
        $actual   = $router->getActionName();
        $I->assertSame($expected, $actual);

        $expected = [];
        $actual   = $router->getParams();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Mvc\Router :: handle() - with placeholders
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-10-20
     */
    public function mvcRouterHandleWithPlaceholders(IntegrationTester $I)
    {
        $I->wantToTest('Mvc\Router - handle() - with placeholders');

        /**
         * Regular placeholders
         */
        $router = $this->getRouter(false);
        $router->add(
            '/:module/:namespace/:controller/:action/:params/:int',
            [
                'module'     => 1,
                'namespace'  => 2,
                'controller' => 3,
                'action'     => 4,
                'params'     => 5,
                'my-number'  => 6
            ]
        );

        $router->handle('/admin/private/businesses/list/my/123');

        $expected = 'admin';
        $actual   = $router->getModuleName();
        $I->assertSame($expected, $actual);

        $expected = 'private';
        $actual   = $router->getNamespaceName();
        $I->assertSame($expected, $actual);

        $expected = 'businesses';
        $actual   = $router->getControllerName();
        $I->assertSame($expected, $actual);

        $expected = 'list';
        $actual   = $router->getActionName();
        $I->assertSame($expected, $actual);

        $expected = [
            'my',
            'my-number' => '123'
        ];
        $actual   = $router->getParams();
        $I->assertSame($expected, $actual);

        /**
         * Parameters
         */
        $router->add(
            '/admin/{year}/{month}/{day}/{invoiceNo:[0-9]+}',
            [
                'controller' => 'invoices',
                'action'     => 'view',
            ]
        );

        $router->handle('/admin/2020/october/21/456');

        $expected = '';
        $actual   = $router->getModuleName();
        $I->assertSame($expected, $actual);

        $expected = '';
        $actual   = $router->getNamespaceName();
        $I->assertSame($expected, $actual);

        $expected = 'invoices';
        $actual   = $router->getControllerName();
        $I->assertSame($expected, $actual);

        $expected = 'view';
        $actual   = $router->getActionName();
        $I->assertSame($expected, $actual);

        $expected = [
            'year'      => '2020',
            'month'     => 'october',
            'day'       => '21',
            'invoiceNo' => '456',
        ];
        $actual   = $router->getParams();
        $I->assertSame($expected, $actual);

        /**
         * Named parameters
         */
        $router->add(
            '/admin/([0-9]{4})/([0-9]{2})/([0-9]{2})/:params',
            [
                'controller' => 'history',
                'action'     => 'search',
                'year'       => 1, // ([0-9]{4})
                'month'      => 2, // ([0-9]{2})
                'day'        => 3, // ([0-9]{2})
                'params'     => 4, // :params
            ]
        );

        $router->handle('/admin/2020/10/21/456');

        $expected = '';
        $actual   = $router->getModuleName();
        $I->assertSame($expected, $actual);

        $expected = '';
        $actual   = $router->getNamespaceName();
        $I->assertSame($expected, $actual);

        $expected = 'history';
        $actual   = $router->getControllerName();
        $I->assertSame($expected, $actual);

        $expected = 'search';
        $actual   = $router->getActionName();
        $I->assertSame($expected, $actual);

        $expected = [
            '456',
            'year'  => '2020',
            'month' => '10',
            'day'   => '21',
        ];
        $actual   = $router->getParams();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Mvc\Router :: handle() - short syntax
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-10-20
     */
    public function mvcRouterHandleShortSyntax(IntegrationTester $I)
    {
        $I->wantToTest('Mvc\Router - handle() - short syntax');

        $router = $this->getRouter(false);
        $router->add("/about", "About::content");

        $router->handle('/about');

        $expected = '';
        $actual   = $router->getModuleName();
        $I->assertSame($expected, $actual);

        $expected = '';
        $actual   = $router->getNamespaceName();
        $I->assertSame($expected, $actual);

        $expected = 'About';
        $actual   = $router->getControllerName();
        $I->assertSame($expected, $actual);

        $expected = 'content';
        $actual   = $router->getActionName();
        $I->assertSame($expected, $actual);

        $expected = [];
        $actual   = $router->getParams();
        $I->assertSame($expected, $actual);

        $_SERVER['REQUEST_METHOD'] = 'POST';

        $container = new FactoryDefault();
        $container->set('request', new Request());

        $router = new Router(false);
        $router->setDI($container);
        $router->add(
            "/about",
            "About::content",
            ["GET"]
        );

        $router->handle('/about');

        $actual   = $router->getMatchedRoute();
        $I->assertNull($actual);

        $expected = '';
        $actual   = $router->getControllerName();
        $I->assertSame($expected, $actual);

        $expected = '';
        $actual   = $router->getActionName();
        $I->assertSame($expected, $actual);

        $actual   = $router->getParams();
        $I->assertEmpty($actual);

        $router->add(
            "/about",
            "About::content",
            ["POST"],
            Router::POSITION_FIRST
        );

        $router->handle('/about');

        $expected = '';
        $actual   = $router->getModuleName();
        $I->assertSame($expected, $actual);

        $expected = '';
        $actual   = $router->getNamespaceName();
        $I->assertSame($expected, $actual);

        $expected = 'About';
        $actual   = $router->getControllerName();
        $I->assertSame($expected, $actual);

        $expected = 'content';
        $actual   = $router->getActionName();
        $I->assertSame($expected, $actual);

        $expected = [];
        $actual   = $router->getParams();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Mvc\Router :: handle() - numeric
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-10-17
     */
    public function mvcRouterHandleNumeric(IntegrationTester $I)
    {
        $I->wantToTest('Mvc\Router - handle() - numeric');

        $router = $this->getRouter();
        $router->handle('/12/34/56');

        $expected = '';
        $actual   = $router->getModuleName();
        $I->assertSame($expected, $actual);

        $expected = '';
        $actual   = $router->getNamespaceName();
        $I->assertSame($expected, $actual);

        $expected = '12';
        $actual   = $router->getControllerName();
        $I->assertSame($expected, $actual);

        $expected = '34';
        $actual   = $router->getActionName();
        $I->assertSame($expected, $actual);

        $expected = ['56'];
        $actual   = $router->getParams();
        $I->assertSame($expected, $actual);
    }

    /**
     * @dataProvider groupsProvider
     */
    public function mvcRouterHandleGroups(IntegrationTester $I, Example $example)
    {
        Route::reset();

        $router = $this->getRouter(false);

        $blog = new Group(
            [
                'module'     => 'blog',
                'controller' => 'index',
            ]
        );

        $blog->setPrefix('/blog');

        $blog->add(
            '/save',
            [
                'action' => 'save',
            ]
        );

        $blog->add(
            '/edit/{id}',
            [
                'action' => 'edit',
            ]
        );

        $blog->add(
            '/about',
            [
                'controller' => 'about',
                'action'     => 'index',
            ]
        );

        $router->mount($blog);


        $router->handle(
            $example['route']
        );

        $I->assertTrue(
            $router->wasMatched()
        );

        $I->assertSame(
            $example['module'],
            $router->getModuleName()
        );

        $I->assertSame(
            $example['controller'],
            $router->getControllerName()
        );

        $I->assertSame(
            $example['action'],
            $router->getActionName()
        );

        $I->assertSame(
            $blog,
            $router->getMatchedRoute()->getGroup()
        );
    }

    /**
     * @return array[]
     */
    private function groupsProvider(): array
    {
        return [
            [
                'route'      => '/blog/save',
                'module'     => 'blog',
                'controller' => 'index',
                'action'     => 'save',
            ],
            [
                'route'      => '/blog/edit/1',
                'module'     => 'blog',
                'controller' => 'index',
                'action'     => 'edit',
            ],
            [
                'route'      => '/blog/about',
                'module'     => 'blog',
                'controller' => 'about',
                'action'     => 'index',
            ],
        ];
    }
}
