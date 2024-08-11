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

namespace Phalcon\Tests\Unit\Container;

use Phalcon\Container\Container;
use Phalcon\Container\Definitions\Definitions;
use Phalcon\Tests\Fixtures\Container\TestProvider;
use Phalcon\Tests\Fixtures\Container\TestWithDefaultConstructorParameters;
use Phalcon\Tests\Fixtures\Container\TestWithInterface;
use Phalcon\Tests\AbstractUnitTestCase;
use stdClass;

final class ContainerTest extends AbstractUnitTestCase
{
    protected $container;

    public function setUp(): void
    {
        $this->container = new Container(
            new Definitions(),
            [
                new TestProvider(),
            ]
        );
    }

    /**
     * @return void
     */
    public function testContainerContainerCallableGet(): void
    {
        $callable = $this->container->callableGet(stdClass::class);

        $expected = $callable(stdClass::class);
        $actual   = $callable(stdClass::class);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerContainerCallableNew(): void
    {
        $callable = $this->container->callableNew(stdClass::class);

        $expected = $callable(stdClass::class);
        $actual   = $callable(stdClass::class);
        $this->assertNotSame($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerContainerGet(): void
    {
        $expected = $this->container->get(stdClass::class);
        $actual   = $this->container->get(stdClass::class);
        $this->assertSame($expected, $actual);

        $expected = 'oneval';
        $actual   = $this->container->get('oneval');
        $this->assertSame($expected, $actual);

        $expected = 'lazyval';
        $actual   = $this->container->get('lazyval');
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerContainerHas(): void
    {
        // defined object
        $actual = $this->container->has(TestWithInterface::class);
        $this->assertTrue($actual);

        // defined value
        $actual = $this->container->has('oneval');
        $this->assertTrue($actual);

        // not defined but exists
        $actual = $this->container->has(TestWithDefaultConstructorParameters::class);
        $this->assertTrue($actual);

        // not defined and does not exist
        $actual = $this->container->has('NoSuchClass');
        $this->assertFalse($actual);
    }

    /**
     * @return void
     */
    public function testContainerContainerNew(): void
    {
        $expected = $this->container->new(stdClass::class);
        $actual   = $this->container->new(stdClass::class);
        $this->assertNotSame($expected, $actual);

        $expected = 'oneval';
        $actual   = $this->container->new('oneval');
        $this->assertSame($expected, $actual);

        $expected = 'lazyval';
        $actual   = $this->container->new('lazyval');
        $this->assertSame($expected, $actual);
    }
}
