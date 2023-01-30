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

use Phalcon\Http\Message\Exception\InvalidArgumentException;
use Phalcon\Http\Message\ServerRequest;
use UnitTester;

class WithParsedBodyCest
{
    /**
     * Tests Phalcon\Http\Message\ServerRequest :: withParsedBody()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageServerRequestWithParsedBody(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequest - withParsedBody()');
        $request     = new ServerRequest();
        $newInstance = $request->withParsedBody(['key' => 'value']);

        $I->assertNotSame($request, $newInstance);

        $actual = $request->getParsedBody();
        $I->assertNull($actual);

        $expected = ['key' => 'value'];
        $actual   = $newInstance->getParsedBody();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: withParsedBody() - exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageServerRequestWithParsedBodyException(
        UnitTester $I
    ) {
        $I->wantToTest(
            'Http\Message\ServerRequest - withParsedBody() - exception'
        );
        $I->expectThrowable(
            new InvalidArgumentException(
                'The method expects null, an array or an object'
            ),
            function () {
                $request     = new ServerRequest();
                $newInstance = $request->withParsedBody('something');
            }
        );
    }
}
