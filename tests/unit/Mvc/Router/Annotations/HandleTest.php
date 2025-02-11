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

namespace Phalcon\Tests\Unit\Mvc\Router\Annotations;

use Phalcon\Annotations\AdapterFactory;
use Phalcon\Events\Exception as EventsException;
use Phalcon\Mvc\Router\Annotations;
use Phalcon\Mvc\Router\Exception as RouterException;
use Phalcon\Mvc\Router\Route;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\AbstractUnitTestCase;

use function dataDir;

final class HandleTest extends AbstractUnitTestCase
{
    use DiTrait;

    /**
     * @return array[]
     */
    public static function getRouteExamples(): array
    {
        return [
            [
                '/products/save',
                'PUT',
                'products',
                'save',
                [],
            ],
            [
                '/products/save',
                'POST',
                'products',
                'save',
                [],
            ],
            [
                '/products/edit/100',
                'GET',
                'products',
                'edit',
                [
                    'id' => '100',
                ],
            ],
            [
                '/products',
                'GET',
                'products',
                'index',
                [],
            ],
            [
                '/invoices/edit/100',
                'GET',
                'invoices',
                'edit',
                [
                    'id' => '100',
                ],
            ],
            [
                '/invoices',
                'GET',
                'invoices',
                'index',
                [],
            ],
            [
                '/invoices/save',
                'PUT',
                'invoices',
                'save',
                [],
            ],
            [
                '/about/team',
                'GET',
                'about',
                'team',
                [],
            ],
            [
                '/about/team',
                'POST',
                'about',
                'teampost',
                [],
            ],
            [
                '/',
                'GET',
                'main',
                'index',
                [],
            ],
        ];
    }

    public function setUp(): void
    {
        $this->newDi();
        $this->setDiService('request');
//        $this->setDiService('annotations');

        $factory = new AdapterFactory(new SerializerFactory());
        $adapter = $factory->newInstance('memory');

        $this->container->setShared(
            'annotations',
            new \Phalcon\Annotations\Annotations($adapter)
        );
    }

    /**
     * Tests Phalcon\Mvc\Router\Annotations :: handle()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testMvcRouterAnnotationsHandle(): void
    {
        $router = new Annotations(false);
        $router->setDI($this->container);

        $router->addResource("Phalcon\Tests\Controllers\Invoices", '/');
        $router->addResource("Phalcon\Tests\Controllers\Products", '/products');
        $router->addResource("Phalcon\Tests\Controllers\About", '/about');

        $router->handle('/products');

        $expected = 6;
        $actual   = $router->getRoutes();
        $this->assertCount($expected, $actual);

        $router = new Annotations(false);
        $router->setDI($this->container);

        $router->addResource("Phalcon\Tests\Controllers\Invoices", '/');
        $router->addResource("Phalcon\Tests\Controllers\Products", '/products');
        $router->addResource("Phalcon\Tests\Controllers\About", '/about');

        $router->handle('/about');

        $expected = 5;
        $actual   = $router->getRoutes();
        $this->assertCount($expected, $actual);
    }

    /**
     * @dataProvider getRouteExamples
     *
     * @return void
     * @throws EventsException
     * @throws RouterException
     */
    public function testMvcRouterAnnotationsHandleFullResources(
        string $uri,
        string $method,
        string $controller,
        string $action,
        array $params
    ): void {
        $router = new Annotations(false);
        $router->setDI($this->container);

        $router->addResource("Phalcon\Tests\Controllers\Invoices");
        $router->addResource("Phalcon\Tests\Controllers\Products");
        $router->addResource("Phalcon\Tests\Controllers\About");
        $router->addResource("Phalcon\Tests\Controllers\Main");

        $router->handle('/');

        $expected = 9;
        $actual   = $router->getRoutes();
        $this->assertCount($expected, $actual);

        $route = $router->getRouteByName('save-invoice');
        $this->assertTrue(is_object($route));

        $this->assertInstanceOf(Route::class, $route);

        $route = $router->getRouteByName('save-product');
        $this->assertTrue(is_object($route));

        $this->assertInstanceOf(Route::class, $route);

        $_SERVER['REQUEST_METHOD'] = $method;
        $router->handle($uri);

        $expected = $controller;
        $actual   = $router->getControllerName();
        $this->assertSame($expected, $actual);

        $expected = $action;
        $actual   = $router->getActionName();
        $this->assertSame($expected, $actual);

        $expected = $params;
        $actual   = $router->getParams();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     * @throws EventsException
     * @throws RouterException
     */
    public function testMvcRouterAnnotationsHandleNamespaced(): void
    {
        require_once dataDir('fixtures/controllers/NamespacedAnnotationController.php');

        $router = new Annotations(false);
        $router->setDI($this->container);

        $router->setDefaultNamespace('MyNamespace\\Controllers');
        $router->addResource('NamespacedAnnotation', '/namespaced');

        $router->handle('/namespaced');

        $expected = 1;
        $actual   = $router->getRoutes();
        $this->assertCount($expected, $actual);


        $router = new Annotations(false);
        $router->setDI($this->container);

        $router->addResource(
            'MyNamespace\\Controllers\\NamespacedAnnotation',
            '/namespaced'
        );

        $router->handle('/namespaced/');

        $expected = 1;
        $actual   = $router->getRoutes();
        $this->assertCount($expected, $actual);
    }
}
