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

namespace Phalcon\Tests\Unit\Helper\Str;

use Codeception\Example;
use Phalcon\Helper\Str;
use UnitTester;

class UcwordsCest
{
    /**
     * Tests Phalcon\Helper\Str :: ucwords()
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-04-06
     *
     * @dataProvider getData
     */
    public function helperStrUcwords(UnitTester $I, Example $example)
    {
        $I->wantToTest('Helper\Str - ucwords()');

        $I->assertEquals(
            $example['expected'],
            Str::ucwords(
                $example['text']
            )
        );
    }

    private function getData(): array
    {
        return [
            [
                'text'     => 'hello goodbye',
                'expected' => 'Hello Goodbye',
            ],

            [
                'text'     => 'HELLO GOODBYE',
                'expected' => 'Hello Goodbye',
            ],

            [
                'text'     => '1234',
                'expected' => '1234',
            ],
            [
                'text'     => 'ПРИВЕТ МИР!',
                'expected' => 'Привет Мир!',
            ],

            [
                'text'     => 'ПриВЕт Мир!',
                'expected' => 'Привет Мир!',
            ],

            [
                'text'     => 'привет мир!',
                'expected' => 'Привет Мир!',
            ],

            [
                'text'     => 'MÄNNER MÄNNER',
                'expected' => 'Männer Männer',
            ],

            [
                'text'     => 'mÄnnER mÄnnER',
                'expected' => 'Männer Männer',
            ],

            [
                'text'     => 'männer männer',
                'expected' => 'Männer Männer',
            ],
        ];
    }
}
