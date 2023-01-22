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

use Phalcon\Http\Request;
use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use UnitTester;

class GetBasicAuthCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getBasicAuth() - empty
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestGetBasicAuthEmpty(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getBasicAuth() - empty');

        $request = new Request();

        $actual = $request->getBasicAuth();
        $I->assertNull($actual);
    }

    /**
     * Tests Phalcon\Http\Request :: getBasicAuth()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestGetBasicAuth(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getBasicAuth()');

        $_SERVER['PHP_AUTH_USER'] = 'darth';
        $_SERVER['PHP_AUTH_PW']   = 'vader';

        $request = new Request();

        $expected = [
            'username' => 'darth',
            'password' => 'vader',
        ];
        $actual   = $request->getBasicAuth();
        $I->assertSame($expected, $actual);
    }
}
