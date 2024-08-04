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

namespace Phalcon\Tests\Unit\Assets\Asset\Js;

use Codeception\Example;
use Phalcon\Assets\Asset\Js;
use Phalcon\Tests\Fixtures\Traits\AssetsTrait;
use Phalcon\Tests\UnitTestCase;

final class GetSetTargetPathTest extends UnitTestCase
{
    use AssetsTrait;

    /**
     * Tests Phalcon\Assets\Asset\Js :: getTargetPath()/setTargetPath()
     *
     * @dataProvider providerJs
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testAssetsAssetJsGetSetTargetPath(
        string $path,
        bool $local
    ): void {
        $asset = new Js($path, $local);

        $targetPath = '/phalcon/path';
        $asset->setTargetPath($targetPath);
        $actual = $asset->getTargetPath();

        $this->assertSame($targetPath, $actual);
    }
}
