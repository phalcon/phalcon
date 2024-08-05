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

use Phalcon\Assets\Asset;
use Phalcon\Tests\UnitTestCase;

final class GetSetTypeTest extends UnitTestCase
{
    /**
     * @return string[][]
     */
    public static function provider(): array
    {
        return [
            [
                'type'    => 'css',
                'path'    => 'css/docs.css',
                'newType' => 'js',
            ],
            [
                'type'    => 'css',
                'path'    => 'js/jquery.js',
                'newType' => 'js',
            ],
        ];
    }

    /**
     * Tests Phalcon\Assets\Asset :: getType()/setType()
     *
     * @dataProvider provider
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testAssetsAssetSetType(
        string $type,
        string $path,
        string $newType
    ): void {
        $asset = new Asset($type, $path);

        $asset->setType($newType);
        $expected = $newType;
        $actual   = $asset->getType();
        $this->assertSame($expected, $actual);
    }
}
