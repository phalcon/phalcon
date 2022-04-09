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

use Phalcon\Http\Message\Exception\InvalidArgumentException;
use Phalcon\Http\Message\Request;
use UnitTester;

class WithRequestTargetCest
{
    /**
     * Tests Phalcon\Http\Message\Request :: withRequestTarget()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageRequestWithRequestTarget(UnitTester $I)
    {
        $I->wantToTest('Http\Message\Request - withRequestTarget()');

        $request = new Request();

        $newInstance = $request->withRequestTarget('/test');

        $I->assertNotSame($request, $newInstance);

        $expected = "/";
        $actual   = $request->getRequestTarget();
        $I->assertSame($expected, $actual);

        $expected = "/test";
        $actual   = $newInstance->getRequestTarget();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Request :: withRequestTarget() - exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageRequestWithRequestTargetException(UnitTester $I)
    {
        $I->wantToTest('Http\Message\Request - withRequestTarget() - exception');

        $I->expectThrowable(
            new InvalidArgumentException(
                'Invalid request target: cannot contain whitespace'
            ),
            function () {
                $request = new Request();

                $newInstance = $request->withRequestTarget('/te st');
            }
        );
    }
}
