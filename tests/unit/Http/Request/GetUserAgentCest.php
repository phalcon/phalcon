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

class GetUserAgentCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getUserAgent() - default
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestGetUserAgentDefault(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getUserAgent() - default');

        $request = $this->getRequestObject();

        $actual = $request->getUserAgent();
        $I->assertEmpty($actual);
    }

    /**
     * Tests Phalcon\Http\Request :: getUserAgent()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestGetUserAgent(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getUserAgent()');

        $_SERVER['HTTP_USER_AGENT'] = Http::TEST_USER_AGENT;

        $request = $this->getRequestObject();

        $expected = Http::TEST_USER_AGENT;
        $actual   = $request->getUserAgent();
        $I->assertSame($expected, $actual);
    }
}
