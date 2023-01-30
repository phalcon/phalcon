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

class SetCest
{
    /**
     * Tests Phalcon\Http\Response\Headers :: set()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-05-08
     */
    public function httpResponseHeadersSet(UnitTester $I)
    {
        $I->wantToTest('Http\Response\Headers - set()');

        $headers = new Headers();
        $headers->set(
            Http::CONTENT_TYPE,
            Http::CONTENT_TYPE_HTML
        );

        $expected = Http::CONTENT_TYPE_HTML;
        $actual   = $headers->get(Http::CONTENT_TYPE);

        $I->assertSame($expected, $actual);

        $headers->set(
            Http::CONTENT_TYPE,
            Http::CONTENT_TYPE_PLAIN
        );

        $expected = Http::CONTENT_TYPE_PLAIN;
        $actual   = $headers->get(Http::CONTENT_TYPE);

        $I->assertSame($expected, $actual);
    }
}
