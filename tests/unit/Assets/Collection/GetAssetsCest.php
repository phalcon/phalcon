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

use Phalcon\Assets\Asset;
use Phalcon\Assets\Collection;
use UnitTester;

/**
 * Class GetAssetsCest
 *
 * @package Phalcon\Tests\Unit\Assets\Collection
 */
class GetAssetsCest
{
    /**
     * Tests Phalcon\Assets\Collection :: getAssets()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function assetsCollectionGetAssets(UnitTester $I)
    {
        $I->wantToTest('Assets\Collection - getAssets()');

        $collection = new Collection();
        $asset      = new Asset('js', 'js/jquery.js');
        $collection->add($asset);
        $asset1 = new Asset('js', 'js/jquery-ui.js');
        $collection->add($asset1);

        $assets = $collection->getAssets();

        $I->assertCount(2, $assets);
        $I->assertEquals([$asset, $asset1], $assets);
    }
}
