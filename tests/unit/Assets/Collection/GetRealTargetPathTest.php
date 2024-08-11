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

namespace Phalcon\Tests\Unit\Assets\Collection;

use Phalcon\Assets\Collection;
use Phalcon\Tests\Fixtures\Assets\CollectionFileExistsFixture;
use Phalcon\Tests\AbstractUnitTestCase;

use PHPUnit\Framework\Attributes\Test;

use function dataDir;

final class GetRealTargetPathTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Assets\Collection :: getRealTargetPath()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testAssetsCollectionGetRealTargetPath(): void
    {
        $collection        = new Collection();
        $targetPath        = '/assets';
        $basePath          = dataDir('assets');
        $constructRealPath = realpath($basePath . $targetPath);

        $collection->setTargetPath($targetPath);
        $realBasePath = $collection->getRealTargetPath($basePath);

        $this->assertSame($constructRealPath, $realBasePath);
    }

    /**
     * Tests Phalcon\Assets\Collection :: getRealTargetPath() - file does not
     * exist
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testAssetsCollectionGetRealTargetPathFileDoesNotExist(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Need to fix Windows new lines...');
        }

        $collection        = new CollectionFileExistsFixture();
        $targetPath        = '/assets';
        $basePath          = dataDir('assets');
        $constructRealPath = realpath($basePath . $targetPath);

        $collection->setTargetPath($targetPath);
        $realBasePath = $collection->getRealTargetPath($basePath);

        $this->assertSame($constructRealPath, $realBasePath);
    }
}
