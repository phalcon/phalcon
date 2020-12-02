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

namespace Phalcon\Tests\Unit\Assets\Collection;

use Phalcon\Assets\Collection;
use UnitTester;

/**
 * Class KeyNextRewindCest
 *
 * @package Phalcon\Tests\Unit\Assets\Collection
 */
class KeyNextRewindCest
{
    /**
     * Tests Phalcon\Assets\Collection :: key() / next() / rewind()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function assetsCollectionKeyNextRewind(UnitTester $I)
    {
        $I->wantToTest('Assets\Collection - key() / next() / rewind()');

        $collection = new Collection();
        $I->assertEquals(0, $collection->key());

        $collection->next();
        $I->assertEquals(1, $collection->key());

        $collection->rewind();
        $I->assertEquals(0, $collection->key());
    }
}
