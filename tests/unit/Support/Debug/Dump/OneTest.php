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

namespace Phalcon\Tests\Unit\Support\Debug\Dump;

use Phalcon\Support\Debug\Dump;
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Test;

final class OneTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Support\Debug\Dump :: one()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testSupportDebugDumpOne(): void
    {
        $test = 'value';
        $dump = new Dump();

        $expected = '<pre style="background-color:#f3f3f3; font-size:11px; '
            . 'padding:10px; border:1px solid #ccc; text-align:left; '
            . 'color:#333"><b style="color:teal">String</b> '
            . '(<span style="color:teal">5</span>) "'
            . '<span style="color:teal">value</span>"</pre>';
        $actual   = $dump->one($test);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Debug\Dump :: one() - name
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testSupportDebugDumpOneName(): void
    {
        $test = 'value';
        $dump = new Dump();

        $expected = '<pre style="background-color:#f3f3f3; font-size:11px; '
            . 'padding:10px; border:1px solid #ccc; text-align:left; '
            . 'color:#333">super <b style="color:teal">String</b> '
            . '(<span style="color:teal">5</span>) "'
            . '<span style="color:teal">value</span>"</pre>';
        $actual   = $dump->one($test, 'super');
        $this->assertSame($expected, $actual);
    }
}
