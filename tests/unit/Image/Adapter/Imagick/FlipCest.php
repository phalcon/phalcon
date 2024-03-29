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
use Phalcon\Image\Enum;
use Phalcon\Tests\Fixtures\Traits\ImagickTrait;
use UnitTester;

use function dataDir;
use function outputDir;

class FlipCest
{
    use ImagickTrait;

    /**
     * Tests Phalcon\Image\Adapter\Imagick :: flip()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2016-02-19
     */
    public function imageAdapterImagickFlip(UnitTester $I)
    {
        $I->wantToTest('Image\Adapter\Imagick - flip()');

        $image = new Imagick(
            dataDir('assets/images/example-jpg.jpg')
        );

        $image->setResourceLimit(6, 1);

        // Flip the image from top to bottom
        $image->flip(Enum::HORIZONTAL)
              ->save(outputDir('tests/image/imagick/flip.jpg'))
        ;

        $I->amInPath(
            outputDir('tests/image/imagick/')
        );

        $I->seeFileFound('flip.jpg');

        $I->assertGreaterThan(
            200,
            $image->getWidth()
        );

        $I->assertGreaterThan(
            200,
            $image->getHeight()
        );

        $I->safeDeleteFile('flip.jpg');
    }
}
