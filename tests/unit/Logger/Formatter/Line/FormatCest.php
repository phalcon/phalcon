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

use Exception;
use Phalcon\Logger\Logger;
use Phalcon\Logger\Formatter\Line;
use Phalcon\Logger\Item;
use UnitTester;
use function var_dump;

class FormatCest
{
    /**
     * Tests Phalcon\Logger\Formatter\Line :: format()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-06
     *
     * @param UnitTester $I
     *
     * @throws Exception
     */
    public function loggerFormatterLineFormat(UnitTester $I)
    {
        $I->wantToTest('Logger\Formatter\Line - format()');

        $formatter = new Line();

        $time = time();

        $item = new Item(
            'log message',
            'debug',
            Logger::DEBUG,
            $time
        );

        $expected = sprintf(
            '[%s][debug] log message',
            date('c', $time)
        );

        $I->assertEquals(
            $expected,
            $formatter->format($item)
        );
    }

    /**
     * Tests Phalcon\Logger\Formatter\Line :: format() -custom
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-06
     *
     * @param UnitTester $I
     *
     * @throws Exception
     */
    public function loggerFormatterLineFormatCustom(UnitTester $I)
    {
        $I->wantToTest('Logger\Formatter\Line - format() - custom');

        $formatter = new Line('{message}-[{type}]-{date}');

        $time = time();

        $item = new Item(
            'log message',
            'debug',
            Logger::DEBUG,
            $time
        );

        $expected = sprintf(
            'log message-[debug]-%s',
            date('c', $time)
        );

        $I->assertEquals(
            $expected,
            $formatter->format($item)
        );
    }

    /**
     * Tests Phalcon\Logger\Formatter\Line :: format() -custom with milliseconds
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-06
     *
     * @param UnitTester $I
     *
     * @throws Exception
     */
    public function loggerFormatterLineFormatCustomWithMilliseconds(UnitTester $I)
    {
        $I->wantToTest('Logger\Formatter\Line - format() - custom - with milliseconds');

        $formatter = new Line(
            '{message}-[{type}]-{date}',
            'U.u'
        );

        $item = new Item(
            'log message',
            'debug',
            Logger::DEBUG,
            time()
        );

        $result = $formatter->format($item);
        $I->assertEquals('', $result);
        die();
        $parts  = explode('-', $result);
        $parts  = explode('.', $parts[2]);
        $I->assertCount(2, $parts);
        $I->assertGreaterThan(0, (int) $parts[0]);
        $I->assertGreaterThan(0, (int) $parts[1]);
    }
}
