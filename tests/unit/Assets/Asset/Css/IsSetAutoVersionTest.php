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

namespace Phalcon\Tests\Unit\Assets\Asset\Css;

use Phalcon\Assets\Asset\Css;
use Phalcon\Tests\UnitTestCase;

final class IsSetAutoVersionTest extends UnitTestCase
{
    /**
     * Unit Tests Phalcon\Assets\Asset\Css :: isAutoVersion()/setAutoVersion()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAssetsAssetCssIsSetAutoVersion(): void
    {
        $asset  = new Css('css/docs.css');
        $actual = $asset->isAutoVersion();
        $this->assertFalse($actual);

        $asset->setAutoVersion(true);

        $actual = $asset->isAutoVersion();
        $this->assertTrue($actual);
    }
}
