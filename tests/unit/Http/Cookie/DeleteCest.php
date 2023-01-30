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

use Phalcon\Http\Cookie;
use UnitTester;

use function uniqid;

class DeleteCest
{
    /**
     * Tests Phalcon\Http\Cookie :: delete()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function httpCookieDelete(UnitTester $I)
    {
        $I->wantToTest('Http\Cookie - delete()');

        $name  = uniqid('nam-');
        $value = uniqid('val-');

        $cookie = new Cookie($name);

        $actual = $cookie->getValue();
        $I->assertNull($actual);

        $cookie->setValue($value);

        $expected = $value;
        $actual   = $cookie->getValue();
        $I->assertSame($expected, $actual);

        $cookie->delete();

        $actual = $cookie->getValue();
        $I->assertNull($actual);
    }
}
