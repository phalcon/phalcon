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

class ToStringCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Cookie :: __toString()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function httpCookieToString(UnitTester $I)
    {
        $I->wantToTest('Http\Cookie - __toString()');

        $this->setDiService('sessionStream');

        $cookie = $this->getCookieObject();

        $expected = 'phalcon';
        $actual   = (string) $cookie;
        $I->assertSame($expected, $actual);
    }
}
