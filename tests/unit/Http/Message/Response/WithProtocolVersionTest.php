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

namespace Phalcon\Tests\Unit\Http\Message\Response;

use InvalidArgumentException;
use Phalcon\Http\Message\Response;
use Phalcon\Tests\AbstractUnitTestCase;

final class WithProtocolVersionTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Response :: withProtocolVersion()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-09
     */
    public function testHttpMessageResponseWithProtocolVersion(): void
    {
        $response    = new Response();
        $newInstance = $response->withProtocolVersion('2.0');

        $this->assertNotSame($response, $newInstance);

        $expected = '1.1';
        $actual   = $response->getProtocolVersion();
        $this->assertSame($expected, $actual);

        $expected = '2.0';
        $actual   = $newInstance->getProtocolVersion();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Response :: withProtocolVersion() - exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-09
     */
    public function testHttpMessageResponseWithProtocolVersionException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported protocol 1.2');

        $response = new Response();
        $response->withProtocolVersion('1.2');
    }
}
