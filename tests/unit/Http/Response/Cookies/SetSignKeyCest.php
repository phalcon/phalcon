<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Http\Response\Cookies;

use Phalcon\Http\Response\Cookies;
use Phalcon\Tests\Fixtures\Traits\CookieTrait;
use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use UnitTester;

class SetSignKeyCest extends HttpBase
{
    use CookieTrait;

    /**
     * Executed before each test
     *
     * @param UnitTester $I
     *
     * @return void
     */
    public function _before(UnitTester $I): void
    {
        parent::_before($I);

        $this->setDiService('sessionStream');
    }

    /**
     * Tests Phalcon\Http\Response\Cookies :: setSignKey()
     *
     * @param UnitTester $I
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-01-06
     */
    public function httpResponseCookiesSetSignKey(UnitTester $I)
    {
        $I->wantToTest('Http\Response\Cookies - setSignKey()');

        $signKey = "#1dj8$=dp?.ak//j1V$~%*0XaK\xb1\x8d\xa9\x98\x054t7w!z%C*F-Jk\x98\x05\\\x5c";

        $cookies = new Cookies();
        $cookies->setDI($this->container);

        $cookies->setSignKey($signKey);

        $cookiesSigned = new Cookies(true, $signKey);
        $cookiesSigned->setDI($this->container);

        $I->assertEquals($cookies, $cookiesSigned);
    }
}
