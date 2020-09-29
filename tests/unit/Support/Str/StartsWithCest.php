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

use Phalcon\Support\Str\StartsWith;
use UnitTester;

class StartsWithCest
{
    /**
     * Tests Phalcon\Support\Str :: startsWith()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrStartsWith(UnitTester $I)
    {
        $I->wantToTest('Support\Str - startsWith()');

        $object = new StartsWith();

        $actual = $object('Hello', 'H');
        $I->assertTrue($actual);

        $actual = $object('Hello', 'He');
        $I->assertTrue($actual);

        $actual = $object('Hello', 'Hello');
        $I->assertTrue($actual);
    }

    /**
     * Tests Phalcon\Support\Str :: startsWith() - empty strings
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrStartsWithEmpty(UnitTester $I)
    {
        $I->wantToTest('Support\Str - startsWith() - empty strings');

        $object = new StartsWith();

        $actual = $object('', '');
        $I->assertFalse($actual);
    }

    /**
     * Tests Phalcon\Support\Str :: startsWith() - finding an empty string
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrStartsWithEmptySearchString(UnitTester $I)
    {
        $I->wantToTest('Support\Str - startsWith() - search empty string');

        $object = new StartsWith();

        $actual = $object('', 'hello');
        $I->assertFalse($actual);
    }


    /**
     * Tests Phalcon\Support\Str :: startsWith() - case insensitive flag
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrStartsWithCaseInsensitive(UnitTester $I)
    {
        $I->wantToTest('Support\Str - startsWith() - case insensitive flag');

        $object = new StartsWith();

        $actual = $object('Hello', 'h');
        $I->assertTrue($actual);

        $actual = $object('Hello', 'he');
        $I->assertTrue($actual);

        $actual = $object('Hello', 'hello');
        $I->assertTrue($actual);
    }

    /**
     * Tests Phalcon\Support\Str :: startsWith() - case sensitive flag
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrStartsWithCaseSensitive(UnitTester $I)
    {
        $I->wantToTest('Support\Str - startsWith() - case sensitive flag');

        $object = new StartsWith();

        $actual = $object('Hello', 'hello', true);
        $I->assertTrue($actual);

        $actual = $object('Hello', 'hello', false);
        $I->assertFalse($actual);

        $actual = $object('Hello', 'h', false);
        $I->assertFalse($actual);
    }
}
