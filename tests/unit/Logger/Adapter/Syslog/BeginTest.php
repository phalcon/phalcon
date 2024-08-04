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

namespace Phalcon\Tests\Unit\Logger\Adapter\Syslog;

use Phalcon\Logger\Adapter\Syslog;
use Phalcon\Tests\UnitTestCase;

final class BeginTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Logger\Adapter\Syslog :: begin()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testLoggerAdapterSyslogBegin(): void
    {
        $streamName = $this->getNewFileName('log', 'log');

        $adapter = new Syslog($streamName);

        $adapter->begin();

        $actual = $adapter->inTransaction();
        $this->assertTrue($actual);

        $adapter->rollback();
        $adapter->close();
    }
}
