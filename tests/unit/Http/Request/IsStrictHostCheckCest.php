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

        $store   = $_SERVER ?? [];
        $_SERVER = [
            'SERVER_NAME' => 'LOCALHOST:80',
        ];

        $request = new Request();
        $request->setStrictHostCheck(true);

        $I->assertSame('localhost', $request->getHttpHost());
        $I->assertTrue($request->isStrictHostCheck());

        $request->setStrictHostCheck(false);
        $I->assertSame('LOCALHOST:80', $request->getHttpHost());

        $I->assertFalse($request->isStrictHostCheck());

        $_SERVER = $store;
    }
}
