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

namespace Phalcon\Tests\Unit\Assets\Asset\Js;

use Phalcon\Assets\Asset\Js;
use Phalcon\Tests\AbstractUnitTestCase;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

use function dataDir;
use function file_get_contents;

final class GetContentTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Assets\Asset\Js :: getContent()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testAssetsAssetJsGetContent(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Need to fix Windows new lines...');
        }

        $asset = new Js('assets/assets/signup.js');

        $expected = file_get_contents(dataDir('assets/assets/signup.js'));
        $actual   = $asset->getContent(dataDir());
        $this->assertSame($expected, $actual);
    }
}
