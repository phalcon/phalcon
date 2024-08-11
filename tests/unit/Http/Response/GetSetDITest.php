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

namespace Phalcon\Tests\Unit\Http\Response;

use Phalcon\Di\Di;
use Phalcon\Http\Response;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetSetDITest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Response :: getDI() / setDI()
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2019-12-07
     */
    public function testHttpResponseGetSetDI(): void
    {
        $container = new Di();
        $response  = new Response();

        $response->setDI($container);

        $expected = $container;
        $actual   = $response->getDI();
        $this->assertSame($expected, $actual);

        $class  = Di::class;
        $actual = $response->getDI();
        $this->assertInstanceOf($class, $actual);
    }
}
