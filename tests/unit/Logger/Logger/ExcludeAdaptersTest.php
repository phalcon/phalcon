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
use Phalcon\Logger\Logger;
use Phalcon\Tests\UnitTestCase;

use function file_get_contents;

final class ExcludeAdaptersTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Logger :: excludeAdapters()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testLoggerExcludeAdapters(): void
    {
        $fileName1  = $this->getNewFileName('log', 'log');
        $fileName2  = $this->getNewFileName('log', 'log');
        $outputPath = logsDir();
        $adapter1   = new Stream($outputPath . $fileName1);
        $adapter2   = new Stream($outputPath . $fileName2);

        $logger = new Logger(
            'my-logger',
            [
                'one' => $adapter1,
                'two' => $adapter2,
            ]
        );

        /**
         * Log into both
         */
        $logger->debug('Hello');

        $contents = file_get_contents($outputPath . $fileName1);
        $this->assertStringContainsString('Hello', $contents);

        $contents = file_get_contents($outputPath . $fileName2);
        $this->assertStringContainsString('Hello', $contents);

        /**
         * Exclude a logger
         */
        $logger
            ->excludeAdapters(['two'])
            ->debug('Goodbye')
        ;

        $contents = file_get_contents($outputPath . $fileName1);
        $this->assertStringContainsString('Goodbye', $contents);

        $contents = file_get_contents($outputPath . $fileName2);
        $this->assertStringNotContainsString('Goodbye', $contents);

        $adapter1->close();
        $adapter2->close();

        $this->safeDeleteFile($fileName1);
        $this->safeDeleteFile($fileName2);
    }
}
