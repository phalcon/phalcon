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

namespace Phalcon\Tests\Unit\Mvc\Router;

use Phalcon\Tests\UnitTestCase;
use Phalcon\Tests\Fixtures\Traits\RouterTrait;

final class GetRouteByNameTest extends UnitTestCase
{
    use RouterTrait;

    /**
     * Tests Phalcon\Mvc\Router :: getRouteByName()
     *
     * @author Wojciech Ślawski <jurigag@gmail.com>
     * @since  2018-06-28
     */
    public function testGetRouteByName(): void
    {
        $router = $this->getRouter(false);

        $router
            ->add('/test', ['controller' => 'test', 'action' => 'test'])
            ->setName('test')
        ;
        $router
            ->add('/test2', ['controller' => 'test', 'action' => 'test'])
            ->setName('test2')
        ;
        $router
            ->add('/test3', ['controller' => 'test', 'action' => 'test'])
            ->setName('test3')
        ;

        /**
         * We reverse routes so we first check last added route
         */
        foreach (array_reverse($router->getRoutes()) as $route) {
            $expected = $router->getRouteByName($route->getName());
            $actual   = $route;

            $this->assertSame($expected, $actual);
        }
    }

    /**
     * Tests getting named route
     *
     * @author Andy Gutierrez <andres.gutierrez@phalcon.io>
     * @since  2012-08-27
     */
    public function testGettingNamedRoutes(): void
    {
        $router = $this->getRouter(false);

        $usersFind = $router
            ->add('/api/users/find')
            ->setHttpMethods('GET')
            ->setName('usersFind')
        ;
        $usersAdd  = $router
            ->add('/api/users/add')
            ->setHttpMethods('POST')
            ->setName('usersAdd')
        ;

        $expected = $usersAdd;
        $actual   = $router->getRouteByName('usersAdd');
        $this->assertSame($expected, $actual);

        // second check when the same route goes from name lookup
        $expected = $usersAdd;
        $actual   = $router->getRouteByName('usersAdd');
        $this->assertSame($expected, $actual);
    }
}
