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

use Phalcon\Support\Arr\LastKey;
use UnitTester;

use function strlen;

/**
 * Class LastKeyCest
 *
 * @package Phalcon\Tests\Unit\Support\Arr
 */
class LastKeyCest
{
    /**
     * Tests Phalcon\Support\Arr :: lastKey()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportArrLastKey(UnitTester $I)
    {
        $I->wantToTest('Support\Arr - lastKey()');

        $object     = new LastKey();
        $collection = [
            1 => 'Phalcon',
            3 => 'Framework',
        ];

        $expected = 3;
        $actual   = $object($collection);
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Arr :: lastKey() - function
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportArrLastKeyFunction(UnitTester $I)
    {
        $I->wantToTest('Support\Arr - lastKey() - function');

        $object     = new LastKey();
        $collection = [
            1 => 'Phalcon',
            3 => 'Framework',
        ];

        $expected = 1;
        $actual   = $object(
            $collection,
            function ($element) {
                return strlen($element) < 8;
            }
        );
        $I->assertEquals($expected, $actual);
    }
}
