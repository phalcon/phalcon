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

final class BackgroundTest extends AbstractUnitTestCase
{
    use ImagickTrait;

    /**
     * Tests Phalcon\Image\Adapter\Imagick :: background()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2016-02-19
     */
    public function testImageAdapterImagickBackground(): void
    {
        $image = new Imagick(
            dataDir('assets/images/example-jpg.jpg')
        );

        $image->setResourceLimit(6, 1);

        // Add a watermark to the bottom right of the image
        $image->background('#000')
              ->save(outputDir('tests/image/imagick/background.jpg'))
        ;

        $this->assertFileExists(
            outputDir('tests/image/imagick/background.jpg')
        );

        $this->assertGreaterThan(
            200,
            $image->getWidth()
        );

        $this->assertGreaterThan(
            200,
            $image->getHeight()
        );

        $this->safeDeleteFile('background.jpg');
    }
}
