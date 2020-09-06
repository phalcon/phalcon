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

        $time = time();

        $item = new Item(
            'log message',
            'debug',
            Logger::DEBUG,
            $time
        );

        $I->assertEquals(
            'debug',
            $item->getName()
        );
    }
}
