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

namespace Phalcon\Tests\Unit\Support\Collection\Collection;

use Phalcon\Support\Collection;
use UnitTester;

class CountCest
{
    /**
     * Tests Phalcon\Support\Collection :: count()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportCollectionCount(UnitTester $I)
    {
        $I->wantToTest('Support\Collection - count()');

        $data = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];

        $collection = new Collection($data);

        $expected = 3;
        $actual   = $collection->toArray();
        $I->assertCount($expected, $actual);

        $expected = 3;
        $actual   = $collection->count();
        $I->assertSame($expected, $actual);
    }
}
