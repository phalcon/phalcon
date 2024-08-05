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
use Phalcon\Assets\Exception;
use Phalcon\Tests\Fixtures\Assets\AssetFileExistsFixture;
use Phalcon\Tests\Fixtures\Assets\AssetFileGetContentsFixture;
use Phalcon\Tests\Fixtures\Traits\AssetsTrait;
use Phalcon\Tests\UnitTestCase;

use function dataDir;
use function file_get_contents;

use const PHP_EOL;

final class GetContentTest extends UnitTestCase
{
    use AssetsTrait;

    /**
     * Tests Phalcon\Assets\Asset :: getContent()
     *
     * @dataProvider providerCssJs
     *
     * @param Example $example
     *
     * @return void
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testAssetsAssetGetContent(
        string $type,
        string $path
    ): void {
        $asset = new Asset($type, $path);

        $expected = file_get_contents(dataDir($path));
        $expected = str_replace("\r\n", PHP_EOL, $expected);
        $actual   = $asset->getContent(dataDir());
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Assets\Asset :: getContent() - exception 404
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAssetsAssetGetContentException404(): void
    {
        $file    = 'assets/assets/1198.css';
        $message = "Asset's content for '" . dataDir($file) . "' cannot be read";
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($message);

        $asset = new AssetFileExistsFixture('css', $file);
        $asset->getContent(dataDir());
    }

    /**
     * Tests Phalcon\Assets\Asset :: getContent() - exception cannot read file
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAssetsAssetGetContentExceptionCannotReadFile(): void
    {
        $file    = 'assets/assets/1198.css';
        $message = "Asset's content for '" . dataDir($file) . "' cannot be read";
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($message);

        $asset = new AssetFileGetContentsFixture('css', $file);
        $asset->getContent(dataDir());
    }
}
