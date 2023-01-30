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

class GetDigestAuthCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getDigestAuth()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestGetDigestAuth(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getDigestAuth()');

        $_SERVER['PHP_AUTH_DIGEST'] = 'Digest realm="phalcon.io",'
            . 'qop="auth",nonce="abcdef",opaque="123456789"';

        $request = new Request();

        $expected = [
            'realm'  => 'phalcon.io',
            'qop'    => 'auth',
            'nonce'  => 'abcdef',
            'opaque' => '123456789',
        ];
        $actual   = $request->getDigestAuth();
        $I->assertSame($expected, $actual);
    }
}
