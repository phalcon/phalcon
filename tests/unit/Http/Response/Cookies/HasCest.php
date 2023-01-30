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

class HasCest extends HttpBase
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
     * Tests Phalcon\Http\Response\Cookies :: has()
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-01-06
     */
    public function httpResponseCookiesHas(UnitTester $I)
    {
        $I->wantToTest('Http\Response\Cookies - has()');

        $name    = uniqid('nam-');
        $value   = uniqid('val-');
        $unknown = uniqid('unk-');

        $this->setDiService('crypt');

        $cookies = new Cookies();
        $cookies->setDI($this->container);
        $cookies->set($name, $value);

        $actual = $cookies->has($name);
        $I->assertTrue($actual);

        $actual = $cookies->has($unknown);
        $I->assertFalse($actual);
    }
}
