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

namespace Phalcon\Tests\Unit\Support\Str;

use Phalcon\Support\Str\Len;
use UnitTester;

class LenCest
{
    /**
     * Tests Phalcon\Support\Str :: len()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrLen(UnitTester $I)
    {
        $I->wantToTest('Support\Str - len()');

        $object = new Len();
        $actual = $object('hello');
        $I->assertEquals(5, $actual);

        $actual = $object('1234');
        $I->assertEquals(4, $actual);
    }

    /**
     * Tests Phalcon\Support\Str :: len() - multi-bytes encoding
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrLenMultiBytesEncoding(UnitTester $I)
    {
        $I->wantToTest('Support\Str - len() - multi byte encoding');

        $object = new Len();
        $actual = $object('привет мир!');
        $I->assertEquals(11, $actual);

        $actual = $object('männer');
        $I->assertEquals(6, $actual);
    }
}
