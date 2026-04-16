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
use Phalcon\Tests\AbstractDatabaseTestCase;

final class GetSetLogLevelTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Profiler\Profiler ::
     * getLogLevel()/setLogLevel()
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoProfilerProfilerGetSetLogLevel(): void
    {
        $profiler = new Profiler();

        $this->assertSame(
            Enum::DEBUG,
            $profiler->getLogLevel()
        );

        $profiler->setLogLevel(Enum::INFO);
        $this->assertSame(
            Enum::INFO,
            $profiler->getLogLevel()
        );
    }
}
