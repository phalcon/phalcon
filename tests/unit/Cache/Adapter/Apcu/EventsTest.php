<?php

/**
 * This file is part of the Phalcon Framework.
 * (c) Phalcon Team <team@phalcon.io>
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Cache\Adapter\Apcu;

use Codeception\Example;
use Phalcon\Tests\UnitTestCase;
use Phalcon\Cache\Adapter\Apcu;
use Phalcon\Events\Event;
use Phalcon\Events\Manager;
use Phalcon\Storage\SerializerFactory;
use RuntimeException;

final class EventsTest extends UnitTestCase
{
    /**
     * Tests Cache\Adapter\Apcu :: getEventsManager()
     *
     * @author n[oO]ne <lominum@protonmail.com>
     * @since  2024-06-07
     */
    public function testCacheAdapterMemoryGetEventsManagerNotSet(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Apcu($serializer);

        $this->assertNull($adapter->getEventsManager());
    }

    /**
     * Tests Cache\Adapter\Apcu :: getEventsManager()
     *
     * @author n[oO]ne <lominum@protonmail.com>
     * @since  2024-06-07
     */
    public function testCacheAdapterMemoryGetEventsManagerSet(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Apcu($serializer);

        $adapter->setEventsManager(new Manager());

        $this->assertInstanceOf(Manager::class, $adapter->getEventsManager());
    }

    /**
     * Tests Cache\Adapter\Apcu :: trigger cache events
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
        $adapter    = new Apcu($serializer);

        $counter = 0;
        $manager = new Manager();
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
}
