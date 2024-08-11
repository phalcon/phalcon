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

namespace Phalcon\Tests\Unit\Image\Adapter\Imagick;

use Phalcon\Image\Adapter\Imagick;
use Phalcon\Tests\Fixtures\Traits\ImagickTrait;
use Phalcon\Tests\AbstractUnitTestCase;

use function dataDir;
use function outputDir;

final class LiquidRescaleTest extends AbstractUnitTestCase
{
    use ImagickTrait;

    /**
     * Tests Phalcon\Image\Adapter\Imagick :: liquidRescale()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2016-02-19
     */
    public function testImageAdapterImagickLiquidRescale(): void
    {
        $this->markTestSkipped('Check library support');

        $image = new Imagick(
            dataDir('assets/images/example-jpg.jpg')
        );

        $image->setResourceLimit(6, 1);

        // Resize to 200 pixels on the shortest side
        $image->liquidRescale(200, 200)
              ->save(outputDir('tests/image/imagick/liquidRescale.jpg'))
        ;

        $this->assertFileExists(
            outputDir('tests/image/imagick/liquidRescale.jpg')
        );

        $this->assertSame(
            200,
            $image->getWidth()
        );

        $this->assertSame(
            200,
            $image->getHeight()
        );

        $this->safeDeleteFile('liquidRescale.jpg');
    }
}
