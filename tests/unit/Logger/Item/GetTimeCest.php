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

use Phalcon\Logger\Item;
use Phalcon\Logger\Logger;
use UnitTester;

class GetTimeCest
{
    /**
     * Tests Phalcon\Logger\Item :: getTime()
     *
     * @param UnitTester $I
     *
     * @since  2020-09-06
     *
     * @author Phalcon Team <team@phalcon.io>
     */
    public function loggerItemGetTime(UnitTester $I)
    {
        $I->wantToTest('Logger\Item - getTime()');
        $time = time();
        $item = new Item('log message', 'debug', Logger::DEBUG, $time);

        $expected = $time;
        $actual   = $item->getTime();
        $I->assertEquals($expected, $actual);
    }
}
