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

use Phalcon\Logger\Adapter\Stream;
use Phalcon\Tests\UnitTestCase;

final class InTransactionTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Logger\Adapter\Stream :: inTransaction()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testLoggerAdapterStreamInTransaction(): void
    {
         $fileName   = $this->getNewFileName('log', 'log');
        $outputPath = logsDir();
        $adapter    = new Stream($outputPath . $fileName);

        $adapter->begin();

        $actual = $adapter->inTransaction();
        $this->assertTrue($actual);

        $adapter->commit();

        $actual = $adapter->inTransaction();
        $this->assertFalse($actual);

        $adapter->close();
        $this->safeDeleteFile($outputPath . $fileName);
    }
}
