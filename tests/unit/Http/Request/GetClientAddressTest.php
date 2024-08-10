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

namespace Phalcon\Tests\Unit\Http\Request;

use Phalcon\Http\Request;
use Phalcon\Tests\Fixtures\Page\Http;
use Phalcon\Tests\Unit\Http\Helper\AbstractHttpBase;

final class GetClientAddressTest extends AbstractHttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getClientAddress()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function testHttpRequestGetClientAddress(): void
    {
        $_SERVER['REMOTE_ADDR'] = Http::TEST_IP_THREE;

        $request = new Request();

        $expected = Http::TEST_IP_THREE;
        $actual   = $request->getClientAddress();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Request :: getClientAddress() - incorrect
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function testHttpRequestGetClientAddressIncorrect(): void
    {
        $_SERVER['REMOTE_ADDR'] = [Http::TEST_IP_THREE];

        $request = new Request();

        $actual = $request->getClientAddress();
        $this->assertFalse($actual);
    }

    /**
     * Tests Phalcon\Http\Request :: getClientAddress() - ipv6
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function testHttpRequestGetClientAddressIpv6(): void
    {
        $_SERVER['REMOTE_ADDR'] = Http::TEST_IP_IPV6;

        $request = new Request();

        $expected = Http::TEST_IP_IPV6;
        $actual   = $request->getClientAddress();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Request :: getClientAddress() - multiple
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function testHttpRequestGetClientAddressMultiple(): void
    {
        $_SERVER['REMOTE_ADDR'] = Http::TEST_IP_MULTI;

        $request = new Request();

        $expected = '10.4.6.4';
        $actual   = $request->getClientAddress();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Request :: getClientAddress() - trustForwardedHeader
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function testHttpRequestGetClientAddressTrustForwardedHeader(): void
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = Http::TEST_IP_ONE;

        $request = new Request();

        $expected = Http::TEST_IP_ONE;
        $actual   = $request->getClientAddress(true);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Request :: getClientAddress() - trustForwardedHeader
     * - client IP
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function testHttpRequestGetClientAddressTrustForwardedHeaderClientIp(): void
    {
        $_SERVER['HTTP_CLIENT_IP'] = Http::TEST_IP_TWO;

        $request = new Request();

        $expected = Http::TEST_IP_TWO;
        $actual   = $request->getClientAddress(true);
        $this->assertSame($expected, $actual);
    }
}
