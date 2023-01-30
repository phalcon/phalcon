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

use Phalcon\Http\Cookie;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use UnitTester;

class GetSetExpirationCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Cookie :: getExpiration()/setExpiration()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function httpCookieGetSetExpiration(UnitTester $I)
    {
        $I->wantToTest('Http\Cookie - getExpiration()/setExpiration()');

        $this->setDiService('sessionStream');

        $expire = time() - 100;
        $cookie = $this->getCookieObject();

        $expected = $expire;
        $actual   = $cookie->getExpiration();
        $I->assertSame($expected, $actual);

        $expire = time() - 200;
        $cookie->setExpiration($expire);

        $expected = $expire;
        $actual   = $cookie->getExpiration();
        $I->assertSame($expected, $actual);
    }
}
