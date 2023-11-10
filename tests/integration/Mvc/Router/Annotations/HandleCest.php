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

namespace Phalcon\Tests\Integration\Mvc\Router\Annotations;

use Codeception\Example;
use IntegrationTester;
use Phalcon\Events\Exception as EventsException;
use Phalcon\Mvc\Router\Annotations;
use Phalcon\Mvc\Router\Exception as RouterException;
use Phalcon\Mvc\Router\Route;
use Phalcon\Tests\Fixtures\Traits\DiTrait;

use function dataDir;

/**
 * Class HandleCest
 */
class HandleCest
{
    use DiTrait;

    public function _before(IntegrationTester $I)
    {
        $this->newDi();
        $this->setDiService('request');
        $this->setDiService('annotations');
    }

    /**
     * Tests Phalcon\Mvc\Router\Annotations :: handle()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function mvcRouterAnnotationsHandle(IntegrationTester $I)
    {
        $I->wantToTest('Mvc\Router\Annotations - handle()');

        $router = new Annotations(false);
        $router->setDI($this->container);

        $router->addResource("Phalcon\Tests\Controllers\Invoices", '/');
        $router->addResource("Phalcon\Tests\Controllers\Products", '/products');
        $router->addResource("Phalcon\Tests\Controllers\About", '/about');

        $router->handle('/products');

        $expected = 6;
        $actual   = $router->getRoutes();
        $I->assertCount($expected, $actual);

        $router = new Annotations(false);
        $router->setDI($this->container);

        $router->addResource("Phalcon\Tests\Controllers\Invoices", '/');
        $router->addResource("Phalcon\Tests\Controllers\Products", '/products');
        $router->addResource("Phalcon\Tests\Controllers\About", '/about');

        $router->handle('/about');

        $expected = 5;
        $actual   = $router->getRoutes();
        $I->assertCount($expected, $actual);
    }

    /**
     * @param IntegrationTester $I
     *
     * @return void
     * @throws EventsException
     * @throws RouterException
     */
    public function mvcRouterAnnotationsHandleNamespaced(IntegrationTester $I)
    {
        $I->wantToTest('Mvc\Router\Annotations - handle() - namespaced');
        require_once dataDir('fixtures/controllers/NamespacedAnnotationController.php');

        $router = new Annotations(false);
        $router->setDI($this->container);

        $router->setDefaultNamespace('MyNamespace\\Controllers');
        $router->addResource('NamespacedAnnotation', '/namespaced');

        $router->handle('/namespaced');

        $expected = 1;
        $actual   = $router->getRoutes();
        $I->assertCount($expected, $actual);


        $router = new Annotations(false);
        $router->setDI($this->container);

        $router->addResource(
            'MyNamespace\\Controllers\\NamespacedAnnotation',
            '/namespaced'
        );

        $router->handle('/namespaced/');

        $expected = 1;
        $actual   = $router->getRoutes();
        $I->assertCount($expected, $actual);
    }

    /**
     * @dataProvider getRouteExamples
     *
     * @param IntegrationTester $I
     * @param Example           $example
     *
     * @return void
     * @throws EventsException
     * @throws RouterException
     */
    public function mvcRouterAnnotationsHandleFullResources(IntegrationTester $I, Example $example)
    {
        $uri        = $example['uri'];
        $method     = $example['method'];
        $controller = $example['controller'];
        $action     = $example['action'];
        $params     = $example['params'];

        $router = new Annotations(false);
        $router->setDI($this->container);

        $router->addResource("Phalcon\Tests\Controllers\Invoices");
        $router->addResource("Phalcon\Tests\Controllers\Products");
        $router->addResource("Phalcon\Tests\Controllers\About");
        $router->addResource("Phalcon\Tests\Controllers\Main");

        $router->handle('/');

        $expected = 9;
        $actual   = $router->getRoutes();
        $I->assertCount($expected, $actual);

        $route = $router->getRouteByName('save-invoice');
        $I->assertTrue(is_object($route));

        $I->assertInstanceOf(Route::class, $route);

        $route = $router->getRouteByName('save-product');
        $I->assertTrue(is_object($route));

        $I->assertInstanceOf(Route::class, $route);

        $_SERVER['REQUEST_METHOD'] = $method;
        $router->handle($uri);

        $expected = $controller;
        $actual   = $router->getControllerName();
        $I->assertSame($expected, $actual);

        $expected = $action;
        $actual   = $router->getActionName();
        $I->assertSame($expected, $actual);

        $expected = $params;
        $actual   = $router->getParams();
        $I->assertSame($expected, $actual);
    }

    /**
     * @return array[]
     */
    private function getRouteExamples(): array
    {
        return [
            [
                'uri'        => '/products/save',
                'method'     => 'PUT',
                'controller' => 'products',
                'action'     => 'save',
                'params'     => [],
            ],
            [
                'uri'        => '/products/save',
                'method'     => 'POST',
                'controller' => 'products',
                'action'     => 'save',
                'params'     => [],
            ],
            [
                'uri'        => '/products/edit/100',
                'method'     => 'GET',
                'controller' => 'products',
                'action'     => 'edit',
                'params'     => [
                    'id' => '100',
                ],
            ],
            [
                'uri'        => '/products',
                'method'     => 'GET',
                'controller' => 'products',
                'action'     => 'index',
                'params'     => [],
            ],
            [
                'uri'        => '/invoices/edit/100',
                'method'     => 'GET',
                'controller' => 'invoices',
                'action'     => 'edit',
                'params'     => [
                    'id' => '100',
                ],
            ],
            [
                'uri'        => '/invoices',
                'method'     => 'GET',
                'controller' => 'invoices',
                'action'     => 'index',
                'params'     => [],
            ],
            [
                'uri'        => '/invoices/save',
                'method'     => 'PUT',
                'controller' => 'invoices',
                'action'     => 'save',
                'params'     => [],
            ],
            [
                'uri'        => '/about/team',
                'method'     => 'GET',
                'controller' => 'about',
                'action'     => 'team',
                'params'     => [],
            ],
            [
                'uri'        => '/about/team',
                'method'     => 'POST',
                'controller' => 'about',
                'action'     => 'teampost',
                'params'     => [],
            ],
            [
                'uri'        => '/',
                'method'     => 'GET',
                'controller' => 'main',
                'action'     => 'index',
                'params'     => [],
            ],
        ];
    }
}
