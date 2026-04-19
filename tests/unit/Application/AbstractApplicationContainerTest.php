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

namespace Phalcon\Tests\Unit\Application;

use Phalcon\Container\Container;
use Phalcon\Di\Di;
use Phalcon\Mvc\Application;
use Phalcon\Tests\AbstractUnitTestCase;

final class AbstractApplicationContainerTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-19
     */
    public function testAcceptsContainer(): void
    {
        $container = new Container();
        $app       = new Application($container);

        $this->assertSame($container, $app->getDI());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-19
     */
    public function testAcceptsDi(): void
    {
        $di  = new Di();
        $app = new Application($di);

        $this->assertSame($di, $app->getDI());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-19
     */
    public function testAcceptsNull(): void
    {
        $app = new Application();

        $this->assertInstanceOf(Application::class, $app);
    }
}
