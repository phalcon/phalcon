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

class SetRawCest
{
    /**
     * Tests Phalcon\Http\Response\Headers :: setRaw()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-05-08
     */
    public function httpResponseHeadersSetRaw(UnitTester $I)
    {
        $I->wantToTest('Http\Response\Headers - setRaw()');

        $headers = new Headers();
        $headers->setRaw(Http::HEADERS_CONTENT_TYPE_HTML_RAW);

        $I->assertTrue($headers->has(Http::HEADERS_CONTENT_TYPE_HTML_RAW));
        $I->assertFalse($headers->has(Http::HEADERS_CONTENT_TYPE_PLAIN_RAW));
    }
}
