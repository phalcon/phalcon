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

class GetSetSecureCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Cookie :: getSecure()/setSecure()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function httpCookieGetSetSecure(UnitTester $I)
    {
        $I->wantToTest('Http\Cookie - getSecure()/setSecure()');

        $this->setDiService('sessionStream');

        $secure = true;
        $cookie = $this->getCookieObject();

        $expected = true;
        $actual   = $cookie->getSecure();
        $I->assertSame($expected, $actual);

        $secure = false;
        $cookie->setSecure($secure);

        $expected = false;
        $actual   = $cookie->getSecure();
        $I->assertSame($expected, $actual);
    }
}
