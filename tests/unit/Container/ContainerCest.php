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
use stdClass;
use UnitTester;

class ContainerCest
{
    protected $container;

    public function _before(): void
    {
        $this->container = new Container(
            new Definitions(),
            [
                new TestProvider(),
            ]
        );
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerContainerGet(UnitTester $I): void
    {
        $expected = $this->container->get(stdClass::class);
        $actual = $this->container->get(stdClass::class);
        $I->assertSame($expected, $actual);

        $expected = 'oneval';
        $actual   = $this->container->get('oneval');
        $I->assertSame($expected, $actual);

        $expected = 'lazyval';
        $actual   = $this->container->get('lazyval');
        $I->assertSame($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerContainerHas(UnitTester $I): void
    {
        // defined object
        $actual = $this->container->has(TestWithInterface::class);
        $I->assertTrue($actual);

        // defined value
        $actual = $this->container->has('oneval');
        $I->assertTrue($actual);

        // not defined but exists
        $actual = $this->container->has(TestWithDefaultConstructorParameters::class);
        $I->assertTrue($actual);

        // not defined and does not exist
        $actual = $this->container->has('NoSuchClass');
        $I->assertFalse($actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerContainerNew(UnitTester $I): void
    {
        $expected = $this->container->new(stdClass::class);
        $actual   = $this->container->new(stdClass::class);
        $I->assertNotSame($expected, $actual);

        $expected = 'oneval';
        $actual   = $this->container->new('oneval');
        $I->assertSame($expected, $actual);

        $expected = 'lazyval';
        $actual   = $this->container->new('lazyval');
        $I->assertSame($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerContainerCallableGet(UnitTester $I): void
    {
        $callable = $this->container->callableGet(stdClass::class);

        $expected = $callable(stdClass::class);
        $actual   = $callable(stdClass::class);
        $I->assertSame($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerContainerCallableNew(UnitTester $I): void
    {
        $callable = $this->container->callableNew(stdClass::class);

        $expected = $callable(stdClass::class);
        $actual   = $callable(stdClass::class);
        $I->assertNotSame($expected, $actual);
    }
}
