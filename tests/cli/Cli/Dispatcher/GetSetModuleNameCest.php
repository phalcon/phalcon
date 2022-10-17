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

namespace Phalcon\Tests\Cli\Cli\Dispatcher;

use CliTester;
use Phalcon\Cli\Dispatcher;

/**
 * Class GetSetModuleNameCest
 */
class GetSetModuleNameCest
{
    /**
     * Tests Phalcon\Cli\Dispatcher - getModuleName() / setModuleName()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function cliDispatcherGetModuleName(CliTester $I)
    {
        $I->wantToTest('Cli\Dispatcher - getModuleName() / setModuleName()');

        $dispatcher = new Dispatcher();

        $expected = '';
        $actual   = $dispatcher->getModuleName();
        $I->assertSame($expected, $actual);

        $moduleName = "Phalcon";
        $dispatcher->setModuleName($moduleName);

        $expected = $moduleName;
        $actual   = $dispatcher->getModuleName();
        $I->assertSame($expected, $actual);
    }
}
