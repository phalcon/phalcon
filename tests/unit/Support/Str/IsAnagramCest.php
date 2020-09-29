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

use Phalcon\Support\Str\IsAnagram;
use UnitTester;

class IsAnagramCest
{
    /**
     * Tests Phalcon\Support\Str :: isAnagram()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrIsAnagram(UnitTester $I)
    {
        $I->wantToTest('Support\Str - isAnagram()');

        $object = new IsAnagram();
        $actual = $object('rail safety', 'fairy tales');
        $I->assertTrue($actual);
    }
}
