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

final class GetSetSourcePathTest extends UnitTestCase
{
    use AssetsTrait;

    /**
     * Tests Phalcon\Assets\Asset :: getSourcePath()/setSourcePath()
     *
     * @dataProvider providerCssJsLocal
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testAssetsAssetGetSetSourcePath(
        string $type,
        string $path,
        bool $local
    ): void {
        $asset      = new Asset($type, $path, $local);
        $sourcePath = '/new/path';

        $asset->setSourcePath($sourcePath);
        $actual = $asset->getSourcePath();

        $this->assertSame($sourcePath, $actual);
    }
}
