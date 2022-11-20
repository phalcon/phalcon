<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Pdo\Profiler\Profiler;

use DatabaseTester;
use Phalcon\DataMapper\Pdo\Profiler\Profiler;
use Phalcon\Logger\Enum;
use Phalcon\Logger\LogLevel;

class GetSetLogLevelCest
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Profiler\Profiler ::
     * getLogLevel()/setLogLevel()
     *
     * @since  2020-01-25
     *
     * @group  pgsql
     * @group  mysql
     * @group  sqlite
     */
    public function dMPdoProfilerProfilerGetSetLogLevel(DatabaseTester $I)
    {
        $I->wantToTest('DataMapper\Pdo\Profiler\Profiler - getLogLevel()/setLogLevel()');

        $profiler = new Profiler();

        $I->assertEquals(
            Enum::DEBUG,
            $profiler->getLogLevel()
        );

        $profiler->setLogLevel(Enum::INFO);
        $I->assertEquals(
            Enum::INFO,
            $profiler->getLogLevel()
        );
    }
}
