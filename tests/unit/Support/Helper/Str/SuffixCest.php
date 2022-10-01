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

namespace Phalcon\Tests\Unit\Support\Helper\Str;

use Phalcon\Support\Helper\Str\Suffix;
use UnitTester;

/**
 * Class SuffixCest
 *
 * @package Phalcon\Tests\Unit\Support\Helper\Str
 */
class SuffixCest
{
    /**
     * Tests Phalcon\Support\Helper\Str :: suffix()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportHelperStrSuffix(UnitTester $I)
    {
        $I->wantToTest('Support\Helper\Str - suffix()');

        $object = new Suffix();

        $expected = 'ClassConstants';
        $actual   = $object('Class', 'Constants');
        $I->assertSame($expected, $actual);
    }
}
