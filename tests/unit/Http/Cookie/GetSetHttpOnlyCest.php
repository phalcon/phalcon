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

namespace Phalcon\Tests\Unit\Http\Cookie;

use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use UnitTester;

class GetSetHttpOnlyCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Cookie :: getHttpOnly()/setHttpOnly()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function httpCookieGetSetHttpOnly(UnitTester $I)
    {
        $I->wantToTest('Http\Cookie - getHttpOnly()/setHttpOnly()');

        $this->setDiService('sessionStream');

        $httpOnly = true;
        $cookie   = $this->getCookieObject();

        $expected = $httpOnly;
        $actual   = $cookie->getHttpOnly();
        $I->assertSame($expected, $actual);

        $httpOnly = false;
        $cookie->setHttpOnly($httpOnly);

        $expected = $httpOnly;
        $actual   = $cookie->getHttpOnly();
        $I->assertSame($expected, $actual);
    }
}
