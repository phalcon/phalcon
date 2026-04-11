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

final class ConcatenationTest extends AbstractUnitTestCase
{
    /**
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-10
     */
    public function testMvcViewEngineVoltParserExprConcat(): void
    {
        $source   = '{{ \'Hello\' ~ \' \' ~ \'World\' }}';
        $expected = [
            [
                'type' => 359,
                'expr' => [
                    'type' => 126,
                    'left' => [
                        'type' => 126,
                        'left' => [
                            'type' => 260,
                            'value' => 'Hello',
                            'file' => 'eval code',
                            'line' => 1,
                        ],
                        'right' => [
                            'type' => 260,
                            'value' => ' ',
                            'file' => 'eval code',
                            'line' => 1,
                        ],
                        'file' => 'eval code',
                        'line' => 1,
                    ],
                    'right' => [
                        'type' => 260,
                        'value' => 'World',
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
    public function testMvcViewEngineVoltParserExprConcatVar(): void
    {
        $source   = '{{ firstName ~ \' \' ~ lastName }}';
        $expected = [
            [
                'type' => 359,
                'expr' => [
                    'type' => 126,
                    'left' => [
                        'type' => 126,
                        'left' => [
                            'type' => 265,
                            'value' => 'firstName',
                            'file' => 'eval code',
                            'line' => 1,
                        ],
                        'right' => [
                            'type' => 260,
                            'value' => ' ',
                            'file' => 'eval code',
                            'line' => 1,
                        ],
                        'file' => 'eval code',
                        'line' => 1,
                    ],
                    'right' => [
                        'type' => 265,
                        'value' => 'lastName',
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