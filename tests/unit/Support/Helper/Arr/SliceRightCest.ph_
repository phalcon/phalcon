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

use Phalcon\Support\Arr\SliceRight;
use UnitTester;

/**
 * Class SliceRightCest
 *
 * @package Phalcon\Tests\Unit\Support\Arr
 */
class SliceRightCest
{
    /**
     * Tests Phalcon\Support\Arr :: sliceRight()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportArrSliceRight(UnitTester $I)
    {
        $I->wantToTest('Support\Arr - sliceRight()');

        $object     = new SliceRight();
        $collection = [
            'Phalcon',
            'Framework',
            'for',
            'PHP',
        ];

        $expected = [
            'Framework',
            'for',
            'PHP',
        ];
        $actual   = $object($collection, 1);
        $I->assertEquals($expected, $actual);

        $expected = [
            'PHP',
        ];
        $actual   = $object($collection, 3);
        $I->assertEquals($expected, $actual);
    }
}
