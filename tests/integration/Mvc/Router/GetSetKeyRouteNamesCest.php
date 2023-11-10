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
use Phalcon\Tests\Fixtures\Traits\RouterTrait;

/**
 * Class GetSetKeyRouteNamesCest
 */
class GetSetKeyRouteNamesCest
{
    use RouterTrait;

    /**
     * Tests Phalcon\Mvc\Router :: getKeyRouteNames()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function mvcRouterGetKeyRouteNames(IntegrationTester $I)
    {
        $I->wantToTest('Mvc\Router - getKeyRouteNames()');

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
        $I->assertSame($expected, $actual);

        // second check when the same route goes from name lookup
        $expected = [
            'usersFind' => 0,
            'usersAdd'  => 1,
        ];
        $actual   = $router->getKeyRouteNames();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Mvc\Router :: getKeyRouteNames()/setKeyRouteNames()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2023-11-07
     */
    public function mvcRouterGetSetKeyRouteNames(IntegrationTester $I)
    {
        $I->wantToTest('Mvc\Router - getKeyRouteNames()/setKeyRouteNames()');

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

        $actual = $router->getRouteByName('unknown');
        $I->assertFalse($actual);

        $expected = $usersAdd;
        $actual   = $router->getRouteByName('usersAdd');
        $I->assertSame($expected, $actual);

        // second check when the same route goes from name lookup
        $expected = [
            'usersFind' => 0,
            'usersAdd'  => 1,
        ];
        $actual   = $router->getKeyRouteNames();
        $I->assertSame($expected, $actual);

        $names = [
            'usersAdd'  => 0,
            'usersFind' => 1,
        ];
        $router->setKeyRouteNames($names);

        $expected = $names;
        $actual   = $router->getKeyRouteNames();
        $I->assertSame($expected, $actual);
    }
}
