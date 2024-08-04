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

final class GetSetTargetUriTest extends UnitTestCase
{
    use AssetsTrait;

    /**
     * Tests Phalcon\Assets\Asset :: setTargetUri() - local
     *
     * @dataProvider providerCssJsLocal
     *
     * @return void
     * @param Example    $example
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testAssetsAssetSetTargetUriJsLocal(
        string $type,
        string $path,
        bool $local
    ): void {
        $asset     = new Asset($type, $path, $local);
        $targetUri = '/new/path';

        $asset->setTargetUri($targetUri);
        $actual = $asset->getTargetUri();
        $this->assertSame($targetUri, $actual);
    }
}
