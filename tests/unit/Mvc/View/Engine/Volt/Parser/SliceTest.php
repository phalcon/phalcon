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

final class SliceTest extends AbstractUnitTestCase
{
    /**
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-10
     */
    public function testMvcViewEngineVoltParserExprSliceBoth(): void
    {
        $source   = '{{ items[1:3] }}';
        $expected = [
            [
                'type' => 359,
                'expr' => [
                    'type' => 365,
                    'left' => [
                        'type' => 265,
                        'value' => 'items',
                        'file' => 'eval code',
                        'line' => 1,
                    ],
                    'start' => [
                        'type' => 258,
                        'value' => '1',
                        'file' => 'eval code',
                        'line' => 1,
                    ],
                    'end' => [
                        'type' => 258,
                        'value' => '3',
                        'file' => 'eval code',
                        'line' => 1,
                    ],
                    'file' => 'eval code',
                    'line' => 1,
                ],
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
    public function testMvcViewEngineVoltParserExprSliceFrom(): void
    {
        $source   = '{{ items[2:] }}';
        $expected = [
            [
                'type' => 359,
                'expr' => [
                    'type' => 365,
                    'left' => [
                        'type' => 265,
                        'value' => 'items',
                        'file' => 'eval code',
                        'line' => 1,
                    ],
                    'start' => [
                        'type' => 258,
                        'value' => '2',
                        'file' => 'eval code',
                        'line' => 1,
                    ],
                    'file' => 'eval code',
                    'line' => 1,
                ],
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
    public function testMvcViewEngineVoltParserExprSliceTo(): void
    {
        $source   = '{{ items[:4] }}';
        $expected = [
            [
                'type' => 359,
                'expr' => [
                    'type' => 365,
                    'left' => [
                        'type' => 265,
                        'value' => 'items',
                        'file' => 'eval code',
                        'line' => 1,
                    ],
                    'end' => [
                        'type' => 258,
                        'value' => '4',
                        'file' => 'eval code',
                        'line' => 1,
                    ],
                    'file' => 'eval code',
                    'line' => 1,
                ],
                'file' => 'eval code',
                'line' => 1,
            ],
        ];
        $actual   = (new Parser($source))->parseView('eval code');
        $this->assertSame($expected, $actual);
    }
}