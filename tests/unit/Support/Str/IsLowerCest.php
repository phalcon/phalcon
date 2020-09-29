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

use Phalcon\Support\Str\IsLower;
use UnitTester;

class IsLowerCest
{
    /**
     * Tests Phalcon\Support\Str :: isLower()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrIsLower(UnitTester $I)
    {
        $I->wantToTest('Support\Str - isLower()');

        $object = new IsLower();
        $actual = $object('phalcon framework');
        $I->assertTrue($actual);

        $actual = $object('Phalcon Framework');
        $I->assertFalse($actual);
    }
}
