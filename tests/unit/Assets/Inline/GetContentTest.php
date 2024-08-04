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

use Codeception\Example;
use Phalcon\Assets\Inline;
use Phalcon\Tests\Fixtures\Traits\AssetsTrait;
use Phalcon\Tests\UnitTestCase;

final class GetContentTest extends UnitTestCase
{
    use AssetsTrait;

    /**
     * Tests Phalcon\Assets\Inline :: getContent()
     *
     * @dataProvider providerInlineCssJs
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testAssetsInlineGetContent(
        string $type,
        string $content
    ): void {
        $asset = new Inline($type, $content);

        $expected = $content;
        $actual   = $asset->getContent();
        $this->assertSame($expected, $actual);
    }
}
