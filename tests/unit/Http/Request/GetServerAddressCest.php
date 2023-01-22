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

use Page\Http;
use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use UnitTester;

use function gethostbyname;

class GetServerAddressCest extends HttpBase
{
    /**
     * Tests getServerAddress default
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-10-04
     */
    public function testHttpRequestGetServerAddressDefault(UnitTester $I)
    {
        $request = $this->getRequestObject();

        $expected = gethostbyname('localhost');
        $actual   = $request->getServerAddress();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests getServerAddress
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-10-04
     */
    public function testHttpRequestGetServerAddress(UnitTester $I)
    {
        $request = $this->getRequestObject();

        $_SERVER['SERVER_ADDR'] = Http::TEST_IP_ONE;

        $expected = Http::TEST_IP_ONE;
        $actual = $request->getServerAddress();
        $I->assertSame($expected, $actual);
    }
}
