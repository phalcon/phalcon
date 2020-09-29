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

use Phalcon\Support\Str\IsUpper;
use UnitTester;

class IsUpperCest
{
    /**
     * Tests Phalcon\Support\Str :: isUpper()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrIsUpper(UnitTester $I)
    {
        $I->wantToTest('Support\Str - isUpper()');

        $object = new IsUpper();
        $actual = $object('PHALCON FRAMEWORK');
        $I->assertTrue($actual);

        $actual = $object('Phalcon Framework');
        $I->assertFalse($actual);
    }
}
