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

namespace Phalcon\Tests\Unit\Collection\ReadOnly;

use Phalcon\Collection\ReadOnly;
use UnitTester;

class GetCest
{
    /**
     * Tests Phalcon\Collection\ReadOnly :: get()
     *
     * @param UnitTester $I
     *
     * @since  2020-09-09
     *
     * @author Phalcon Team <team@phalcon.io>
     */
    public function collectionGet(UnitTester $I)
    {
        $I->wantToTest('ReadOnly - get()');

        $data = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];

        $collection = new ReadOnly($data);

        $expected = 'four';

        $I->assertEquals(
            $expected,
            $collection->get('three')
        );

        $I->assertEquals(
            $expected,
            $collection->get('THREE')
        );

        $I->assertEquals(
            $expected,
            $collection->get('unknown', 'four')
        );

        $I->assertEquals(
            $expected,
            $collection['three']
        );

        $I->assertEquals(
            $expected,
            $collection->three
        );

        $I->assertEquals(
            $expected,
            $collection->offsetGet('three')
        );
    }
}
