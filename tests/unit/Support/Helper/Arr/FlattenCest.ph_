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

namespace Phalcon\Tests\Unit\Support\Arr;

use Phalcon\Support\Arr\Flatten;
use UnitTester;

/**
 * Class FlattenCest
 *
 * @package Phalcon\Tests\Unit\Support\Arr
 */
class FlattenCest
{
    /**
     * Tests Phalcon\Support\Arr :: flatten()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportArrFlatten(UnitTester $I)
    {
        $I->wantToTest('Support\Arr - flatten()');

        $object = new Flatten();
        $source = [1, [2], [[3], 4], 5];

        $expected = [1, 2, [3], 4, 5];
        $actual   = $object($source);
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Arr :: flatten() - deep
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportArrFlattenDeep(UnitTester $I)
    {
        $I->wantToTest('Support\Arr - flatten() - deep');

        $object = new Flatten();
        $source = [1, [2], [[3], 4], 5];

        $expected = [1, 2, 3, 4, 5];
        $actual   = $object($source, true);
        $I->assertEquals($expected, $actual);
    }
}
