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

use Phalcon\Support\Str\IsPalindrome;
use UnitTester;

class IsPalindromeCest
{
    /**
     * Tests Phalcon\Support\Str :: isPalindrome()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrIsPalindrome(UnitTester $I)
    {
        $I->wantToTest('Support\Str - isPalindrome()');

        $object = new IsPalindrome();
        $actual = $object('racecar');
        $I->assertTrue($actual);
    }
}
