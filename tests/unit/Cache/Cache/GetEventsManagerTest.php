<?php

/**
 * This file is part of the Phalcon Framework.
 * (c) Phalcon Team <team@phalcon.io>
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Cache\Cache;

use Phalcon\Cache\AdapterFactory;
use Phalcon\Cache\Cache;
use Phalcon\Events\Manager;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Tests\UnitTestCase;

final class GetEventsManagerTest extends UnitTestCase
{
    /**
     * @return array[]
     */
    public static function getEvents(): array
    {
        // Event, Method, Data
        return [
            ['beforeDelete', 'delete', ['test']],
            ['afterDelete', 'delete', ['test']],
            ['beforeDeleteMultiple', 'deleteMultiple', [['test', 'test2']]],
            ['afterDeleteMultiple', 'deleteMultiple', [['test', 'test2']]],
            ['beforeGet', 'get', ['test']],
            ['afterGet', 'get', ['test']],
            ['beforeGetMultiple', 'getMultiple', [['test', 'test2']]],
            ['afterGetMultiple', 'getMultiple', [['test', 'test2']]],
            ['beforeHas', 'has', ['test']],
            ['afterHas', 'has', ['test']],
            ['beforeSet', 'set', ['test', 'test']],
            ['afterSet', 'set', ['test', 'test']],
            ['beforeSetMultiple', 'setMultiple', [['test' => 'test', 'test2' => 'test2']]],
            ['afterSetMultiple', 'setMultiple', [['test' => 'test', 'test2' => 'test2']]],
        ];
    }

    /**
     * Tests Phalcon\Cache :: trigger cache events
     *
     * @dataProvider getEvents
     * @author       n[oO]ne <lominum@protonmail.com>
     * @since        2024-06-07
     */
    public function testCacheCacheEventTriggers(
        string $event,
        string $method,
        array $data
    ): void {
        $serializer = new SerializerFactory();
        $factory    = new AdapterFactory($serializer);
        $instance   = $factory->newInstance('memory');

        $counter = 0;
        $adapter = new Cache($instance);
        $manager = new Manager();

        $manager->attach(
            'cache:' . $event,
            static function () use (&$counter) {
                $counter++;
            }
        );

        $adapter->setEventsManager($manager);

        $this->assertInstanceOf($manager::class, $adapter->getEventsManager());

        call_user_func_array([$adapter, $method], $data);
        call_user_func_array([$adapter, $method], $data);
        $this->assertEquals(2, $counter);
    }

    /**
     * Tests Phalcon\Cache :: getEventsManager()
     *
     * @author n[oO]ne <lominum@protonmail.com>
     * @since  2024-06-07
     */
    public function testCacheCacheGetEventsManagerNotSet(): void
    {
        $serializer = new SerializerFactory();
        $factory    = new AdapterFactory($serializer);
        $instance   = $factory->newInstance('memory');

        $adapter = new Cache($instance);

        $this->assertNull($adapter->getEventsManager());
    }

    /**
     * Tests Phalcon\Cache :: getEventsManager()
     *
     * @author n[oO]ne <lominum@protonmail.com>
     * @since  2024-06-07
     */
    public function testCacheCacheGetEventsManagerSet(): void
    {
        $serializer = new SerializerFactory();
        $factory    = new AdapterFactory($serializer);
        $instance   = $factory->newInstance('memory');

        $adapter = new Cache($instance);

        $adapter->setEventsManager(new Manager());

        $this->assertInstanceOf(Manager::class, $adapter->getEventsManager());
    }
}
