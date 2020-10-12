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

namespace Phalcon\Tests\Unit\Logger\Adapter\Stream;

use DateTimeImmutable;
use DateTimeZone;
use Phalcon\Logger\Adapter\Stream;
use Phalcon\Logger\Item;
use Phalcon\Logger\Logger;
use UnitTester;

use function date_default_timezone_get;

class CloseCest
{
    /**
     * Tests Phalcon\Logger\Adapter\Stream :: close()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function loggerAdapterStreamClose(UnitTester $I)
    {
        $I->wantToTest('Logger\Adapter\Stream - close()');
        $fileName   = $I->getNewFileName('log', 'log');
        $outputPath = logsDir();
        $timezone   = date_default_timezone_get();
        $datetime   = new DateTimeImmutable('now', new DateTimeZone($timezone));
        $adapter    = new Stream($outputPath . $fileName);

        $item = new Item(
            'Message 1',
            'debug',
            Logger::DEBUG,
            $datetime
        );
        $adapter->process($item);

        $actual = $adapter->close();
        $I->assertTrue($actual);

        $I->amInPath($outputPath);
        $I->seeFileFound($fileName);
        $I->openFile($fileName);
        $I->seeInThisFile('Message 1');

        $I->safeDeleteFile($outputPath . $fileName);
    }
}
