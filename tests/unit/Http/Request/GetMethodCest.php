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

namespace Phalcon\Tests\Unit\Http\Request;

use Page\Http;
use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use UnitTester;

use function uniqid;

class GetMethodCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getMethod() - default
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestGetMethodDefault(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getMethod() - default');

        // Default
        $request = $this->getRequestObject();

        $expected = Http::METHOD_GET;
        $actual   = $request->getMethod();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Request :: getMethod() - header POST
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestGetMethodHeaderPost(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getMethod() - header POST');

        $_SERVER['REQUEST_METHOD'] = Http::METHOD_POST;

        $request = $this->getRequestObject();

        $expected = Http::METHOD_POST;
        $actual   = $request->getMethod();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Request :: getMethod() - header POST override
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestGetMethodHeaderPostOverride(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getMethod() - header POST override');

        $_SERVER['REQUEST_METHOD']         = Http::METHOD_POST;
        $_SERVER['X_HTTP_METHOD_OVERRIDE'] = Http::METHOD_TRACE;

        $request = $this->getRequestObject();

        $expected = Http::METHOD_TRACE;
        $actual   = $request->getMethod();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Request :: getMethod() - header spoof
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestGetMethodHeaderSpoof(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getMethod() - header spoof');

        $_SERVER['REQUEST_METHOD'] = Http::METHOD_POST;
        $_REQUEST['_method']       = Http::METHOD_CONNECT;

        $request = $this->getRequestObject();
        $request->setHttpMethodParameterOverride(true);

        $expected = Http::METHOD_CONNECT;
        $actual   = $request->getMethod();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Request :: getMethod() - not valid
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestGetMethodNotValid(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getMethod() - not valid');

        $method                    = uniqid('meth-');
        $_SERVER['REQUEST_METHOD'] = $method;

        $request = $this->getRequestObject();

        $expected = Http::METHOD_GET;
        $actual   = $request->getMethod();
        $I->assertSame($expected, $actual);
    }
}
