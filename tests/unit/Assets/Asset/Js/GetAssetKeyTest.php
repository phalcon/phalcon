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
use Phalcon\Tests\AbstractUnitTestCase;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

use function hash;

final class GetAssetKeyTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Assets\Asset\Js :: getAssetKey()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testAssetsAssetJsGetAssetKey(): void
    {
        $path     = 'js/jquery.js';
        $asset    = new Js($path);
        $expected = hash("sha256", 'js:' . $path);
        $actual   = $asset->getAssetKey();

        $this->assertSame($expected, $actual);
    }
}
