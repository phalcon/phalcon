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

class WithAddedHeaderCest
{
    /**
     * Tests Phalcon\Http\Message\ServerRequest :: withAddedHeader()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageServerRequestWithAddedHeader(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequest - withAddedHeader()');
        $data        = [
            'Accept' => [Http::CONTENT_TYPE_HTML],
        ];
        $request     = new ServerRequest('GET', null, [], Http::STREAM, $data);
        $newInstance = $request->withAddedHeader('Cache-Control', ['max-age=0']);

        $I->assertNotSame($request, $newInstance);

        $expected = [
            'Accept' => [Http::CONTENT_TYPE_HTML],
        ];
        $actual   = $request->getHeaders();
        $I->assertSame($expected, $actual);

        $expected = [
            'Accept'        => [Http::CONTENT_TYPE_HTML],
            'Cache-Control' => ['max-age=0'],
        ];
        $actual   = $newInstance->getHeaders();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: withAddedHeader() - merge
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageServerRequestWithAddedHeaderMerge(UnitTester $I)
    {
        $data        = [
            'Accept' => [Http::CONTENT_TYPE_HTML],
        ];
        $request     = new ServerRequest('GET', null, [], Http::STREAM, $data);
        $newInstance = $request->withAddedHeader('Accept', ['text/json']);

        $I->assertNotSame($request, $newInstance);

        $expected = [
            'Accept' => [Http::CONTENT_TYPE_HTML],
        ];
        $actual   = $request->getHeaders();
        $I->assertSame($expected, $actual);

        $expected = [
            'Accept' => [Http::CONTENT_TYPE_HTML, 'text/json'],
        ];
        $actual   = $newInstance->getHeaders();
        $I->assertSame($expected, $actual);
    }
}
