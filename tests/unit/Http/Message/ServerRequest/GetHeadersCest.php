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
use Phalcon\Http\Message\Headers;
use Phalcon\Http\Message\ServerRequest;
use UnitTester;

class GetHeadersCest
{
    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getHeaders()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageServerRequestGetHeaders(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequest - getHeaders()');
        $data    = [
            'Accept'        => [Http::HEADERS_CONTENT_TYPE_HTML],
            'Cache-Control' => ['max-age=0'],
        ];
        $request = new ServerRequest('GET', null, [], Http::STREAM, $data);

        $expected = [
            'Accept'        => [Http::HEADERS_CONTENT_TYPE_HTML],
            'Cache-Control' => ['max-age=0'],
        ];
        $actual   = $request->getHeaders();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getHeaders() - collection
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageServerRequestGetHeadersCollection(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequest - getHeaders() - collection');
        $data    = [
            'Accept'        => [Http::HEADERS_CONTENT_TYPE_HTML],
            'Cache-Control' => ['max-age=0'],
        ];
        $headers = new Headers($data);
        $request = new ServerRequest('GET', null, [], Http::STREAM, $headers);

        $expected = [
            'Accept'        => [Http::HEADERS_CONTENT_TYPE_HTML],
            'Cache-Control' => ['max-age=0'],
        ];
        $actual   = $request->getHeaders();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getHeaders() - empty
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageServerRequestGetHeadersEmpty(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequest - getHeaders() - empty');
        $request = new ServerRequest();

        $expected = [];
        $actual   = $request->getHeaders();
        $I->assertSame($expected, $actual);
    }
}
