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

class GetSetTarisLocalCest
{
    /**
     * Tests Phalcon\Assets\Collection :: getTarisLocal() / setTarisLocal()
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-02-15
     */
    public function assetsCollectionGetSetTarisLocal(UnitTester $I)
    {
        $I->wantToTest('Assets\Collection - getTarisLocal() / setTarisLocal()');

        $collection = new Collection();

        $I->assertEquals(true, $collection->getTarisLocal());

        $collection->setTarisLocal(false);

        $I->assertEquals(false, $collection->getTarisLocal());
    }
}
