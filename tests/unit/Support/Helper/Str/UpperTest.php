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

namespace Phalcon\Tests\Unit\Support\Helper\Str;

use Codeception\Example;
use Phalcon\Support\Helper\Str\Upper;
use Phalcon\Tests\UnitTestCase;

final class UpperTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Support\Helper\Str :: upper()
     *
     * @dataProvider basicProvider
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testSupportHelperStrUpper(
        string $text,
        string $expected
    ): void {
        $object   = new Upper();
        $actual   = $object($text);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Helper\Str :: upper() - multi-bytes encoding
     *
     * @dataProvider multiBytesEncodingProvider
     *
     * @return void
     *
     * @author       Stanislav Kiryukhin <korsar.zn@gmail.com>
     * @since        2015-05-06
     */
    public function testSupportHelperStrUpperMultiBytesEncoding(
        string $text,
        string $expected
    ): void {
        $object   = new Upper();
        $actual   = $object($text);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return string[][]
     */
    public static function basicProvider(): array
    {
        return [
            [
                'hello',
                'HELLO',
            ],

            [
                'HELLO',
                'HELLO',
            ],

            [
                '1234',
                '1234',
            ],
        ];
    }

    /**
     * @return string[][]
     */
    public static function multiBytesEncodingProvider(): array
    {
        return [
            [
                'ПРИВЕТ МИР!',
                'ПРИВЕТ МИР!',
            ],

            [
                'ПриВЕт Мир!',
                'ПРИВЕТ МИР!',
            ],

            [
                'привет мир!',
                'ПРИВЕТ МИР!',
            ],

            [
                'MÄNNER',
                'MÄNNER',
            ],

            [
                'mÄnnER',
                'MÄNNER',
            ],

            [
                'männer',
                'MÄNNER',
            ],
        ];
    }
}
