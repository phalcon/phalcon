<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Cli;

use Phalcon\Cli\Console;
use Phalcon\Cli\Console\Exceptions\ModuleDefinitionPathNotFound;
use Phalcon\Cli\Router;
use Phalcon\Di\FactoryDefault\Cli as CliFactoryDefault;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;

/**
 * cphalcon uses the PHP weak type rules for an int value that goes to a string
 * target, thus the int becomes a string.
 *
 * @issue https://github.com/phalcon/phalcon/issues/885
 */
final class WeakModeIntValuesTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testCliConsoleModulePathInt(): void
    {
        $this->expectException(ModuleDefinitionPathNotFound::class);
        $this->expectExceptionMessage("Module definition path '123' does not exist");

        $console = new Console(new CliFactoryDefault());
        $console->registerModules(
            [
                'main' => [
                    'path'      => 123,
                    'className' => 'Unknown',
                ],
            ]
        );
        $console->handle(['module' => 'main']);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testCliRouterSetDefaultsInt(): void
    {
        $router = new Router();
        $router->setDefaults(['module' => 1, 'task' => 2, 'action' => 3]);
        $router->handle([]);

        $this->assertSame('1', $router->getModuleName());
        $this->assertSame('2', $router->getTaskName());
        $this->assertSame('3', $router->getActionName());
    }
}
