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
use Phalcon\Tests\Fixtures\Traits\CookieTrait;
use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use UnitTester;

use function uniqid;

class GetCookiesCest extends HttpBase
{
    use CookieTrait;

    /**
     * executed before each test
     */
    public function _before(UnitTester $I)
    {
        parent::_before($I);
        $this->setDiService('sessionStream');
    }

    /**
     * Tests Phalcon\Http\Response\Cookies :: getCookies()
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-01-06
     */
    public function httpResponseCookiesGetCookies(UnitTester $I)
    {
        $I->wantToTest('Http\Response\Cookies - getCookies()');

        $name  = uniqid('nam-');
        $value = uniqid('val-');

        $this->setDiService('crypt');

        $cookies = new Cookies();
        $cookies->setDI($this->container);
        $cookies->set($name, $value);

        $keys = $cookies->getCookies();

        $actual = array_key_exists($name, $keys);
        $I->assertTrue($actual);

        $expected = $value;
        $actual   = (string) $keys[$name];
        $I->assertSame($expected, $actual);

        $expected = 1;
        $actual   = count($keys);
        $I->assertSame($expected, $actual);
    }
}
