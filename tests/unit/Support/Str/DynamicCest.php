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

use Phalcon\Support\Str\Dynamic;
use UnitTester;

class DynamicCest
{
    /**
     * Tests Phalcon\Text :: dynamic()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrDynamic(UnitTester $I)
    {
        $I->wantToTest('Support\Str - dynamic()');

        $object = new Dynamic();
        $actual = $object('{Hi|Hello}, my name is a Bob!');

        $I->assertStringNotContainsString('{', $actual);
        $I->assertStringNotContainsString('}', $actual);

        $I->assertRegExp(
            '/^(Hi|Hello), my name is a Bob!$/',
            $actual
        );
    }

    /**
     * Tests Phalcon\Text :: dynamic() - custom delimiter
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrDynamicCustomDelimiter(UnitTester $I)
    {
        $I->wantToTest('Support\Str - dynamic() - custom delimiter');
        $object = new Dynamic();
        $actual = $object('(Hi|Hello), my name is a Bob!', '(', ')');

        $I->assertStringNotContainsString('{', $actual);
        $I->assertStringNotContainsString('}', $actual);

        $I->assertRegExp(
            '/^(Hi|Hello), my name is a Bob!$/',
            $actual
        );
    }

    /**
     * Tests Phalcon\Text :: dynamic() - custom separator
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrDynamicCustomSeparator(UnitTester $I)
    {
        $I->wantToTest('Support\Str - dynamic() - custom separator');

        $object = new Dynamic();
        $actual = $object('{Hi=Hello}, my name is a Bob!', '{', '}', '=');

        $I->assertStringNotContainsString('{', $actual);
        $I->assertStringNotContainsString('}', $actual);
        $I->assertStringNotContainsString('=', $actual);

        $I->assertRegExp(
            '/^(Hi|Hello), my name is a Bob!$/',
            $actual
        );

        $actual = $object("{Hi'Hello}, my name is a {Rob'Zyxep'Andres}!", '{', '}', "'");

        $I->assertStringNotContainsString('{', $actual);
        $I->assertStringNotContainsString('}', $actual);
        $I->assertStringNotContainsString("''", $actual);

        $I->assertRegExp(
            '/^(Hi|Hello), my name is a (Rob|Zyxep|Andres)!$/',
            $actual
        );

        $actual = $object('{Hi/Hello}, my name is a {Stanislav/Nikos}!', '{', '}', '/');

        $I->assertStringNotContainsString('{', $actual);
        $I->assertStringNotContainsString('}', $actual);
        $I->assertStringNotContainsString('/', $actual);

        $I->assertRegExp(
            '/^(Hi|Hello), my name is a (Stanislav|Nikos)!$/',
            $actual
        );
    }
}
