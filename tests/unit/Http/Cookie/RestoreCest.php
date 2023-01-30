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

class RestoreCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Cookie :: restore()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function httpCookieRestore(UnitTester $I)
    {
        $I->wantToTest('Http\Cookie - restore()');

        $this->setDiService('sessionStream');

        $name     = 'test';
        $value    = "phalcon";
        $expire   = time() - 100;
        $path     = "/";
        $secure   = true;
        $domain   = "phalcon.ld";
        $httpOnly = true;

        $cookie = $this->getCookieObject();

        $expected = $name;
        $actual   = $cookie->getName();
        $I->assertSame($expected, $actual);
        $expected = $value;
        $actual   = $cookie->getValue();
        $I->assertSame($expected, $actual);
        $expected = $expire;
        $actual   = $cookie->getExpiration();
        $I->assertSame($expected, $actual);
        $expected = $path;
        $actual   = $cookie->getPath();
        $I->assertSame($expected, $actual);
        $expected = $secure;
        $actual   = $cookie->getSecure();
        $I->assertSame($expected, $actual);
        $expected = $domain;
        $actual   = $cookie->getDomain();
        $I->assertSame($expected, $actual);
        $expected = $httpOnly;
        $actual   = $cookie->getHttpOnly();
        $I->assertSame($expected, $actual);

        $cookie->restore();

        $expected = $name;
        $actual   = $cookie->getName();
        $I->assertSame($expected, $actual);
        $expected = $value;
        $actual   = $cookie->getValue();
        $I->assertSame($expected, $actual);
        $expected = $expire;
        $actual   = $cookie->getExpiration();
        $I->assertSame($expected, $actual);
        $expected = $path;
        $actual   = $cookie->getPath();
        $I->assertSame($expected, $actual);
        $expected = $secure;
        $actual   = $cookie->getSecure();
        $I->assertSame($expected, $actual);
        $expected = $domain;
        $actual   = $cookie->getDomain();
        $I->assertSame($expected, $actual);
        $expected = $httpOnly;
        $actual   = $cookie->getHttpOnly();
        $I->assertSame($expected, $actual);
    }
}
