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

use IntegrationTester;
use Phalcon\Mvc\Router\Route;
use Phalcon\Tests\Fixtures\Traits\RouterTrait;

class GetSetKeyRouteIdsCest
{
    use RouterTrait;

    /**
     * Tests Phalcon\Mvc\Router :: getKeyRouteIds()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function mvcRouterGetKeyRouteIds(IntegrationTester $I)
    {
        $I->wantToTest('Mvc\Router - getKeyRouteIds()');

        Route::reset();

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

        $expected = $usersFind;
        $actual   = $router->getRouteById(0);
        $I->assertSame($expected, $actual);

        $expected = [0 => 0];
        $actual   = $router->getKeyRouteIds();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Mvc\Router :: getKeyRouteIds()/setKeyRouteIds()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2023-11-07
     */
    public function mvcRouterGetSetKeyRouteIds(IntegrationTester $I)
    {
        $I->wantToTest('Mvc\Router - getKeyRouteIds()');

        Route::reset();

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

        $expected = $usersFind;
        $actual   = $router->getRouteById(0);
        $I->assertSame($expected, $actual);

        $expected = [0 => 0];
        $actual   = $router->getKeyRouteIds();
        $I->assertSame($expected, $actual);

        $router->setKeyRouteIds([1 => 0]);

        $expected = [1 => 0];
        $actual   = $router->getKeyRouteIds();
        $I->assertSame($expected, $actual);
    }
}
