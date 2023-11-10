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

namespace Phalcon\Tests\Integration\Mvc\Router\Group;

use Codeception\Example;
use IntegrationTester;
use Phalcon\Mvc\Router\Group;
use Phalcon\Mvc\Router\Route;
use Phalcon\Tests\Fixtures\Traits\RouterTrait;

/**
 * Class GetSetHostnameCest
 */
class GetSetHostnameCest
{
    use RouterTrait;

    /**
     * Tests Phalcon\Mvc\Router\Group :: getHostname()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function mvcRouterGroupGetHostname(IntegrationTester $I)
    {
        $I->wantToTest('Mvc\Router\Group - getHostname()');

        $group = new Group();

        $actual = $group->getHostname();
        $I->assertNull($actual);

        $hostname = 'https://phalcon.io';
        $group->setHostname($hostname);

        $expected = $hostname;
        $actual   = $group->getHostname();
        $I->assertSame($expected, $actual);
    }

    /**
     * @dataProvider getHostnameRoutes
     */
    public function mvcRouterGroupGetHostnameRouteGroup(IntegrationTester $I, Example $example)
    {
        $actualHost   = $example[0];
        $expectedHost = $example[1];
        $controller   = $example[2];

        Route::reset();

        $router = $this->getRouter(false);

        $router->add(
            '/edit',
            [
                'controller' => 'posts3',
                'action'     => 'edit3',
            ]
        );

        $group = new Group();

        $group->setHostname('my.phalcon.io');

        $group->add(
            '/edit',
            [
                'controller' => 'posts',
                'action'     => 'edit',
            ]
        );

        $router->mount($group);

        $_SERVER['HTTP_HOST'] = $actualHost;

        $router->handle('/edit');

        $I->assertSame(
            $controller,
            $router->getControllerName()
        );

        $I->assertSame(
            $expectedHost,
            $router->getMatchedRoute()->getHostname()
        );
    }

    private function getHostnameRoutes(): array
    {
        return [
            [
                'localhost',
                null,
                'posts3',
            ],
            [
                'my.phalcon.io',
                'my.phalcon.io',
                'posts',
            ],
            [
                null,
                null,
                'posts3',
            ],
        ];
    }

    /**
     * @dataProvider getHostnameRoutesRegex
     */
    public function mvcRouterGroupGetHostnameRegexRouteGroup(IntegrationTester $I, Example $example)
    {
        $actualHost   = $example[0];
        $expectedHost = $example[1];
        $controller   = $example[2];

        Route::reset();

        $router = $this->getRouter(false);

        $router->add(
            '/edit',
            [
                'controller' => 'posts3',
                'action'     => 'edit3',
            ]
        );

        $group = new Group();

        $group->setHostname('([a-z]+).phalcon.io');

        $group->add(
            '/edit',
            [
                'controller' => 'posts',
                'action'     => 'edit',
            ]
        );

        $router->mount($group);

        $_SERVER['HTTP_HOST'] = $actualHost;

        $router->handle('/edit');

        $I->assertSame(
            $controller,
            $router->getControllerName()
        );

        $I->assertSame(
            $expectedHost,
            $router->getMatchedRoute()->getHostname()
        );
    }

    /**
     * @return array
     */
    private function getHostnameRoutesRegex(): array
    {
        return [
            [
                'localhost',
                null,
                'posts3',
            ],
            [
                'my.phalcon.io',
                '([a-z]+).phalcon.io',
                'posts',
            ],
            [
                null,
                null,
                'posts3',
            ],
        ];
    }
}
