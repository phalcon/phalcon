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

class GetModuleNameCest
{
    /**
     * Tests Phalcon\Cli\Router :: getModuleName()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function cliRouterGetModuleName(CliTester $I)
    {
        $I->wantToTest('Cli\Router - getModuleName()');

        $router = new Router();

        $expected = '';
        $actual   = $router->getModuleName();
        $I->assertSame($expected, $actual);

        $router->handle(
            [
                'module' => 'test',
            ]
        );

        $expected = "test";
        $actual   = $router->getModuleName();
        $I->assertSame($expected, $actual);
    }
}
