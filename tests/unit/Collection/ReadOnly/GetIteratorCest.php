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

class GetIteratorCest
{
    /**
     * Tests Phalcon\Collection\ReadOnly :: getIterator()
     *
     * @param UnitTester $I
     *
     * @since  2020-09-09
     *
     * @author Phalcon Team <team@phalcon.io>
     */
    public function collectionGetIterator(UnitTester $I)
    {
        $I->wantToTest('ReadOnly - getIterator()');

        $data = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];

        $collection = new ReadOnly($data);

        foreach ($collection as $key => $value) {
            $I->assertEquals(
                $data[$key],
                $collection[$key]
            );
        }
    }
}
