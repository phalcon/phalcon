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

class UseEncryptionIsCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Cookie :: useEncryption()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function httpCookieUseEncryption(UnitTester $I)
    {
        $I->wantToTest('Http\Cookie - useEncryption()');

        $this->setDiService('sessionStream');

        $cookie = $this->getCookieObject();

        $I->assertFalse($cookie->isUsingEncryption());

        $cookie->useEncryption(true);

        $actual = $cookie->isUsingEncryption();
        $I->assertTrue($actual);
    }
}
