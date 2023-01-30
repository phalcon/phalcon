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

use Phalcon\Http\Cookie\Exception;
use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use UnitTester;

class SetSignKeyCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Cookie :: setSignKey() - exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function httpCookieSetSignKeyException(UnitTester $I)
    {
        $I->wantToTest('Http\Cookie - setSignKey() - exception');

        $this->setDiService('sessionStream');

        $I->expectThrowable(
            new Exception(
                "The cookie's key should be at least 32 characters " .
                "long. Current length is 5."
            ),
            function () {
                $cookie = $this->getCookieObject();
                $cookie->setSignKey('12345');
            }
        );
    }
}
