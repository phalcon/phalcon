<?php

/**
 * This file is part of the Phalcon Framework.
 * (c) Phalcon Team <team@phalcon.io>
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Cache\Adapter\Redis;

use Phalcon\Cache\Adapter\Redis;
use Phalcon\Events\Event;
use Phalcon\Events\Manager;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Tests\UnitTestCase;
use RuntimeException;

final class EventsTest extends UnitTestCase
{
    public static function getEvents(): array
    {
        // Event, Method, Data
        return [
            ['beforeDelete', 'delete', ['test']],
            ['afterDelete', 'delete', ['test']],
            ['beforeIncrement', 'increment', ['test']],
            ['afterIncrement', 'increment', ['test']],
            ['beforeGet', 'get', ['test']],
            ['afterGet', 'get', ['test']],
            ['beforeDecrement', 'decrement', ['test']],
            ['afterDecrement', 'decrement', ['test']],
            ['beforeHas', 'has', ['test']],
            ['afterHas', 'has', ['test']],
            ['beforeSet', 'set', ['test', 'test']],
            ['afterSet', 'set', ['test', 'test']],
        ];
    }

    /**
     * Tests Cache\Adapter\Redis :: getEventsManager()
     *
     * @author n[oO]ne <lominum@protonmail.com>
     * @since  2024-06-07
     */
    public function testCacheAdapterRedisGetEventsManagerNotSet(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Redis($serializer, getOptionsRedis());

        $this->assertNull($adapter->getEventsManager());
    }

    /**
     * Tests Cache\Adapter\Redis :: getEventsManager()
     *
     * @author n[oO]ne <lominum@protonmail.com>
     * @since  2024-06-07
     */
    public function testCacheAdapterRedisGetEventsManagerSet(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Redis($serializer, getOptionsRedis());

        $adapter->setEventsManager(new Manager());

        $this->assertInstanceOf(Manager::class, $adapter->getEventsManager());
    }

    /**
     * Tests Cache\Adapter\Redis :: trigger cache events
     *
     * @dataProvider getEvents
     * @author       n[oO]ne <lominum@protonmail.com>
     * @since        2024-06-07
     */
    public function testCacheCacheEventTriggers(
        string $eventName,
        string $method,
        array $data
    ): void {
        $serializer = new SerializerFactory();
        $adapter    = new Redis($serializer, getOptionsRedis());

        $counter = 0;
        $manager = new Manager();
        $adapter->setEventsManager(new Manager());

        $manager->attach(
            'cache:' . $eventName,
            static function (Event $event) use (&$counter): void {
                $counter++;
                $data = $event->getData();
                $data === 'test' ?: throw new RuntimeException('wrong key');
            }
        );

        $adapter->setEventsManager($manager);

        $this->assertInstanceOf($manager::class, $adapter->getEventsManager());

        call_user_func_array([$adapter, $method], $data);
        call_user_func_array([$adapter, $method], $data);
        $this->assertEquals(2, $counter);
    }
}
