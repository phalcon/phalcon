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

namespace Phalcon\Tests\Unit\Http\Response\Headers;

use Page\Http;
use Phalcon\Http\Response\Headers;
use UnitTester;

class ToArrayCest
{
    /**
     * Tests Phalcon\Http\Response\Headers :: toArray()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-05-08
     */
    public function httpResponseHeadersToArray(UnitTester $I)
    {
        $I->wantToTest('Http\Response\Headers - toArray()');

        $headers = new Headers();
        $headers->set(
            Http::HEADERS_CONTENT_TYPE,
            Http::HEADERS_CONTENT_TYPE_HTML_CHARSET
        );
        $headers->set(
            Http::HEADERS_CONTENT_ENCODING,
            Http::HEADERS_CONTENT_ENCODING_GZIP
        );

        $expected = [
            Http::HEADERS_CONTENT_TYPE => Http::HEADERS_CONTENT_TYPE_HTML_CHARSET,
            Http::HEADERS_CONTENT_ENCODING => Http::HEADERS_CONTENT_ENCODING_GZIP,
        ];
        $actual   = $headers->toArray();

        $I->assertSame($expected, $actual);
    }
}
