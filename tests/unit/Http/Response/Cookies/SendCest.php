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

class SendCest extends HttpBase
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
     * Tests Phalcon\Http\Response\Cookies :: send()
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-01-06
     */
    public function httpResponseCookiesSend(UnitTester $I)
    {
        $I->wantToTest('Http\Response\Cookies - send()');

        $name  = uniqid('nam-');
        $value = uniqid('val-');

        $this->setDiService('crypt');

        $cookies = new Cookies();
        $cookies->setDI($this->container);
        $cookies->set($name, $value);

        $actual = $cookies->send();
        $I->assertTrue($actual);
    }

    /**
     * Tests Phalcon\Http\Response\Cookies :: send() - twice
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-04-22
     * @issue  15334
     */
    public function httpResponseCookiesSendTwice(UnitTester $I)
    {
        $I->wantToTest('Http\Response\Cookies - send() - twice');

        $name  = uniqid('nam-');
        $value = uniqid('val-');

        $this->setDiService('crypt');

        $cookies = new Cookies();
        $cookies->setDI($this->container);
        $cookies->set($name, $value);

        $actual = $cookies->isSent();
        $I->assertFalse($actual);

        $actual = $cookies->send();
        $I->assertTrue($actual);

        $actual = $cookies->isSent();
        $I->assertTrue($actual);

        $actual = $cookies->send();
        $I->assertFalse($actual);
    }
}
