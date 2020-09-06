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

class GetContextCest
{
    /**
     * Tests Phalcon\Logger\Item :: getContext()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-06
     *
     * @param UnitTester $I
     */
    public function loggerItemGetContext(UnitTester $I)
    {
        $I->wantToTest('Logger\Item - getContext()');
        $time    = time();
        $context = ['context'];
        $item    = new Item('log message', 'debug', Logger::DEBUG, $time, $context);

        $expected = $context;
        $actual   = $item->getContext();
        $I->assertEquals($expected, $actual);
    }
}
