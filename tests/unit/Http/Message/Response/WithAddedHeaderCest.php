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

use Page\Http;
use Phalcon\Http\Message\Response;
use UnitTester;

class WithAddedHeaderCest
{
    /**
     * Tests Phalcon\Http\Message\Response :: withAddedHeader()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-09
     */
    public function httpMessageResponseWithAddedHeader(UnitTester $I)
    {
        $I->wantToTest('Http\Message\Response - withAddedHeader()');
        $data        = [
            'Accept' => [Http::HEADERS_CONTENT_TYPE_HTML],
        ];
        $response    = new Response(Http::STREAM_MEMORY, 200, $data);
        $newInstance = $response->withAddedHeader('Cache-Control', ['max-age=0']);

        $I->assertNotSame($response, $newInstance);

        $expected = [
            'Accept' => [Http::HEADERS_CONTENT_TYPE_HTML],
        ];
        $actual   = $response->getHeaders();
        $I->assertSame($expected, $actual);

        $expected = [
            'Accept'        => [Http::HEADERS_CONTENT_TYPE_HTML],
            'Cache-Control' => ['max-age=0'],
        ];
        $actual   = $newInstance->getHeaders();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Response :: withAddedHeader() - merge
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-09
     */
    public function httpMessageResponseWithAddedHeaderMerge(UnitTester $I)
    {
        $data        = [
            'Accept' => [Http::HEADERS_CONTENT_TYPE_HTML],
        ];
        $response    = new Response(Http::STREAM_MEMORY, 200, $data);
        $newInstance = $response->withAddedHeader('Accept', ['text/json']);

        $I->assertNotSame($response, $newInstance);

        $expected = [
            'Accept' => [Http::HEADERS_CONTENT_TYPE_HTML],
        ];
        $actual   = $response->getHeaders();
        $I->assertSame($expected, $actual);

        $expected = [
            'Accept' => [Http::HEADERS_CONTENT_TYPE_HTML, 'text/json'],
        ];
        $actual   = $newInstance->getHeaders();
        $I->assertSame($expected, $actual);
    }
}
