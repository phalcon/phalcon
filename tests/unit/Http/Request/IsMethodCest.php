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

class IsMethodCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Request :: isMethod()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestIsMethod(UnitTester $I)
    {
        $I->wantToTest('Http\Request - isMethod()');

        $_SERVER['REQUEST_METHOD'] = Http::METHOD_POST;

        $request = $this->getRequestObject();

        $actual = $request->isMethod(Http::METHOD_POST);
        $I->assertTrue($actual);

        $actual = $request->isMethod(
            [
                Http::METHOD_GET,
                Http::METHOD_POST,
            ]
        );
        $I->assertTrue($actual);


        $_SERVER['REQUEST_METHOD'] = Http::METHOD_GET;

        $actual = $request->isMethod(Http::METHOD_GET);
        $I->assertTrue($actual);

        $actual = $request->isMethod(
            [
                Http::METHOD_GET,
                Http::METHOD_POST,
            ]
        );
        $I->assertTrue($actual);
    }
}
