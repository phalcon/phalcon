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

final class GetVersionTest extends AbstractUnitTestCase
{
    use GdTrait;

    /**
     * Unit Tests Phalcon\Image\Adapter\Gd :: getVersion()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-05-25
     */
    public function testImageAdapterGdGetVersion(): void
    {
        $this->checkJpegSupport();

        $gd = new Gd(dataDir('assets/images/example-jpg.jpg'));

        $expected = '/^2.[0-9].[0-9]/';
        $actual   = $gd->getVersion();
        $this->assertMatchesRegularExpression($expected, $actual);
    }
}
