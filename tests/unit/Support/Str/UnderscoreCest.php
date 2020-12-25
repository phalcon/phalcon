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
use Phalcon\Support\Str\Underscore;
use UnitTester;

/**
 * Class UnderscoreCest
 *
 * @package Phalcon\Tests\Unit\Support\Str
 */
class UnderscoreCest
{
    /**
     * Tests Phalcon\Support\Str :: underscore()
     *
     * @dataProvider getExamples
     *
     * @param UnitTester $I
     * @param Example    $example
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrUnderscore(UnitTester $I, Example $example)
    {
        $I->wantToTest('Support\Str - underscore() - ' . $example[0]);

        $object = new Underscore();

        $expected = $example[2];
        $actual   = $object($example[1]);
        $I->assertEquals($expected, $actual);
    }

    /**
     * @return \string[][]
     */
    private function getExamples(): array
    {
        return [
            [
                'spaces',
                'start a horse',
                'start_a_horse',
            ],
            [
                'tabs',
                "five\tcats",
                'five_cats',
            ],
            [
                'more spaces',
                ' look behind ',
                'look_behind',
            ],
            [
                'more tabs',
                " \t Awesome \t  \t Phalcon ",
                'Awesome_Phalcon',
            ],
        ];
    }
}
