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

namespace Phalcon\Tests\Unit\Cli\Router;

use Phalcon\Cli\Router;
use Phalcon\Tests\UnitTestCase;

final class SetDefaultTaskTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Cli\Router :: setDefaultTask()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testCliRouterSetDefaultTask(): void
    {
        $router = new Router();

        $expected = "";
        $actual   = $router->getTaskName();
        $this->assertSame($expected, $actual);

        $router->handle("");

        $expected = "";
        $actual   = $router->getTaskName();
        $this->assertSame($expected, $actual);

        $router->setDefaultTask("test");
        $router->handle("");

        $expected = "test";
        $actual   = $router->getTaskName();
        $this->assertSame($expected, $actual);
    }
}
