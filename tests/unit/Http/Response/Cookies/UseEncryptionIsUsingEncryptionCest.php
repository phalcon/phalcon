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

class UseEncryptionIsUsingEncryptionCest extends HttpBase
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
     * Tests Phalcon\Http\Response\Cookies :: useEncryption /
     * isUsingEncryption()
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-01-06
     */
    public function httpResponseCookiesUseEncryptionIsUsingEncryption(
        UnitTester $I
    ) {
        $I->wantToTest(
            'Http\Response\Cookies - useEncryption / isUsingEncryption()'
        );

        $this->setDiService('crypt');

        $cookies = new Cookies(false);
        $cookies->setDI($this->container);

        $actual = $cookies->isUsingEncryption();
        $I->assertFalse($actual);

        $cookies->useEncryption(true);

        $actual = $cookies->isUsingEncryption();
        $I->assertTrue($actual);
    }
}
