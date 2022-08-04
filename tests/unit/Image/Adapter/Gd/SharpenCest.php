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

class SharpenCest
{
    use GdTrait;

    /**
     * Tests Phalcon\Image\Adapter\Gd :: sharpen()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function imageAdapterGdSharpen(UnitTester $I)
    {
        $I->wantToTest('Image\Adapter\Gd - sharpen()');

        $this->checkJpegSupport($I);

        $outputDir = 'tests/image/gd';
        $params    = [
            [10, 'fbf9f3e3c3c18183'],
            [50, 'fbf9f3e3c3c18183'],
            [100, 'fbf9f7e3c3c1c183'],
        ];
        $i         = 0;

        foreach ($params as [$amount, $hash]) {
            $image = new Gd(
                dataDir('assets/images/example-jpg.jpg')
            );

            $outputImage = $i++ . 'sharpen.jpg';
            $output      = outputDir($outputDir . '/' . $outputImage);

            $image->sharpen($amount)
                  ->save($output)
            ;

            $I->amInPath(
                outputDir($outputDir)
            );

            $I->seeFileFound($outputImage);

            $I->assertTrue(
                $this->checkImageHash($output, $hash)
            );

            $I->safeDeleteFile($outputImage);
        }
    }
}
