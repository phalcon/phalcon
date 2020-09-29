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

use Phalcon\Support\Arr\ToObject;
use stdClass;
use UnitTester;

/**
 * Class ToObjectCest
 *
 * @package Phalcon\Tests\Unit\Support\Arr
 */
class ToObjectCest
{
    /**
     * Unit Tests Phalcon\Support\Arr :: toObject()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportArrArrayToObject(UnitTester $I)
    {
        $I->wantToTest('Support\Arr - toObject()');

        $object = new ToObject();
        $source = [
            'one'   => 'two',
            'three' => 'four',
        ];


        $expected        = new stdClass();
        $expected->one   = 'two';
        $expected->three = 'four';

        $actual = $object($source);
        $I->assertEquals($expected, $actual);
    }
}
