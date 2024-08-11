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
use Phalcon\Tests\AbstractUnitTestCase;

use const IMAGETYPE_GIF;
use const IMAGETYPE_JPEG;
use const IMAGETYPE_PNG;
use const IMAGETYPE_WBMP;
use const IMAGETYPE_WEBP;
use const IMAGETYPE_XBM;

final class GetTypeTest extends AbstractUnitTestCase
{
    use GdTrait;

    /**
     * @return array[]
     */
    public static function getExamples(): array
    {
        return [
            [
                dataDir('assets/images/example-gif.gif'),
                IMAGETYPE_GIF,
            ],
            [
                dataDir('assets/images/example-jpg.jpg'),
                IMAGETYPE_JPEG,
            ],
            [
                dataDir('assets/images/example-png.png'),
                IMAGETYPE_PNG,
            ],
            [
                dataDir('assets/images/example-wbmp.wbmp'),
                IMAGETYPE_WBMP,
            ],
            [
                dataDir('assets/images/example-webp.webp'),
                IMAGETYPE_WEBP,
            ],
            [
                dataDir('assets/images/example-xbm.xbm'),
                IMAGETYPE_XBM,
            ],
        ];
    }

    /**
     * Tests Phalcon\Image\Adapter\Gd :: getType()
     *
     * @dataProvider getExamples
     *
     * @return void
     * @since        2022-07-19
     *
     * @author       Phalcon Team <team@phalcon.io>
     */
    public function testImageAdapterGdGetType(
        string $source,
        int $expected
    ): void {
        $this->checkJpegSupport();

        $gd = new Gd($source);

        $actual = $gd->getType();
        $this->assertSame($expected, $actual);
    }
}
