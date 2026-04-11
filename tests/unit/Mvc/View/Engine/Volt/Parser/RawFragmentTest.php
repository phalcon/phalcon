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

namespace Phalcon\Tests\Unit\Mvc\View\Engine\Volt\Parser;

use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Volt\Parser\Parser;

final class RawFragmentTest extends AbstractUnitTestCase
{
    /**
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-10
     */
    public function testMvcViewEngineVoltParserRawText(): void
    {
        $source   = 'Hello World';
        $expected = [
            [
                'type' => 357,
                'value' => 'Hello World',
                'file' => 'eval code',
                'line' => 1,
            ],
        ];
        $actual   = (new Parser($source))->parseView('eval code');
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-10
     */
    public function testMvcViewEngineVoltParserRawTextMultiline(): void
    {
        $source   = "Line one
Line two";
        $expected = [
            [
                'type' => 357,
                'value' => "Line one
Line two",
                'file' => 'eval code',
                'line' => 2,
            ],
        ];
        $actual   = (new Parser($source))->parseView('eval code');
        $this->assertSame($expected, $actual);
    }
}
