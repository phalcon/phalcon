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

namespace Phalcon\Tests\Unit\Support\Arr;

use Phalcon\Support\Arr\Set;
use UnitTester;

/**
 * Class SetCest
 *
 * @package Phalcon\Tests\Unit\Support\Arr
 */
class SetCest
{
    /**
     * Tests Phalcon\Support\Arr :: set() - numeric
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportArrSetNumeric(UnitTester $I)
    {
        $I->wantToTest('Support\Arr - set() - numeric');

        $object     = new Set();
        $collection = [];

        $expected = [
            1 => 'Phalcon',
        ];
        $actual   = $object($collection, 'Phalcon', 1);
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Arr :: set() - string
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportArrSetString(UnitTester $I)
    {
        $I->wantToTest('Support\Arr - set() - string');

        $object     = new Set();
        $collection = [];

        $expected = [
            'suffix' => 'Framework',
        ];
        $actual   = $object($collection, 'Framework', 'suffix');
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Arr :: set() - no index
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportArrSetNoIndex(UnitTester $I)
    {
        $I->wantToTest('Support\Arr - set() - no index');

        $object     = new Set();
        $collection = [];

        $expected = [
            0 => 'Phalcon',
        ];
        $actual   = $object($collection, 'Phalcon');
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Arr :: set() - overwrite
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportArrSetOverwride(UnitTester $I)
    {
        $I->wantToTest('Support\Arr - set() - overwrite');

        $object     = new Set();
        $collection = [
            1 => 'Phalcon',
        ];

        $expected = [
            1 => 'Framework',
        ];
        $actual   = $object($collection, 'Framework', 1);
        $I->assertEquals($expected, $actual);
    }
}
