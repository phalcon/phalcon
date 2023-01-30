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

namespace Phalcon\Tests\Unit\Http\Response\Cookies;

use Phalcon\Http\Response\Cookies;
use Phalcon\Http\Response\CookiesInterface;
use UnitTester;

class ConstructCest
{
    /**
     * Tests Phalcon\Http\Response\Cookies :: __construct()
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-01-06
     */
    public function httpResponseCookiesConstruct(UnitTester $I)
    {
        $I->wantToTest('Http\Response\Cookies - __construct()');

        $cookies = new Cookies();
        $class   = CookiesInterface::class;
        $I->assertInstanceOf($class, $cookies);
    }

    /**
     * Tests Phalcon\Http\Response\Cookies :: __construct() - with Sign Key
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-01-06
     */
    public function httpResponseCookiesConstructSignKey(UnitTester $I)
    {
        $I->wantToTest('Http\Response\Cookies - __construct() - with Sign Key');

        $key = "#1dj8$=dp?.ak//j1V$~%*0XaK\xb1\x8d\xa9\x98\x054t7w!z%C*F-Jk\x98\x05\\\x5c";

        $cookies = new Cookies(true, $key);
        $class   = CookiesInterface::class;
        $I->assertInstanceOf($class, $cookies);
    }
}
