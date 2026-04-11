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

final class ReturnTest extends AbstractUnitTestCase
{
    /**
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-10
     */
    public function testMvcViewEngineVoltParserReturn(): void
    {
        $source   = '{% return value %}';
        $expected = [
            [
                'type' => 327,
                'expr' => [
                    'type' => 265,
                    'value' => 'value',
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
    public function testMvcViewEngineVoltParserReturnExpr(): void
    {
        $source   = '{% return a + b %}';
        $expected = [
            [
                'type' => 327,
                'expr' => [
                    'type' => 43,
                    'left' => [
                        'type' => 265,
                        'value' => 'a',
                        'file' => 'eval code',
                        'line' => 1,
                    ],
                    'right' => [
                        'type' => 265,
                        'value' => 'b',
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