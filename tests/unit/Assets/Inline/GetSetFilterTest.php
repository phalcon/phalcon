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

namespace Phalcon\Tests\Unit\Assets\Inline;

use Phalcon\Assets\Inline;
use Phalcon\Tests\Fixtures\Traits\AssetsTrait;
use Phalcon\Tests\UnitTestCase;

final class GetSetFilterTest extends UnitTestCase
{
    use AssetsTrait;

    /**
     * Tests Phalcon\Assets\Inline :: getFilter()/setFilter()
     *
     * @dataProvider providerInlineCssJs
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testAssetsInlineGetSetFilter(
        string $type,
        string $content
    ): void {
        $asset  = new Inline($type, $content);
        $actual = $asset->getFilter();
        $this->assertTrue($actual);

        $asset->setFilter(false);

        $actual = $asset->getFilter();
        $this->assertFalse($actual);
    }
}
