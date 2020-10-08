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

namespace Phalcon\Tests\Unit\Logger\Formatter\Line;

use Phalcon\Logger\Formatter\Line;
use UnitTester;

class GetDateFormatCest
{
    /**
     * Tests Phalcon\Logger\Formatter\Line :: getDateFormat()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function loggerFormatterLineGetDateFormat(UnitTester $I)
    {
        $I->wantToTest('Logger\Formatter\Line - getDateFormat()');
        $formatter = new Line();

        $expected = 'c';
        $actual   = $formatter->getDateFormat();
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Logger\Formatter\Line :: getDateFormat() - custom
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function loggerFormatterLineGetDateFormatCustom(UnitTester $I)
    {
        $I->wantToTest('Logger\Formatter\Line - getDateFormat() - custom');
        $formatter = new Line('', 'Ymd-His');

        $expected = 'Ymd-His';
        $actual   = $formatter->getDateFormat();
        $I->assertEquals($expected, $actual);
    }
}
