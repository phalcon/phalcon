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
use Phalcon\Tests\AbstractUnitTestCase;

final class GetTaskNameTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Cli\Router :: getTaskName()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testCliRouterGetTaskName(): void
    {
        $router = new Router();

        $expected = '';
        $actual   = $router->getTaskName();
        $this->assertSame($expected, $actual);

        $router->handle("task action param1 param2");

        $expected = "task";
        $actual   = $router->getTaskName();
        $this->assertSame($expected, $actual);
    }
}
