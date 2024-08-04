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

final class GetRealTargetUriTest extends UnitTestCase
{
    use AssetsTrait;

    /**
     * Tests Phalcon\Assets\Asset :: getRealTargetUri() - local
     *
     * @dataProvider providerCssJsTargetUri
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testAssetsAssetGetRealTargetUri(
        string $type,
        string $path,
        bool $local,
        string $targetUri,
        string $expected
    ): void {
        $asset = new Asset($type, $path, $local);
        $asset->setTargetUri($targetUri);

        $actual = $asset->getRealTargetUri();
        $this->assertSame($expected, $actual);
    }
}
