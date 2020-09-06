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

namespace Phalcon\Tests\Unit\Helper\Str;

use Phalcon\Helper\Str;
use UnitTester;

class LenCest
{
    /**
     * Tests Phalcon\Helper\Str :: len()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-23
     */
    public function helperStrLen(UnitTester $I)
    {
        $I->wantToTest('Helper\Str - len()');

        $actual = Str::len('hello');
        $I->assertEquals(5, $actual);

        $actual = Str::len('1234');
        $I->assertEquals(4, $actual);
    }

    /**
     * Tests Phalcon\Helper\Str :: len() - multi-bytes encoding
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-23
     */
    public function helperStrLenMultiBytesEncoding(UnitTester $I)
    {
        $I->wantToTest('Helper\Str - len() - multi byte encoding');
        $actual = Str::len('привет мир!');
        $I->assertEquals(11, $actual);

        $actual = Str::len('männer');
        $I->assertEquals(6, $actual);
    }
}
