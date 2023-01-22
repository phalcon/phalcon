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
use Phalcon\Http\Request;
use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use UnitTester;

class GetURICest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getURI() - default
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestGetURIDefault(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getURI() - default');

        $request = $this->getRequestObject();

        $actual = $request->getURI();
        $I->assertEmpty($actual);
    }

    /**
     * Tests Phalcon\Http\Request :: getURI()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestGetURI(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getURI()');

        $uri = Http::TEST_URI . '?a=b';
        $_SERVER['REQUEST_URI'] = $uri;

        $request = $this->getRequestObject();

        $expected = $uri;
        $actual   = $request->getURI();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Request :: getURI() - only path
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestGetURIOnlyPath(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getURI() - only path');

        $uri = Http::TEST_URI . '?a=b';
        $_SERVER['REQUEST_URI'] = $uri;

        $request = $this->getRequestObject();

        $expected = Http::TEST_URI;
        $actual   = $request->getURI(true);
        $I->assertSame($expected, $actual);
    }
}
