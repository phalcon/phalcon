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

namespace Phalcon\Tests\Unit\Http\Response;

use Page\Http;
use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use UnitTester;

class RedirectCest extends HttpBase
{
    /**
     * Tests redirect locally
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-10-08
     */
    public function testHttpResponseRedirectLocalUrl(UnitTester $I)
    {
        $response = $this->getResponseObject();

        $response->resetHeaders();
        $response->redirect(Http::REDIRECT_URI);

        $headers = $response->getHeaders();

        $expected = Http::MESSAGE_302_FOUND;
        $actual   = $headers->get(Http::STATUS);
        $I->assertSame($expected, $actual);

        $expected = '/' . Http::REDIRECT_URI;
        $actual   = $headers->get(Http::LOCATION);
        $I->assertSame($expected, $actual);

        $actual = $headers->get(Http::HTTP_302_FOUND);
        $I->assertNull($actual);
    }

    /**
     * Tests redirect remotely 302
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-10-08
     */
    public function testHttpResponseRedirectRemoteUrl302(UnitTester $I)
    {
        $response = $this->getResponseObject();

        $response->resetHeaders();
        $response->redirect(Http::TEST_URI, true);

        $headers = $response->getHeaders();

        $expected = Http::MESSAGE_302_FOUND;
        $actual   = $headers->get(Http::STATUS);
        $I->assertSame($expected, $actual);

        $expected = Http::TEST_URI;
        $actual   = $headers->get(Http::LOCATION);
        $I->assertSame($expected, $actual);

        $actual = $headers->get(Http::HTTP_302_FOUND);
        $I->assertNull($actual);
    }

    /**
     * Tests redirect local with non-standard code
     *
     * @issue  https://github.com/phalcon/cphalcon/issues/11324
     * @author Phalcon Team <team@phalcon.io>
     * @since  2016-01-19
     */
    public function testHttpResponseRedirectLocalUrlWithNonStandardCode(
        UnitTester $I
    ) {
        $response = $this->getResponseObject();

        $response->resetHeaders();
        $response->redirect(Http::REDIRECT_URI, false, 309);

        $headers = $response->getHeaders();

        $expected = Http::MESSAGE_302_FOUND;
        $actual   = $headers->get(Http::STATUS);
        $I->assertSame($expected, $actual);

        $expected = '/' . Http::REDIRECT_URI;
        $actual   = $headers->get(Http::LOCATION);
        $I->assertSame($expected, $actual);

        $actual = $headers->get(Http::HTTP_302_FOUND);
        $I->assertNull($actual);
    }

    /**
     * Tests redirect remotely 301
     *
     * @issue  https://github.com/phalcon/cphalcon/issues/1182
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-10-08
     */
    public function testHttpResponseRedirectRemoteUrl301(UnitTester $I)
    {
        $response = $this->getResponseObject();

        $response->resetHeaders();
        $response->redirect(
            Http::TEST_URI,
            true,
            Http::CODE_301
        );

        $headers = $response->getHeaders();

        $expected = Http::MESSAGE_301_MOVED_PERMANENTLY;
        $actual   = $headers->get(Http::STATUS);
        $I->assertSame($expected, $actual);

        $expected = Http::TEST_URI;
        $actual   = $headers->get(Http::LOCATION);
        $I->assertSame($expected, $actual);

        $actual = $headers->get(Http::HTTP_302_FOUND);
        $I->assertFalse($actual);
    }
}
