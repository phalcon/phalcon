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
 * Class GetSetTargetLocalCest
 *
 * @package Phalcon\Tests\Unit\Assets\Collection
 */
class GetSetTargetLocalCest
{
    /**
     * Tests Phalcon\Assets\Collection :: getTargetLocal()/setTargetLocal()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function assetsCollectionGetSetTarisLocal(UnitTester $I)
    {
        $I->wantToTest('Assets\Collection - getTargetLocal()/setTargetLocal()');

        $collection = new Collection();
        $I->assertEquals(true, $collection->getTargetLocal());

        $collection->setTargetLocal(false);
        $I->assertEquals(false, $collection->getTargetLocal());
    }
}
