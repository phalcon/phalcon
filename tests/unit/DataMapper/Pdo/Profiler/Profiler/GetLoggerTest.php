<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\DataMapper\Pdo\Profiler\Profiler;

use Phalcon\Tests\DatabaseTestCase;
use Phalcon\DataMapper\Pdo\Profiler\MemoryLogger;
use Phalcon\DataMapper\Pdo\Profiler\Profiler;

final class GetLoggerTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Profiler\Profiler :: getLogger()
     *
     * @since  2020-01-25
     *
     * @group  common
     */
    public function testDmPdoProfilerProfilerGetLogger(): void
    {
        $profile = new Profiler();
        $logger  = $profile->getLogger();

        $this->assertInstanceOf(MemoryLogger::class, $logger);

        $newLogger = new MemoryLogger();
        $profile   = new Profiler($newLogger);

        $logger = $profile->getLogger();
        $this->assertInstanceOf(MemoryLogger::class, $logger);
        $this->assertEquals($newLogger, $logger);
    }
}
