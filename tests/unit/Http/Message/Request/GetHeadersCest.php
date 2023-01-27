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

namespace Phalcon\Tests\Unit\Http\Message\Request;

use Page\Http;
use Phalcon\Http\Message\Headers;
use Phalcon\Http\Message\Request;
use UnitTester;

class GetHeadersCest
{
    /**
     * Tests Phalcon\Http\Message\Request :: getHeaders()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageRequestGetHeaders(UnitTester $I)
    {
        $I->wantToTest('Http\Message\Request - getHeaders()');

        $data = [
            'Accept'        => [Http::CONTENT_TYPE_HTML],
            'Cache-Control' => ['max-age=0'],
        ];

        $request = new Request(
            'GET',
            null,
            Http::STREAM_MEMORY,
            $data
        );

        $expected = [
            'Accept'        => [Http::CONTENT_TYPE_HTML],
            'Cache-Control' => ['max-age=0'],
        ];
        $actual   = $request->getHeaders();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Request :: getHeaders() - collection
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageRequestGetHeadersCollection(UnitTester $I)
    {
        $I->wantToTest('Http\Message\Request - getHeaders()');

        $data = [
            'Accept'        => [Http::CONTENT_TYPE_HTML],
            'Cache-Control' => ['max-age=0'],
        ];

        $headers = new Headers($data);

        $request = new Request(
            'GET',
            null,
            Http::STREAM_MEMORY,
            $headers
        );

        $expected = [
            'Accept'        => [Http::CONTENT_TYPE_HTML],
            'Cache-Control' => ['max-age=0'],
        ];
        $actual   = $request->getHeaders();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Request :: getHeaders() - empty
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageRequestGetHeadersEmpty(UnitTester $I)
    {
        $I->wantToTest('Http\Message\Request - getHeaders() - empty');

        $request = new Request();

        $expected = [];
        $actual   = $request->getHeaders();
        $I->assertSame($expected, $actual);
    }
}
