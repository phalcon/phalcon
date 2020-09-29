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

use Phalcon\Support\Str\CountVowels;
use UnitTester;

class CountVowelsCest
{
    /**
     * Tests Phalcon\Support\Str :: countVowels()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrCountVowels(UnitTester $I)
    {
        $I->wantToTest('Support\Str - countVowels()');

        $object = new CountVowels();

        $source   = 'Luke, I am your father!';
        $expected = 9;
        $actual   = $object($source);
        $I->assertEquals($expected, $actual);

        $source   = '';
        $expected = 0;
        $actual   = $object($source);
        $I->assertEquals($expected, $actual);
    }
}
