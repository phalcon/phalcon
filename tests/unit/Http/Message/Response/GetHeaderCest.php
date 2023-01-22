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

class GetHeaderCest
{
    /**
     * Tests Phalcon\Http\Message\Response :: getHeader() - empty headers
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-09
     */
    public function httpMessageResponseGetHeader(UnitTester $I)
    {
        $I->wantToTest('Http\Message\Response - getHeader()');

        $data = [
            'cache-control' => ['max-age=0'],
            'accept'        => [Http::HEADERS_CONTENT_TYPE_HTML],
        ];

        $response = new Response(Http::STREAM_MEMORY, 200, $data);

        $expected = [Http::HEADERS_CONTENT_TYPE_HTML];

        $I->assertSame(
            $expected,
            $response->getHeader('accept')
        );


        $I->assertSame(
            $expected,
            $response->getHeader('aCCepT')
        );
    }

    /**
     * Tests Phalcon\Http\Message\Response :: getHeader() - empty headers
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-09
     */
    public function httpMessageResponseGetHeaderEmptyHeaders(UnitTester $I)
    {
        $I->wantToTest('Http\Message\Response - getHeader() - empty headers');

        $response = new Response();

        $I->assertSame(
            [],
            $response->getHeader('empty')
        );
    }
}
