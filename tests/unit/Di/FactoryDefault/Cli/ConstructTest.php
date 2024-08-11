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

namespace Phalcon\Tests\Unit\Di\FactoryDefault\Cli;

use Phalcon\Di\Exception;
use Phalcon\Di\FactoryDefault\Cli;
use Phalcon\Tests\Fixtures\Traits\CliTrait;
use Phalcon\Tests\AbstractUnitTestCase;

final class ConstructTest extends AbstractUnitTestCase
{
    use CliTrait;

    /**
     * Tests Phalcon\Di\FactoryDefault\Cli :: __construct()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function testDiFactoryDefaultCliConstruct(): void
    {
        $container = new Cli();
        $services  = $this->getServices();

        $expected = count($services);
        $actual   = count($container->getServices());
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Di\FactoryDefault\Cli :: __construct() - Check services
     *
     * @dataProvider getServices
     *
     * @return void
     *
     * @throws Exception
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-09-09
     */
    public function testDiFactoryDefaultCliConstructServices(
        string $class,
        string $service
    ): void {
        $container = new Cli();

        if ('sessionBag' === $class) {
            $params = ['someName'];
        } else {
            $params = null;
        }

        $actual = $container->get($class, $params);
        $this->assertInstanceOf($service, $actual);
    }
}
