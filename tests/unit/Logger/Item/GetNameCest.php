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

namespace Phalcon\Tests\Unit\Logger\Item;

use DateTimeImmutable;
use DateTimeZone;
use Phalcon\Logger\Item;
use Phalcon\Logger\Logger;
use UnitTester;

use function date_default_timezone_get;

class GetNameCest
{
    /**
     * Tests Phalcon\Logger\Item :: getName()
     *
     * @param UnitTester $I
     *
     * @since  2020-09-06
     *
     * @author Phalcon Team <team@phalcon.io>
     */
    public function loggerItemGetName(UnitTester $I)
    {
        $I->wantToTest('Logger\Item - getName()');

        $timezone = date_default_timezone_get();
        $datetime = new DateTimeImmutable('now', new DateTimeZone($timezone));
        $item     = new Item(
            'log message',
            'debug',
            Logger::DEBUG,
            $datetime
        );

        $I->assertEquals(
            'debug',
            $item->getName()
        );
    }
}
