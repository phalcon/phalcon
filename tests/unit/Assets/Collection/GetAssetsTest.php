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
use Phalcon\Tests\UnitTestCase;

final class GetAssetsTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Assets\Collection :: getAssets()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAssetsCollectionGetAssets(): void
    {
        $collection = new Collection();
        $asset1     = new Asset('js', 'js/jquery.js');
        $key1       = $asset1->getAssetKey();
        $collection->add($asset1);

        $asset2 = new Asset('js', 'js/jquery-ui.js');
        $key2   = $asset2->getAssetKey();
        $collection->add($asset2);

        $assets = $collection->getAssets();

        $this->assertCount(2, $assets);
        $expected = [$key1 => $asset1, $key2 => $asset2];
        $this->assertSame($expected, $assets);
    }
}
