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

use Phalcon\Support\Arr\Has;
use UnitTester;

/**
 * Class HasCest
 *
 * @package Phalcon\Tests\Unit\Support\Arr
 */
class HasCest
{
    /**
     * Tests Phalcon\Support\Arr :: has()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportArrHas(UnitTester $I)
    {
        $I->wantToTest('Support\Arr - has()');

        $object     = new Has();
        $collection = [
            1        => 'Phalcon',
            'suffix' => 'Framework',
        ];

        $actual = $object($collection, 1);
        $I->assertTrue($actual);

        $actual = $object($collection, 'suffix');
        $I->assertTrue($actual);

        $actual = $object($collection, 'unknown');
        $I->assertFalse($actual);
    }
}
