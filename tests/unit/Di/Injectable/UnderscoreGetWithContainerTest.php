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
use stdClass;

final class UnderscoreGetWithContainerTest extends AbstractUnitTestCase
{
    protected function tearDown(): void
    {
        Di::reset();
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testDiInjectableUnderscoreGetServiceFromContainer(): void
    {
        Di::reset();
        $obj       = new stdClass();
        $container = new Container();
        $container->set('myService', static function () use ($obj) {
            return $obj;
        });

        $component = new InjectableComponent();
        $component->setDI($container);

        $actual = $component->myService;
        $this->assertSame($obj, $actual);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testDiInjectableUnderscoreGetDiPropertyWithContainer(): void
    {
        Di::reset();
        $container = new Container();
        $component = new InjectableComponent();
        $component->setDI($container);

        $actual = $component->di;
        $this->assertSame($container, $actual);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testDiInjectableIssetWithContainer(): void
    {
        Di::reset();
        $container = new Container();
        $container->set('myService', static function () {
            return new stdClass();
        });

        $component = new InjectableComponent();
        $component->setDI($container);

        $this->assertTrue(isset($component->myService));
        $this->assertFalse(isset($component->undefinedService));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testDiInjectableUnderscoreGetSharedReturnsSameInstanceFromContainer(): void
    {
        Di::reset();
        $container = new Container();
        $container->set('myService', static function () {
            return new stdClass();
        });

        $component = new InjectableComponent();
        $component->setDI($container);

        $first  = $component->myService;
        $second = $component->myService;
        $this->assertSame($first, $second);
    }
}
