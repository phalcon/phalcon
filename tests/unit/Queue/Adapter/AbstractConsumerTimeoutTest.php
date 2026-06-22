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

namespace Phalcon\Tests\Unit\Queue\Adapter;

use Phalcon\Queue\Adapter\Memory\MemoryConnectionFactory;
use Phalcon\Queue\Adapter\Memory\MemoryConsumer;
use Phalcon\Tests\AbstractUnitTestCase;

use function microtime;

final class AbstractConsumerTimeoutTest extends AbstractUnitTestCase
{
    public function testReceiveHonoursTimeoutFinerThanPollInterval(): void
    {
        $context = (new MemoryConnectionFactory())->createContext();

        /** @var MemoryConsumer $consumer */
        $consumer = $context->createConsumer($context->createQueue('empty'));
        $consumer->setPollInterval(5000);

        $startTime = microtime(true);
        $message   = $consumer->receive(50);
        $elapsed   = (microtime(true) - $startTime) * 1000;

        $this->assertNull($message);
        $this->assertLessThan(1000, $elapsed);
    }
}
