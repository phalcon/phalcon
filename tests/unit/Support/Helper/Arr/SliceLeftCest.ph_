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

use Phalcon\Support\Arr\SliceLeft;
use UnitTester;

/**
 * Class SliceLeftCest
 *
 * @package Phalcon\Tests\Unit\Support\Arr
 */
class SliceLeftCest
{
    /**
     * Tests Phalcon\Support\Arr :: sliceLeft()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportArrSliceLeft(UnitTester $I)
    {
        $I->wantToTest('Support\Arr - sliceLeft()');

        $object     = new SliceLeft();
        $collection = [
            'Phalcon',
            'Framework',
            'for',
            'PHP',
        ];

        $expected = [
            'Phalcon',
        ];
        $actual   = $object($collection, 1);
        $I->assertEquals($expected, $actual);

        $expected = [
            'Phalcon',
            'Framework',
            'for',
        ];
        $actual   = $object($collection, 3);
        $I->assertEquals($expected, $actual);
    }
}
