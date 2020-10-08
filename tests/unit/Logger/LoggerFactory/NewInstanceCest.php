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

namespace Phalcon\Tests\Unit\Logger\LoggerFactory;

use Phalcon\Logger\Adapter\Stream;
use Phalcon\Logger\Logger;
use Phalcon\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;
use UnitTester;

use function logsDir;

class NewInstanceCest
{
    /**
     * Tests Phalcon\Logger\LoggerFactory :: newInstance()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function loggerLoggerFactoryNewInstance(UnitTester $I)
    {
        $I->wantToTest('Logger\LoggerFactory - newInstance()');

        $logPath  = logsDir();
        $fileName = $I->getNewFileName('log', 'log');
        $adapter  = new Stream($logPath . $fileName);
        $factory  = new LoggerFactory();
        $logger   = $factory->newInstance(
            'my-logger',
            [
                'one' => $adapter,
            ]
        );

        $I->assertInstanceOf(LoggerInterface::class, $logger);
        $I->assertInstanceOf(Logger::class, $logger);
    }
}
