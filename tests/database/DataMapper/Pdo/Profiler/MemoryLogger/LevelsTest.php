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
use Phalcon\Tests\AbstractDatabaseTestCase;

final class LevelsTest extends AbstractDatabaseTestCase
{
    /**
     * @return array
     */
    public static function getExamples(): array
    {
        return [
            [
                'alert',
            ],
            [
                'critical',
            ],
            [
                'debug',
            ],
            [
                'emergency',
            ],
            [
                'error',
            ],
            [
                'info',
            ],
            [
                'notice',
            ],
            [
                'warning',
            ],
        ];
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\Profiler\MemoryLogger ::
     *
     * @dataProvider getExamples
     * @since        2020-01-25
     *
     * @group pgsql
     * @group mysql
     * @group sqlite
     */
    public function testDmPdoProfilerMemoryLoggerLevels(
        string $level
    ): void {
        $logger = new MemoryLogger();

        $logger->$level($level . ' message');
        $expected = [$level . ' message'];
        $message  = $logger->getMessages();

        $this->assertSame($expected, $message);
    }
}
