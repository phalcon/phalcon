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

namespace Phalcon\Tests\Unit\Support\Str;

use Codeception\Example;
use Phalcon\Support\Str\Upper;
use UnitTester;

class UpperCest
{
    /**
     * Tests Phalcon\Support\Str :: upper()
     *
     * @dataProvider basicProvider
     *
     * @param UnitTester $I
     * @param Example    $example
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrUpper(UnitTester $I, Example $example)
    {
        $I->wantToTest('Support\Str - upper()');

        $object   = new Upper();
        $expected = $example['expected'];
        $actual   = $object($example['text']);
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Str :: upper() - multi-bytes encoding
     *
     * @dataProvider multiBytesEncodingProvider
     *
     * @param UnitTester $I
     * @param Example    $example
     *
     * @author Stanislav Kiryukhin <korsar.zn@gmail.com>
     * @since  2015-05-06
     */
    public function supportStrUpperMultiBytesEncoding(UnitTester $I, Example $example)
    {
        $I->wantToTest('Support\Str - upper() - multi byte encoding');

        $object   = new Upper();
        $expected = $example['expected'];
        $actual   = $object($example['text']);
        $I->assertEquals($expected, $actual);
    }

    /**
     * @return \string[][]
     */
    private function basicProvider(): array
    {
        return [
            [
                'text'     => 'hello',
                'expected' => 'HELLO',
            ],

            [
                'text'     => 'HELLO',
                'expected' => 'HELLO',
            ],

            [
                'text'     => '1234',
                'expected' => '1234',
            ],
        ];
    }

    /**
     * @return \string[][]
     */
    private function multiBytesEncodingProvider(): array
    {
        return [
            [
                'text'     => 'ПРИВЕТ МИР!',
                'expected' => 'ПРИВЕТ МИР!',
            ],

            [
                'text'     => 'ПриВЕт Мир!',
                'expected' => 'ПРИВЕТ МИР!',
            ],

            [
                'text'     => 'привет мир!',
                'expected' => 'ПРИВЕТ МИР!',
            ],

            [
                'text'     => 'MÄNNER',
                'expected' => 'MÄNNER',
            ],

            [
                'text'     => 'mÄnnER',
                'expected' => 'MÄNNER',
            ],

            [
                'text'     => 'männer',
                'expected' => 'MÄNNER',
            ],
        ];
    }
}
