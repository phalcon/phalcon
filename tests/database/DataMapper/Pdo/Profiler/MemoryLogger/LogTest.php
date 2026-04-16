<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Pdo\Profiler\MemoryLogger;

use Phalcon\DataMapper\Pdo\Profiler\MemoryLogger;
use Phalcon\Logger\Adapter\Noop;
use Phalcon\Logger\Enum;
use Phalcon\Tests\AbstractDatabaseTestCase;

final class LogTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Profiler\MemoryLogger :: log()
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoProfilerMemoryLoggerLog(): void
    {
        $logger = new MemoryLogger();

        $message = "{method} ({duration} seconds): {statement} {backtrace}";
        $context = [
            "method"    => "f1",
            "duration"  => "123",
            "seconds"   => "456",
            "statement" => "select",
            "backtrace" => "backtrace",
        ];

        $logger->log(Enum::INFO, $message, $context);

        $expected = ["f1 (123 seconds): select backtrace"];
        $message  = $logger->getMessages();

        $this->assertSame($expected, $message);
        $this->assertSame(Enum::CUSTOM, $logger->getLogLevel());
        $this->assertSame('memory logger', $logger->getName());
        $this->assertEmpty($logger->getAdapters());
        $this->assertInstanceOf(Noop::class, $logger->getAdapter('unknown'));
    }
}
