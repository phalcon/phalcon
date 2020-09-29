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

use Phalcon\Support\Str\Humanize;
use UnitTester;

class HumanizeCest
{
    /**
     * Tests Phalcon\Support\Str :: humanize()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrHumanize(UnitTester $I)
    {
        $I->wantToTest('Support\Str - humanize()');

        $object   = new Humanize();
        $expected = 'start a horse';
        $actual   = $object('start_a_horse');
        $I->assertEquals($expected, $actual);

        $expected = 'five cats';
        $actual   = $object('five-cats');
        $I->assertEquals($expected, $actual);

        $expected = 'kittens are cats';
        $actual   = $object('kittens-are_cats');
        $I->assertEquals($expected, $actual);

        $expected = 'Awesome Phalcon';
        $actual   = $object(" \t Awesome-Phalcon \t ");
        $I->assertEquals($expected, $actual);
    }
}
