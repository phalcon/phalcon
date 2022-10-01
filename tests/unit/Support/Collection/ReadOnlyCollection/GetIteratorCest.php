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

namespace Phalcon\Tests\Unit\Support\Collection\ReadOnlyCollection;

use Phalcon\Support\Collection\ReadOnlyCollection;
use UnitTester;

class GetIteratorCest
{
    /**
     * Tests Phalcon\Support\Collection\ReadOnlyCollection :: getIterator()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportCollectionGetIterator(UnitTester $I)
    {
        $I->wantToTest('Support\Collection\ReadOnlyCollection - getIterator()');

        $data = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];

        $collection = new ReadOnlyCollection($data);

        foreach ($collection as $key => $value) {
            $expected = $data[$key];
            $actual   = $collection[$key];
            $I->assertSame($expected, $actual);
        }
    }
}
