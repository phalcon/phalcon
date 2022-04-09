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

namespace Phalcon\Tests\Unit\Http\Message\Request;

use Phalcon\Http\Message\Request;
use UnitTester;

class WithMethodCest
{
    /**
     * Tests Phalcon\Http\Message\Request :: withMethod()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageRequestWithMethod(UnitTester $I)
    {
        $I->wantToTest('Http\Message\Request - withMethod()');

        $request = new Request();

        $newInstance = $request->withMethod('POST');

        $I->assertNotSame($request, $newInstance);

        $I->assertSame(
            'GET',
            $request->getMethod()
        );

        $I->assertSame(
            'POST',
            $newInstance->getMethod()
        );
    }
}
