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

namespace Phalcon\Tests\Unit\Logger\Logger;

use Phalcon\Logger\Enum;
use Phalcon\Logger\Logger;
use UnitTester;

class GetSetLogLevelCest
{
    /**
     * Tests Phalcon\Logger :: getLogLevel()/setLogLevel()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function loggerGetSetLogLevel(UnitTester $I)
    {
        $I->wantToTest('Logger - getLogLevel()/setLogLevel()');
        $logger = new Logger('my-name');

        $I->assertSame(Enum::CUSTOM, $logger->getLogLevel());

        $object = $logger->setLogLevel(Enum::INFO);
        $I->assertInstanceOf(Logger::class, $object);

        $I->assertSame(Enum::INFO, $logger->getLogLevel());

        $logger->setLogLevel(99);
        $I->assertSame(Enum::CUSTOM, $logger->getLogLevel());
    }
}
