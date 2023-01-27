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
use Phalcon\Http\Response;
use Phalcon\Http\Response\Headers;
use UnitTester;

class GetSetHeadersCest
{
    /**
     * Tests Phalcon\Http\Response :: getHeaders() / setHeaders()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-12-08
     */
    public function httpResponseGetSetHeaders(UnitTester $I)
    {
        $I->wantToTest('Http\Response - getHeaders() / setHeaders()');

        // Create headers
        $headers = new Headers();
        $headers->set(Http::STATUS, '200');

        $response = new Response();
        $response->setHeaders($headers);

        $expected = $headers;
        $actual   = $response->getHeaders();
        $I->assertEquals($expected, $actual);

        $expected = $headers->toArray();
        $actual   = $response->getHeaders()->toArray();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Response :: setHeaders() - empty
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function httpResponseSetHeadersEmpty(UnitTester $I)
    {
        $I->wantToTest("Http\Response - setHeaders() - empty");

        $response = new Response();
        $headers  = new Headers();

        $headers->set('Cache-Control', 'no-cache');
        $response->setHeaders($headers);

        $expected = $headers;
        $actual   = $response->getHeaders();
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Response :: setHeaders() - merge
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function httpResponseSetHeadersMerge(UnitTester $I)
    {
        $I->wantToTest("Http\Response - setHeaders() - merge");

        $response = new Response();
        $headers  = new Headers();

        /**
         * With setHeader
         */
        $headers->set('Cache-Control', 'no-cache');
        $response->setHeader('Content-Length', '1234');
        $response->setHeaders($headers);

        $headers = new Headers();
        $headers->set('Content-Length', '1234');
        $headers->set('Cache-Control', 'no-cache');

        $expected = $headers;
        $actual   = $response->getHeaders();
        $I->assertEquals($expected, $actual);
    }
}
