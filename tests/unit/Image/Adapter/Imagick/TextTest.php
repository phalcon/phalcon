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
use Phalcon\Tests\UnitTestCase;

use function dataDir;
use function outputDir;

final class TextTest extends UnitTestCase
{
    use ImagickTrait;

    /**
     * Tests Phalcon\Image\Adapter\Imagick :: text()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2016-02-19
     */
    public function testImageAdapterImagickText(): void
    {
        $image = new Imagick(
            dataDir('assets/images/example-jpg.jpg')
        );

        $image->setResourceLimit(6, 1);

        $image->text(
            'Phalcon',
            10,
            10,
            100,
            '000099',
            12,
            dataDir('assets/fonts/Roboto-Thin.ttf')
        )
              ->save(outputDir('tests/image/imagick/text.jpg'))
        ;

        $this->assertFileExists(
            outputDir('tests/image/imagick/text.jpg')
        );

        $this->assertSame(
            1820,
            $image->getWidth()
        );
        $this->assertSame(
            694,
            $image->getHeight()
        );

        $this->safeDeleteFile('text.jpg');
    }
}
