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

namespace Phalcon\Tests\Unit\Http\Message\ServerRequest;

use Page\Http;
use Phalcon\Http\Message\ServerRequest;
use UnitTester;

class GetHeaderCest
{
    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getHeader() - empty headers
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageServerRequestGetHeader(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequest - getHeader()');
        $data    = [
            'Cache-Control' => ['max-age=0'],
            'Accept'        => [Http::HEADERS_CONTENT_TYPE_HTML],
        ];
        $request = new ServerRequest('GET', null, [], Http::STREAM, $data);

        $expected = [Http::HEADERS_CONTENT_TYPE_HTML];
        $actual   = $request->getHeader('accept');
        $I->assertSame($expected, $actual);

        $actual = $request->getHeader('aCCepT');
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getHeader() - empty headers
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageServerRequestGetHeaderEmptyHeaders(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequest - getHeader() - empty headers');
        $request = new ServerRequest();

        $expected = [];
        $actual   = $request->getHeader('empty');
        $I->assertSame($expected, $actual);
    }
}
