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

namespace Phalcon\Tests\Unit\Assets\Inline;

use Codeception\Example;
use Phalcon\Assets\Inline;
use Phalcon\Tests\Fixtures\Traits\AssetsTrait;
use Phalcon\Tests\UnitTestCase;

use function hash;

final class GetAssetKeyTest extends UnitTestCase
{
    use AssetsTrait;

    /**
     * Tests Phalcon\Assets\Inline :: getAssetKey()
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2018-11-13
     *
     * @dataProvider providerInlineCssJs
     */
    public function testAssetsInlineGetAssetKey(
        string $type,
        string $content
    ): void {
        $asset = new Inline(
            $type,
            $content
        );

        $expected = hash(
            "sha256",
            $type . ':' . $content
        );

        $this->assertSame(
            $expected,
            $asset->getAssetKey()
        );
    }
}
