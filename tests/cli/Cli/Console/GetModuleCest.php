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

namespace Phalcon\Tests\Cli\Cli\Console;

use CliTester;
use Phalcon\Application\Exception;
use Phalcon\Cli\Console as CliConsole;
use Phalcon\Di\FactoryDefault\Cli as DiFactoryDefault;
use Phalcon\Tests\Modules\Backend\Module as BackendModule;
use Phalcon\Tests\Modules\Frontend\Module as FrontendModule;

class GetModuleCest
{
    /**
     * Tests Phalcon\Cli\Console :: getModule()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     *
     * @author Nathan Edwards <https://github.com/npfedwards>
     * @since  2018-12-26
     */
    public function cliConsoleGetModule(CliTester $I)
    {
        $I->wantToTest("Cli\Console - getModule()");

        $console = new CliConsole(new DiFactoryDefault());

        $definition = [
            'frontend' => [
                'className' => FrontendModule::class,
                'path'      => dataDir('fixtures/modules/frontend/Module.php'),
            ],
            'backend'  => [
                'className' => BackendModule::class,
                'path'      => dataDir('fixtures/modules/backend/Module.php'),
            ],
        ];

        $console->registerModules($definition);

        $expected = $definition['frontend'];
        $actual   = $console->getModule('frontend');
        $I->assertSame($expected, $actual);

        $expected = $definition['backend'];
        $actual   = $console->getModule('backend');
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Cli\Console :: getModule() - non-existent
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     *
     * @author Nathan Edwards <https://github.com/npfedwards>
     * @since  2018-12-26
     */
    public function cliConsoleGetModuleNonExistent(CliTester $I)
    {
        $I->wantToTest("Cli\Console - getModule() - non-existent");

        $console = new CliConsole(new DiFactoryDefault());

        $I->expectThrowable(
            new Exception(
                "Module 'foo' is not registered in the application container"
            ),
            function () use ($console) {
                $console->getModule('foo');
            }
        );
    }
}
