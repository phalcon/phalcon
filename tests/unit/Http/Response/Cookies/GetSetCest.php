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

use function time;
use function uniqid;

class GetSetCest extends HttpBase
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
     * Tests Phalcon\Http\Response\Cookies :: get / set()
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-01-06
     */
    public function httpResponseCookiesGetSet(UnitTester $I)
    {
        $I->wantToTest('Http\Response\Cookies - get / set()');
        $name  = uniqid('nam-');
        $value = uniqid('val-');

        $this->setDiService('crypt');

        $cookies = new Cookies();
        $cookies->setDI($this->container);
        $cookies->set($name, $value);

        $expected = $value;
        $actual   = (string) $cookies->get($name);
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Issue #13464
     *
     * @author Cameron Hall <me@chall.id.au>
     * @since  2019-01-20
     * @issue https://github.com/phalcon/cphalcon/issues/13464
     */
    public function httpCookieSetHttpOnly(UnitTester $I)
    {
        $I->wantToTest('Issue #13464');
        $I->checkExtensionIsLoaded('xdebug');

        $this->setDiService('crypt');

        $cookie = new Cookies();
        $cookie->setDI($this->container);
        $cookie->useEncryption(false);

        $nameOne   = uniqid('nam-');
        $nameTwo   = uniqid('nam-');
        $nameThree = uniqid('nam-');
        $value     = uniqid('val-');
        $time      = time() + 86400;
        $path      = '/';
        $domain    = 'localhost';

        $cookie->set($nameOne, $value, $time, $path, false, $domain, true);
        $cookie->set($nameTwo, $value, $time, $path, false, $domain, false);
        $cookie->set($nameThree, $value, $time, $path, false, $domain);
        $cookie->send();

        $cookieOne   = $this->getCookie($nameOne);
        $cookieTwo   = $this->getCookie($nameTwo);
        $cookieThree = $this->getCookie($nameThree);

        $expected = 'HttpOnly';
        $I->assertStringContainsString($expected, $cookieOne);

        $expected = 'HttpOnly';
        $I->assertStringNotContainsString($expected, $cookieTwo);

        $expected = 'HttpOnly';
        $I->assertStringNotContainsString($expected, $cookieThree);
    }

    /**
     * Test Http\Response\Cookies - set() options parameter
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-10
     * @issue https://github.com/phalcon/cphalcon/issues/15129
     */
    public function httpCookieSetOptions(UnitTester $I)
    {
        $I->wantToTest('Http\Response\Cookies - set() options parameter');

        $I->checkExtensionIsLoaded('xdebug');

        $this->setDiService('crypt');

        $cookies = new Cookies();
        $cookies->setDI($this->container);
        $cookies->useEncryption(false);

        $nameOne   = uniqid('nam-');
        $nameTwo   = uniqid('nam-');
        $nameThree = uniqid('nam-');
        $nameFour  = uniqid('nam-');
        $value     = uniqid('val-');
        $time      = time() + 86400;
        $path      = '/';
        $domain    = 'localhost';

        $cookies->set(
            $nameOne,
            $value,
            $time,
            $path,
            false,
            $domain,
            false,
            [
                'samesite' => 'None',
            ]
        );
        $cookies->set(
            $nameTwo,
            $value,
            $time,
            $path,
            false,
            $domain,
            false,
            [
                'samesite' => 'Lax',
            ]
        );
        $cookies->set(
            $nameThree,
            $value,
            $time,
            $path,
            false,
            $domain,
            false,
            [
                'samesite' => 'Strict',
            ]
        );
        $cookies->set(
            $nameFour,
            $value,
            $time,
            $path,
            false,
            $domain,
            false
        );
        $cookies->send();

        $cookieOne   = $this->getCookie($nameOne);
        $cookieTwo   = $this->getCookie($nameTwo);
        $cookieThree = $this->getCookie($nameThree);
        $cookieFour  = $this->getCookie($nameFour);

        $expected = 'SameSite=None';
        $I->assertStringContainsString($expected, $cookieOne);

        $expected = 'SameSite=Lax';
        $I->assertStringContainsString($expected, $cookieTwo);

        $expected = 'SameSite=Strict';
        $I->assertStringContainsString($expected, $cookieThree);

        $expected = 'SameSite';
        $I->assertStringNotContainsString($expected, $cookieFour);
    }
}
