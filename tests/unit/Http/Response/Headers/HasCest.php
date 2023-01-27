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

class HasCest
{
    /**
     * Tests Phalcon\Http\Response\Headers :: has()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-05-08
     */
    public function httpResponseHeadersHas(UnitTester $I)
    {
        $I->wantToTest('Http\Response\Headers - has()');

        $headers = new Headers();
        $headers->set(
            Http::CONTENT_TYPE,
            Http::CONTENT_TYPE_HTML_CHARSET
        );
        $headers->set(
            Http::CONTENT_ENCODING,
            Http::CONTENT_ENCODING_GZIP
        );

        $actual = $headers->has(Http::CONTENT_TYPE);
        $I->assertTrue($actual);

        $actual = $headers->has(Http::CONTENT_ENCODING);
        $I->assertTrue($actual);

        $actual = $headers->has(Http::SERVER);
        $I->assertFalse($actual);
    }
}
