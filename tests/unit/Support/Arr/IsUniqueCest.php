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

use Phalcon\Support\Arr\IsUnique;
use UnitTester;

/**
 * Class IsUniqueCest
 *
 * @package Phalcon\Tests\Unit\Support\Arr
 */
class IsUniqueCest
{
    /**
     * Tests Phalcon\Support\Arr :: isUnique()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportArrIsUnique(UnitTester $I)
    {
        $I->wantToTest('Support\Arr - isUnique()');

        $object     = new IsUnique();
        $collection = [
            'Phalcon',
            'Framework',
        ];

        $actual = $object($collection);
        $I->assertTrue($actual);

        $collection = [
            'Phalcon',
            'Framework',
            'Phalcon',
        ];

        $actual = $object($collection);
        $I->assertFalse($actual);
    }
}
