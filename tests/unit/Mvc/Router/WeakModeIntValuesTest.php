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

namespace Phalcon\Tests\Unit\Mvc\Router;

use Exception;
use Phalcon\Annotations\Adapter\Memory;
use Phalcon\Annotations\AttributesReader;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Router\Annotations;
use Phalcon\Mvc\Router\Group;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Tests\Support\Traits\DiTrait;
use PHPUnit\Framework\Attributes\BackupGlobals;

/**
 * cphalcon uses the PHP weak type rules for an int value that goes to a string
 * target, thus the int becomes a string.
 *
 * @issue https://github.com/phalcon/phalcon/issues/885
 */
#[BackupGlobals(true)]
final class WeakModeIntValuesTest extends AbstractUnitTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->newDi();
        $this->setDiService('request');
        $this->setDiService('annotations');

        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testMvcRouterAnnotationsGetTrue(): void
    {
        $router = $this->newAnnotationsRouter();
        $router->addResource('WeakTypesGetTrue');
        $router->handle('/');

        $this->assertSame('1', $router->getRoutes()[0]->getPattern());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testMvcRouterAnnotationsPreformatCallbackInt(): void
    {
        $router = $this->newAnnotationsRouter();
        $router->setActionPreformatCallback(
            function () {
                return 123;
            }
        );
        $router->addResource('WeakTypesPreformat');
        $router->handle('/cb');

        $expected = [
            'namespace'  => 'Phalcon\Tests\Support\Controllers',
            'controller' => 'weak_types_preformat',
            'action'     => '123',
        ];
        $this->assertSame($expected, $router->getRoutes()[0]->getPaths());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testMvcRouterAnnotationsRoutePrefixInt(): void
    {
        $router = $this->newAnnotationsRouter();
        $router->addResource('WeakTypesPrefixInt');
        $router->handle('/');

        $this->assertSame('2024', $router->getRoutes()[0]->getPattern());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testMvcRouterAnnotationsRoutePrefixIntAttribute(): void
    {
        $annotations = new Memory();
        $annotations->setReader(new AttributesReader());
        $this->container->set('annotations', $annotations, true);

        $router = $this->newAnnotationsRouter();
        $router->addResource('WeakTypesPrefixInt');
        $router->handle('/');

        $this->assertSame('2024', $router->getRoutes()[0]->getPattern());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testMvcRouterGroupSetPathsInt(): void
    {
        $group = new Group();
        $group->setPaths(123);

        $expected = [
            'controller' => 'A',
            'action'     => 'b',
        ];
        $this->assertSame($expected, $group->add('/a', 'A::b')->getPaths());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testMvcRouterHttpMethodsInt(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The argument is not initialized or iterable()');

        $router = new Router(false);
        $router->setDI($this->container);
        $router->add('/a', 'A::b', 123);
        $router->handle('/a');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testMvcRouterLoadFromConfigPatternInt(): void
    {
        $router = new Router(false);
        $router->setDI($this->container);
        $router->loadFromConfig(
            [
                'routes' => [
                    [
                        'pattern' => 2024,
                        'paths'   => 'Index::index',
                    ],
                ],
                'groups' => [
                    [
                        'prefix' => '/g',
                        'routes' => [
                            [
                                'pattern' => 2024,
                                'paths'   => 'Index::index',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $routes = $router->getRoutes();
        $this->assertSame('2024', $routes[0]->getPattern());
        $this->assertSame('/g2024', $routes[1]->getPattern());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testMvcRouterNumericStaticPattern(): void
    {
        $router = new Router(false);
        $router->setDI($this->container);
        $router->add('2024', ['controller' => 'a']);
        $router->add('/{id:[0-9]+}', ['controller' => 'b']);
        $router->handle('/1');

        $this->assertSame('b', $router->getControllerName());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testMvcRouterPathsPartInt(): void
    {
        $router = new Router(false);
        $router->setDI($this->container);
        $router->add('/{controller}/{action}')->convert(
            'action',
            function () {
                return 5;
            }
        );
        $router->add('/m', ['module' => 1, 'controller' => 'a']);

        $router->handle('/x/y');
        $this->assertSame('5', $router->getActionName());

        $router->handle('/m');
        $this->assertSame('1', $router->getModuleName());
    }

    private function newAnnotationsRouter(): Annotations
    {
        $router = new Annotations(false);
        $router->setDI($this->container);
        $router->setDefaultNamespace('Phalcon\Tests\Support\Controllers');

        return $router;
    }
}
