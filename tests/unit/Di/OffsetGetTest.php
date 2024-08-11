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

namespace Phalcon\Tests\Unit\Di;

use Phalcon\Di\Di;
use Phalcon\Di\Exception;
use Phalcon\Html\Escaper;
use Phalcon\Tests\AbstractUnitTestCase;

class OffsetGetTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Phalcon\Di\Di :: offsetGet()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function testDiOffsetGet(): void
    {
        $container = new Di();

        $container->set('escaper', Escaper::class);

        $class  = Escaper::class;
        $actual = $container->offsetGet('escaper');
        $this->assertInstanceOf($class, $actual);

        $actual = $container['escaper'];
        $this->assertInstanceOf($class, $actual);
    }

    /**
     * Unit Tests Phalcon\Di :: offsetGet() - exception
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function testDiOffsetGetException(): void
    {
        $container = new Di();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Service 'non-exists' was not found " .
            "in the dependency injection container"
        );

        $container['non-exists'];
    }

    /**
     * Unit Tests Phalcon\Di :: offsetGet() - exception
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function testDiOffsetGetExceptionOffsetGet(): void
    {
        $container = new Di();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Service 'non-exists' was not found " .
            "in the dependency injection container"
        );

        $container->offsetGet('non-exists');
    }
}
