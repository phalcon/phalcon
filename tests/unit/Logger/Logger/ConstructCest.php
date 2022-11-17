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

use Phalcon\Logger\Adapter\Stream;
use Phalcon\Logger\Enum;
use Phalcon\Logger\Exception;
use Phalcon\Logger\Formatter\Json;
use Phalcon\Logger\Logger;
use Phalcon\Logger\LoggerInterface;
use UnitTester;

class ConstructCest
{
    /**
     * Tests Phalcon\Logger :: __construct() - implement PSR
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function loggerConstruct(UnitTester $I)
    {
        $I->wantToTest('Logger - __construct()');

        $logger = new Logger('my-logger');
        $I->assertInstanceOf(LoggerInterface::class, $logger);
    }

    /**
     * Tests Phalcon\Logger :: __construct() - constants
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function loggerConstructConstants(UnitTester $I)
    {
        $I->wantToTest('Logger - __construct() - constants');

        $I->assertSame(2, Enum::ALERT);
        $I->assertSame(1, Enum::CRITICAL);
        $I->assertSame(7, Enum::DEBUG);
        $I->assertSame(0, Enum::EMERGENCY);
        $I->assertSame(3, Enum::ERROR);
        $I->assertSame(6, Enum::INFO);
        $I->assertSame(5, Enum::NOTICE);
        $I->assertSame(4, Enum::WARNING);
        $I->assertSame(8, Enum::CUSTOM);
    }

    /**
     * Tests Phalcon\Logger :: __construct() - file with json formatter
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function loggerConstructStreamWithJsonConstants(UnitTester $I)
    {
        $I->wantToTest('Logger - __construct() - file with json formatter');

        $fileName   = $I->getNewFileName('log', 'log');
        $outputPath = logsDir();
        $adapter    = new Stream($outputPath . $fileName);

        $adapter->setFormatter(new Json());

        $logger = new Logger(
            'my-logger',
            [
                'one' => $adapter,
            ]
        );

        $time = time();

        $logger->debug('This is a message');
        $logger->log(Enum::ERROR, 'This is an error');
        $logger->error('This is another error');

        $I->amInPath($outputPath);
        $I->openFile($fileName);

        $expected = sprintf(
            '{"level":"DEBUG","message":"This is a message","timestamp":"%s"}' . PHP_EOL .
            '{"level":"ERROR","message":"This is an error","timestamp":"%s"}' . PHP_EOL .
            '{"level":"ERROR","message":"This is another error","timestamp":"%s"}',
            date('c', $time),
            date('c', $time),
            date('c', $time)
        );

        $I->seeInThisFile($expected);

        $adapter->close();
        $I->safeDeleteFile($outputPath . $fileName);
    }

    /**
     * Tests Phalcon\Logger :: __construct() - read only mode exception
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function loggerConstructStreamReadOnlyModeException(UnitTester $I)
    {
        $I->wantToTest('Logger - __construct() - read only mode exception');

        $fileName = $I->getNewFileName('log', 'log');

        $outputPath = logsDir();

        $file = $outputPath . $fileName;

        $I->expectThrowable(
            new Exception('Adapter cannot be opened in read mode'),
            function () use ($file) {
                $adapter = new Stream(
                    $file,
                    [
                        'mode' => 'r',
                    ]
                );
            }
        );
    }

    /**
     * Tests Phalcon\Logger :: __construct() - no adapter exception
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function loggerConstructNoAdapterException(UnitTester $I)
    {
        $I->wantToTest('Logger - __construct() - no adapter exception');

        $I->expectThrowable(
            new Exception('No adapters specified'),
            function () {
                $logger = new Logger('my-logger');

                $logger->info('Some message');
            }
        );
    }
}
