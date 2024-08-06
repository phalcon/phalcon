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

use Phalcon\Assets\Asset\Js;
use Phalcon\Tests\Fixtures\Traits\AssetsTrait;
use Phalcon\Tests\UnitTestCase;

final class IsSetIsLocalTest extends UnitTestCase
{
    use AssetsTrait;

    /**
     * Tests Phalcon\Assets\Asset\Js :: isLocal()/setIsLocal()
     *
     * @dataProvider providerJsIsLocal
     *
     * @return void
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testAssetsAssetJsSetLocal(
        string $path,
        bool $local,
        bool $newLocal
    ): void {
        $asset = new Js($path, $local);

        $asset->setIsLocal($newLocal);
        $expected = $newLocal;
        $actual   = $asset->isLocal();
        $this->assertSame($expected, $actual);
    }
}
