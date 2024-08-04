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

final class IsSetIsLocalTest extends UnitTestCase
{
    use AssetsTrait;

    /**
     * Tests Phalcon\Assets\Asset\Css :: isLocal()/setIsLocal()
     *
     * @dataProvider providerCssIsLocal
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testAssetsAssetCssSetLocal(
        string $path,
        bool $local,
        bool $newLocal
    ): void {
        $asset = new Css($path, $local);

        $asset->setIsLocal($newLocal);
        $expected = $newLocal;
        $actual   = $asset->isLocal();
        $this->assertSame($expected, $actual);
    }
}
