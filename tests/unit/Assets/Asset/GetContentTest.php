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

use Phalcon\Assets\Asset;
use Phalcon\Assets\Exception;
use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Assets\Fake\AssetsTrait;
use Phalcon\Tests\Unit\Assets\Fake\FakeAssetFileExists;
use Phalcon\Tests\Unit\Assets\Fake\FakeAssetFileGetContents;
use PHPUnit\Framework\Attributes\DataProvider;

use function file_get_contents;
use function supportDir;

use const PHP_EOL;

final class GetContentTest extends AbstractUnitTestCase
{
    use AssetsTrait;

    /**
     * Tests Phalcon\Assets\Asset :: getContent()
     *
     * @return void
     *
     * @dataProvider providerAssets
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    #[DataProvider('providerAssets')]
    public function testAssetsAssetGetContent(
        string $type,
        string $path
    ): void {
        $asset = new Asset($type, $path);

        $expected = file_get_contents(supportDir($path));
        $expected = str_replace("\r\n", PHP_EOL, $expected);
        $actual   = $asset->getContent(supportDir());
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
        $message = "Asset's content for '" . supportDir($file) . "' cannot be read";

        $this->expectException(Exception::class);
        $this->expectExceptionMessage($message);

        $asset = new FakeAssetFileExists('css', $file);
        $asset->getContent(supportDir());
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
        $message = "Asset's content for '" . supportDir($file) . "' cannot be read";
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($message);

        $asset = new FakeAssetFileGetContents('css', $file);
        $asset->getContent(supportDir());
    }
}
