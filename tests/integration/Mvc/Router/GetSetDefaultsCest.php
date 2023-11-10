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
use Phalcon\Mvc\Router;

class GetSetDefaultsCest
{
    /**
     * Tests Phalcon\Mvc\Router :: getDefaults() / setDefaults()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-05-22
     */
    public function mvcRouterGetSetDefaults(IntegrationTester $I)
    {
        $I->wantToTest('Mvc\Router - getDefaults() / setDefaults()');

        $router = new Router();

        $expected = [
            'namespace'  => '',
            'module'     => '',
            'controller' => '',
            'action'     => '',
            'params'     => [],
        ];
        $actual   = $router->getDefaults();
        $I->assertSame($expected, $actual);

        $defaults = [
            'namespace'  => 'Phalcon',
            'module'     => 'front',
            'controller' => 'Login',
            'action'     => 'default',
            'params'     => [1, 2, 3],
        ];

        $router->setDefaults($defaults);

        $expected = $defaults;
        $actual   = $router->getDefaults();
        $I->assertSame($expected, $actual);
    }
}
