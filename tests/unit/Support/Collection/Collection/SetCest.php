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

class SetCest
{
    /**
     * Tests Phalcon\Collection :: set()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportCollectionSet(UnitTester $I)
    {
        $I->wantToTest('Support\Collection - set()');

        $collection = new Collection();

        $collection->set('three', 'two');

        $I->assertEquals(
            'two',
            $collection->get('three')
        );

        $collection->three = 'Phalcon';

        $I->assertEquals(
            'Phalcon',
            $collection->get('three')
        );

        $collection->offsetSet('three', 123);

        $I->assertEquals(
            123,
            $collection->get('three')
        );


        $collection['three'] = true;

        $I->assertTrue(
            $collection->get('three')
        );
    }
}
