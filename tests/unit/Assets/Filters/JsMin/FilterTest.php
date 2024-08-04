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

namespace Phalcon\Tests\Unit\Assets\Filters\JsMin;

use Phalcon\Assets\Filters\JsMin;
use Phalcon\Tests\UnitTestCase;

final class FilterTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Assets\Filters\JsMin :: filter()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAssetsFiltersJsMinFilter(): void
    {
        $jsmin = new JsMin();
        $source = "// nothing special here
var a = 1;";

        $expected = 'var a=1';
        $actual   = $jsmin->filter($source);
        $this->assertSame($expected, $actual);
    }
}
