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

use function uniqid;

class DeleteCest extends HttpBase
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
     * Tests Phalcon\Http\Response\Cookies :: delete()
     *
     * @param UnitTester $I
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-01-06
     */
    public function httpResponseCookiesDelete(UnitTester $I)
    {
        $I->wantToTest('Http\Response\Cookies - delete()');

        $name  = uniqid('nam-');
        $value = uniqid('val-');

        $this->setDiService('crypt');

        $cookies = new Cookies();
        $cookies->setDI($this->container);
        $cookies->set($name, $value);

        $expected = $value;
        $actual   = (string)$cookies->get($name);
        $I->assertSame($expected, $actual);

        $cookies->delete($name);

        $expected = '';
        $actual   = (string)$cookies->get($name);
        $I->assertSame($expected, $actual);
    }
}
