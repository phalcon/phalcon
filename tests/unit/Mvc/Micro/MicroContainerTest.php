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

namespace Phalcon\Tests\Unit\Mvc\Micro;

use Phalcon\Container\Container;
use Phalcon\Di\Di;
use Phalcon\Mvc\Micro;
use Phalcon\Tests\AbstractUnitTestCase;

class MicroContainerTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-19
     */
    public function testAcceptsContainer(): void
    {
        $container = new Container();
        $app       = new Micro($container);

        $this->assertSame($container, $app->getDI());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-19
     */
    public function testAcceptsDi(): void
    {
        $di  = new Di();
        $app = new Micro($di);

        $this->assertSame($di, $app->getDI());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-19
     */
    public function testAcceptsNull(): void
    {
        $app = new Micro();

        $this->assertInstanceOf(Micro::class, $app);
    }
}
