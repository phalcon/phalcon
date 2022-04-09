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

namespace Phalcon\Tests\Unit\Http\Message\ServerRequest;

use Phalcon\Http\Message\ServerRequest;
use UnitTester;

class WithMethodCest
{
    /**
     * Tests Phalcon\Http\Message\ServerRequest :: withMethod()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageServerRequestWithMethod(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequest - withMethod()');
        $request     = new ServerRequest();
        $newInstance = $request->withMethod('POST');

        $I->assertNotSame($request, $newInstance);

        $expected = 'GET';
        $actual   = $request->getMethod();
        $I->assertSame($expected, $actual);

        $expected = 'POST';
        $actual   = $newInstance->getMethod();
        $I->assertSame($expected, $actual);
    }
}
