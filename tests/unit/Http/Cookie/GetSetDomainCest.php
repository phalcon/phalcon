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

class GetSetDomainCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Cookie :: getDomain()/setDomain()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function httpCookieGetSetDomain(UnitTester $I)
    {
        $I->wantToTest('Http\Cookie - getDomain()/setDomain()');

        $this->setDiService('sessionStream');

        $domain = "phalcon.ld";
        $cookie = $this->getCookieObject();

        $expected = $domain;
        $actual   = $cookie->getDomain();
        $I->assertSame($expected, $actual);

        $domain = 'phalcon.io';
        $cookie->setDomain($domain);

        $expected = $domain;
        $actual   = $cookie->getDomain();
        $I->assertSame($expected, $actual);
    }
}
