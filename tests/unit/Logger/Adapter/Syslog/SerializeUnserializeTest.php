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
use Phalcon\Logger\Exception;
use Phalcon\Tests\UnitTestCase;

use function serialize;

final class SerializeUnserializeTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Logger\Adapter\Syslog :: serialize()/unserialize
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-09-03
     * @issue  15638
     */
    public function testLoggerAdapterSyslogSerializeUnserialize(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("This object cannot be serialized");

        $streamName = $this->getNewFileName('log', 'log');
        $adapter    = new Syslog($streamName);

        serialize($adapter);
    }
}
