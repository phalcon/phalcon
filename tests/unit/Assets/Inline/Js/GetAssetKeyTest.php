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

namespace Phalcon\Tests\Unit\Assets\Inline\Js;

use Phalcon\Assets\Inline\Js;
use Phalcon\Tests\UnitTestCase;

use function hash;

final class GetAssetKeyTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Assets\Inline\Js :: getAssetKey()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAssetsInlineJsGetAssetKey(): void
    {
        $content = '<script>alert("Hello");</script>';
        $asset   = new Js($content);

        $expected = hash("sha256", 'js:' . $content);
        $actual   = $asset->getAssetKey();
        $this->assertSame($expected, $actual);
    }
}
