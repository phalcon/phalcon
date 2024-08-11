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

use function dataDir;

final class GetHeightTest extends AbstractUnitTestCase
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
                640,
            ],
            [
                dataDir('assets/images/example-jpg.jpg'),
                694,
            ],
            [
                dataDir('assets/images/example-png.png'),
                82,
            ],
            [
                dataDir('assets/images/example-wbmp.wbmp'),
                426,
            ],
            [
                dataDir('assets/images/example-webp.webp'),
                1024,
            ],
            [
                dataDir('assets/images/example-xbm.xbm'),
                187,
            ],
        ];
    }

    /**
     * Tests Phalcon\Image\Adapter\Gd :: getHeight()
     *
     * @dataProvider getExamples
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2018-11-13
     */
    public function testImageAdapterGdGetHeight(
        string $source,
        int $expected
    ): void {
        $this->checkJpegSupport();

        $gd = new Gd($source);

        $actual = $gd->getHeight();
        $this->assertSame($expected, $actual);
    }
}
