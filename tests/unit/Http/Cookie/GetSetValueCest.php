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

class GetSetValueCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Cookie :: getValue()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function httpCookieGetValue(UnitTester $I)
    {
        $I->wantToTest('Http\Cookie - getValue()');

        $this->setDiService('sessionStream');

        $value  = 'phalcon';
        $cookie = $this->getCookieObject();

        $expected = $value;
        $actual   = $cookie->getValue();
        $I->assertSame($expected, $actual);

        $value = 'framework';
        $cookie->setValue($value);

        $expected = $value;
        $actual   = $cookie->getValue();
        $I->assertSame($expected, $actual);

        $value = 'encrypted';
        $cookie->useEncryption(true);
        $cookie->setValue($value);

        $expected = $value;
        $actual   = $cookie->getValue();
        $I->assertSame($expected, $actual);
    }
}
