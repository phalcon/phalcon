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

class GetServerNameCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getServerName() - default
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestGetServerNameDefault(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getServerName() - default');

        $request = $this->getRequestObject();

        $expected = Http::HOST_LOCALHOST;
        $actual   = $request->getServerName();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Request :: getServerName()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestGetServerName(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getServerName()');

        $_SERVER['SERVER_NAME'] = Http::TEST_DOMAIN;

        $request = $this->getRequestObject();

        $expected = Http::TEST_DOMAIN;
        $actual   = $request->getServerName();
        $I->assertSame($expected, $actual);
    }
}
