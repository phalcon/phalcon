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
use Phalcon\Http\Message\Request;
use UnitTester;

class GetHeaderCest
{
    /**
     * Tests Phalcon\Http\Message\Request :: getHeader() - empty headers
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageRequestGetHeader(UnitTester $I)
    {
        $I->wantToTest('Http\Message\Request - getHeader()');

        $data = [
            'Cache-Control' => ['max-age=0'],
            'Accept'        => [Http::HEADERS_CONTENT_TYPE_HTML],
        ];

        $request = new Request(
            'GET',
            null,
            Http::STREAM_MEMORY,
            $data
        );

        $expected = [Http::HEADERS_CONTENT_TYPE_HTML];

        $I->assertSame(
            $expected,
            $request->getHeader('accept')
        );


        $I->assertSame(
            $expected,
            $request->getHeader('aCCepT')
        );
    }

    /**
     * Tests Phalcon\Http\Message\Request :: getHeader() - empty headers
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageRequestGetHeaderEmptyHeaders(UnitTester $I)
    {
        $I->wantToTest('Http\Message\Request - getHeader() - empty headers');

        $request = new Request();

        $I->assertSame(
            [],
            $request->getHeader('empty')
        );
    }
}
