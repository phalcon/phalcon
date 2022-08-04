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

namespace Phalcon\Tests\Unit\Image\Adapter\Gd;

use Phalcon\Image\Adapter\Gd;
use Phalcon\Tests\Fixtures\Traits\GdTrait;
use UnitTester;

class ResizeCest
{
    use GdTrait;

    /**
     * Tests Phalcon\Image\Adapter\Gd :: resize()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function imageAdapterGdResizeJpg(UnitTester $I)
    {
        $I->wantToTest('Image\Adapter\Gd - resize() - jpg image');

        $this->checkJpegSupport($I);

        $image = new Gd(
            dataDir('assets/images/example-jpg.jpg')
        );

        $outputDir = 'tests/image/gd';
        $output    = outputDir($outputDir . '/resize.jpg');
        $width     = 199;
        $height    = 76;
        $hash      = 'fbf9f3e3c3c1c183';

        // Resize to 200 pixels on the shortest side
        $image->resize($width, $height)
              ->save($output)
        ;

        $I->amInPath(outputDir($outputDir));
        $I->seeFileFound('resize.jpg');

        $expected = $width;
        $actual   = $image->getWidth();
        $I->assertSame($expected, $actual);

        $expected = $height;
        $actual   = $image->getHeight();
        $I->assertSame($expected, $actual);

        $actual = $this->checkImageHash($output, $hash);
        $I->assertTrue($actual);

        $I->safeDeleteFile('resize.jpg');
    }

    /**
     * Tests Phalcon\Image\Adapter\Gd :: resize()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function imageAdapterGdResizePng(UnitTester $I)
    {
        $I->wantToTest('Image\Adapter\Gd - resize() - png image');

        $image = new Gd(
            dataDir('assets/images/example-png.png')
        );

        $outputDir = 'tests/image/gd';
        $output    = outputDir($outputDir . '/resize.png');
        $width     = 50;
        $height    = 50;
        $hash      = 'bf9f8fc5bf9bc0d0';

        // Resize to 50 pixels on the shortest side
        $image->resize($width, $height)
              ->save($output)
        ;

        $I->amInPath(outputDir($outputDir));
        $I->seeFileFound('resize.png');

        $expected = $width;
        $actual   = $image->getWidth();
        $I->assertSame($expected, $actual);

        $expected = $height;
        $actual   = $image->getHeight();
        $I->assertSame($expected, $actual);

        $actual = $this->checkImageHash($output, $hash);
        $I->assertTrue($actual);

        $I->safeDeleteFile('resize.png');
    }
}
