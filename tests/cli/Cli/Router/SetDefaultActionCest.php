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

namespace Phalcon\Tests\Cli\Cli\Router;

use CliTester;
use Phalcon\Cli\Router;

class SetDefaultActionCest
{
    /**
     * Tests Phalcon\Cli\Router :: setDefaultAction()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function cliRouterSetDefaultAction(CliTester $I)
    {
        $I->wantToTest('Cli\Router - setDefaultAction()');

        $router = new Router(false);

        $expected = "";
        $actual   = $router->getActionName();
        $I->assertSame($expected, $actual);

        $router->handle("");

        $expected = "";
        $actual   = $router->getActionName();
        $I->assertSame($expected, $actual);

        $router->setDefaultAction("test");
        $router->handle("");

        $expected = "test";
        $actual   = $router->getActionName();
        $I->assertSame($expected, $actual);
    }
}
