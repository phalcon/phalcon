<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Pdo\Profiler\Profiler;

use Phalcon\DataMapper\Pdo\Profiler\Profiler;
use Phalcon\Logger\Enum;
use Phalcon\Logger\LogLevel;
use Phalcon\Tests\DatabaseTestCase;

final class GetSetLogLevelTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Profiler\Profiler ::
     * getLogLevel()/setLogLevel()
     *
     * @since  2020-01-25
     *
     * @group  common
     */
    public function testDmPdoProfilerProfilerGetSetLogLevel(): void
    {
        $profiler = new Profiler();

        $this->assertEquals(
            Enum::DEBUG,
            $profiler->getLogLevel()
        );

        $profiler->setLogLevel(Enum::INFO);
        $this->assertEquals(
            Enum::INFO,
            $profiler->getLogLevel()
        );
    }
}
