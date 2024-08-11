<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Assets\Asset\Js;

use Phalcon\Assets\Asset\Js;
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

use function dataDir;

final class GetRealSourcePathTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Assets\Asset\Js :: getRealSourcePath() - js local
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testAssetsAssetJsGetRealSourcePathLocal(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Need to fix Windows new lines...');
        }

        $file  = dataDir('assets/assets/jquery.js');
        $asset = new Js($file);

        $expected = $file;
        $actual   = $asset->getRealSourcePath();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Assets\Asset\Js :: getRealSourcePath() - js local -
     * does not exist
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testAssetsAssetJsGetRealSourcePathLocalDoesNotExist(): void
    {
        $asset  = new Js('js/jquery.js');
        $actual = $asset->getRealSourcePath();
        $this->assertEmpty($actual);
    }

    /**
     * Tests Phalcon\Assets\Asset\Js :: getRealSourcePath() - remote
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testAssetsAssetJsGetRealSourcePathRemote(): void
    {
        $path  = 'https://phalcon.ld/js/jquery.js';
        $asset = new Js($path, false);

        $expected = $path;
        $actual   = $asset->getRealSourcePath();
        $this->assertSame($expected, $actual);
    }
}
