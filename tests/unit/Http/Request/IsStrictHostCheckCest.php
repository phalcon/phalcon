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

class IsStrictHostCheckCest extends HttpBase
{
    /**
     * Tests strict host check
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2016-06-26
     */
    public function testHttpStrictHostCheck(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getDigestAuth()');

        $host                   = 'LOCALHOST:80';
        $_SERVER['SERVER_NAME'] = $host;

        $request = $this->getRequestObject();
        $request->setStrictHostCheck();

        $expected = Http::HOST_LOCALHOST;
        $actual   = $request->getHttpHost();
        $I->assertSame($expected, $actual);

        $actual = $request->isStrictHostCheck();
        $I->assertTrue($actual);

        $request->setStrictHostCheck(false);

        $expected = $host;
        $actual   = $request->getHttpHost();
        $I->assertSame($expected, $actual);

        $actual = $request->isStrictHostCheck();
        $I->assertFalse($actual);
    }
}
