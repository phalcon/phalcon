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

namespace Phalcon\Tests\Unit\Di\Injectable;

use Phalcon\Container\Container;
use Phalcon\Di\Di;
use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Tests\Support\Di\InjectableComponent;

final class GetSetDIWithContainerTest extends AbstractUnitTestCase
{
    protected function tearDown(): void
    {
        Di::reset();
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testDiInjectableSetDIAcceptsContainer(): void
    {
        Di::reset();
        $container = new Container();
        $component = new InjectableComponent();
        $component->setDI($container);

        $this->assertSame($container, $component->getDI());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testDiInjectableGetDIFallsBackToContainerDefault(): void
    {
        Di::reset();
        $container = new Container();
        Di::setDefault($container);

        $component = new InjectableComponent();
        $actual    = $component->getDI();

        $this->assertSame($container, $actual);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testDiInjectableSetDIStillAcceptsDi(): void
    {
        Di::reset();
        $di        = new Di();
        $component = new InjectableComponent();
        $component->setDI($di);

        $this->assertSame($di, $component->getDI());
    }
}
