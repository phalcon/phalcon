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

namespace Phalcon\Tests\Unit\Assets\Asset;

use Codeception\Example;
use Phalcon\Assets\Asset;
use Phalcon\Tests\Fixtures\Traits\AssetsTrait;
use Phalcon\Tests\UnitTestCase;

final class GetSetAttributesTest extends UnitTestCase
{
    use AssetsTrait;

    /**
     * Tests Phalcon\Assets\Asset :: getAttributes()/setAttributes()
     *
     * @dataProvider providerCssJs
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testAssetsAssetGetSetAttributes(
        string $type,
        string $path
    ): void {
        $asset      = new Asset($type, $path);
        $attributes = [
            'data-key' => 'phalcon',
        ];

        $asset->setAttributes($attributes);
        $actual = $asset->getAttributes();
        $this->assertSame($attributes, $actual);
    }
}
