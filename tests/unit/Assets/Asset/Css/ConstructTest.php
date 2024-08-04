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

namespace Phalcon\Tests\Unit\Assets\Asset\Css;

use Codeception\Example;
use Phalcon\Assets\Asset\Css;
use Phalcon\Tests\Fixtures\Traits\AssetsTrait;
use Phalcon\Tests\UnitTestCase;

final class ConstructTest extends UnitTestCase
{
    use AssetsTrait;

    /**
     * Tests Phalcon\Assets\Asset\Css :: __construct() - local
     *
     * @dataProvider providerCss
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testAssetsAssetCssConstructLocal(
        string $path,
        bool $local
    ): void {
        $asset = new Css($path, $local);

        $expected = $local;
        $actual   = $asset->isLocal();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Assets\Asset\Css :: __construct() - filter
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAssetsAssetCssConstructFilter(): void
    {
        $asset  = new Css('css/docs.css');
        $actual = $asset->getFilter();
        $this->assertTrue($actual);
    }

    /**
     * Tests Phalcon\Assets\Asset\Css :: __construct() - filter set
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAssetsAssetCssConstructFilterSet(): void
    {
        $asset  = new Css('css/docs.css', true, false);
        $actual = $asset->getFilter();
        $this->assertFalse($actual);
    }

    /**
     * Tests Phalcon\Assets\Asset\Css :: __construct() - attributes
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAssetsAssetCssConstructAttributes(): void
    {
        $asset = new Css('css/docs.css');

        $expected = [];
        $actual   = $asset->getAttributes();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Assets\Asset\Css :: __construct() - attributes set
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAssetsAssetCssConstructAttributesSet(): void
    {
        $attributes = [
            'data' => 'phalcon',
        ];

        $asset = new Css(
            'css/docs.css',
            true,
            true,
            $attributes
        );

        $actual = $asset->getAttributes();
        $this->assertSame($attributes, $actual);
    }
}
