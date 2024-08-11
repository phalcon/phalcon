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

namespace Phalcon\Tests\Unit\Assets\Manager;

use Phalcon\Assets\Asset;
use Phalcon\Assets\Asset\Css;
use Phalcon\Assets\Asset\Js;
use Phalcon\Assets\AssetInterface;
use Phalcon\Assets\Manager;
use Phalcon\Html\Escaper;
use Phalcon\Html\TagFactory;
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Test;

final class CollectionAssetsByTypeTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Assets\Manager :: collectionAssetsByType()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-19
     */
    #[Test]
    public function testAssetsManagerCollectionAssetsByType(): void
    {
        $manager = new Manager(new TagFactory(new Escaper()));
        $assets  = [
            new Css('/scripts/style1.css'),
            new Css('/scripts/style2.css'),
            new Css('/scripts/style3.css'),
            new Js('/scripts/js1.css'),
            new Js('/scripts/js2.css'),
        ];

        $filtered = $manager->collectionAssetsByType($assets, 'css');
        $this->assertCount(3, $filtered);
        foreach ($filtered as $asset) {
            $this->assertInstanceOf(Css::class, $asset);
            $this->assertInstanceOf(Asset::class, $asset);
            $this->assertInstanceOf(AssetInterface::class, $asset);
        }
    }
}
