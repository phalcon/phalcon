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

use Exception;
use Phalcon\Di\Di;
use Phalcon\Html\Escaper;
use Phalcon\Tests\AbstractUnitTestCase;

class GetRawTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Di\Di :: getRaw()
     *
     * @return void
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function testDiGetRaw(): void
    {
        $container = new Di();

        // existing service
        $container->set('escaper', Escaper::class);

        $expected = Escaper::class;
        $actual   = $container->getRaw('escaper');

        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Di :: getRaw() - exception
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function testDiGetRawException(): void
    {
        $container = new Di();

        // nonexistent service
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Service 'nonexistent-service' was not found " .
            "in the dependency injection container"
        );

        $container->getRaw('nonexistent-service');
    }
}
