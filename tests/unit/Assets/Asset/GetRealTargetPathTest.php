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
use Phalcon\Tests\Fixtures\Assets\AssetFileExistsPositiveFixture;
use Phalcon\Tests\Fixtures\Traits\AssetsTrait;
use Phalcon\Tests\UnitTestCase;

use function dataDir;

final class GetRealTargetPathTest extends UnitTestCase
{
    use AssetsTrait;

    /**
     * Tests Phalcon\Assets\Asset :: getRealTargetPath() - css local
     *
     * @dataProvider providerCssJsLocal
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testAssetsAssetGetRealTargetPath(
        string $type,
        string $path,
        bool $local
    ): void {
        $asset = new Asset($type, $path, $local);

        $expected = $path;
        $actual   = $asset->getRealTargetPath();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Assets\Asset :: getRealTargetPath() - css local 404
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testAssetsAssetGetRealTargetPath404(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Need to fix Windows new lines...');
        }

        $file  = 'assets/assets/1198.css';
        $asset = new AssetFileExistsPositiveFixture('css', $file);

        $expected = dataDir($file);
        $actual   = $asset->getRealTargetPath(dataDir());
        $this->assertSame($expected, $actual);
    }
}
