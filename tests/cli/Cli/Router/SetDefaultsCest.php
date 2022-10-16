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

class SetDefaultsCest
{
    /**
     * Tests Phalcon\Cli\Router :: setDefaults()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function cliRouterSetDefaults(CliTester $I)
    {
        $I->wantToTest('Cli\Router - setDefaults()');

        $router = new Router(false);
        $router->handle();

        $expected = "";
        $actual   = $router->getModuleName();
        $I->assertSame($expected, $actual);

        $expected = "";
        $actual   = $router->getTaskName();
        $I->assertSame($expected, $actual);

        $expected = "";
        $actual   = $router->getActionName();
        $I->assertSame($expected, $actual);

        $defaults = [
            'module' => "testModule",
            'task'   => "testTask",
            'action' => "testAction",
        ];
        $router->setDefaults($defaults);
        $router->handle();

        $expected = $defaults["module"];
        $actual   = $router->getModuleName();
        $I->assertSame($expected, $actual);

        $expected = $defaults["task"];
        $actual   = $router->getTaskName();
        $I->assertSame($expected, $actual);

        $expected = $defaults["action"];
        $actual   = $router->getActionName();
        $I->assertSame($expected, $actual);
    }
}
