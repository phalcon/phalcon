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

namespace Phalcon\Tests\Unit\Assets\Filters\CssMin;

use Phalcon\Assets\Filters\CssMin;
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Test;

final class FilterTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Assets\Filters\CssMin :: filter()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testAssetsFiltersCssMinFilter(): void
    {
        $cssmin = new Cssmin();

        $source = "body {
        background: green;
    }";

        $expected = 'body{background:green}';
        $actual   = $cssmin->filter($source);
        $this->assertSame($expected, $actual);
    }
}
