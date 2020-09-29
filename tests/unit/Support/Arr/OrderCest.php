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

use Phalcon\Support\Arr\Order;
use UnitTester;

/**
 * Class OrderCest
 *
 * @package Phalcon\Tests\Unit\Support\Arr
 */
class OrderCest
{
    /**
     * Tests Phalcon\Support\Arr :: order()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportArrOrder(UnitTester $I)
    {
        $I->wantToTest('Support\Arr - order()');

        $object     = new Order();
        $collection = [
            [
                'id'   => 2,
                'name' => 'Paul',
            ],
            [
                'id'   => 3,
                'name' => 'Peter',
            ],
            [
                'id'   => 1,
                'name' => 'John',
            ],
        ];

        $expected = [
            [
                'id'   => 1,
                'name' => 'John',
            ],
            [
                'id'   => 2,
                'name' => 'Paul',
            ],
            [
                'id'   => 3,
                'name' => 'Peter',
            ],
        ];
        $actual   = $object($collection, 'id');
        $I->assertEquals($expected, $actual);

        $expected = [
            [
                'id'   => 3,
                'name' => 'Peter',
            ],
            [
                'id'   => 2,
                'name' => 'Paul',
            ],
            [
                'id'   => 1,
                'name' => 'John',
            ],
        ];
        $actual   = $object($collection, 'id', $object::ORDER_DESC);
        $I->assertEquals($expected, $actual);

        $collection = [
            (object) [
                'id'   => 2,
                'name' => 'Paul',
            ],
            (object) [
                'id'   => 3,
                'name' => 'Peter',
            ],
            (object) [
                'id'   => 1,
                'name' => 'John',
            ],
        ];

        $expected = [
            (object) [
                'id'   => 1,
                'name' => 'John',
            ],
            (object) [
                'id'   => 2,
                'name' => 'Paul',
            ],
            (object) [
                'id'   => 3,
                'name' => 'Peter',
            ],
        ];
        $actual   = $object($collection, 'id');
        $I->assertEquals($expected, $actual);
    }
}
