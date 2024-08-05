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

namespace Phalcon\Tests\Unit\Session\Adapter;

use Phalcon\Session\Adapter\Libmemcached;
use Phalcon\Session\Adapter\Redis;
use Phalcon\Storage\AdapterFactory;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Fixtures\Traits\SessionTrait;
use Phalcon\Tests\UnitTestCase;
use SessionHandlerInterface;

use function getOptionsRedis;

final class ConstructTest extends UnitTestCase
{
    use DiTrait;
    use SessionTrait;

    /**
     * Tests Phalcon\Session\Adapter\ :: __construct()
     *
     * @dataProvider getClassNames
     * *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testSessionAdapterConstruct(
        string $name
    ): void {
        $adapter = $this->newService($name);

        $class = SessionHandlerInterface::class;
        $this->assertInstanceOf($class, $adapter);
    }

    /**
     * Tests Phalcon\Session\Adapter\Libmemcached :: __construct() - with custom prefix
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-10-23
     */
    public function testSessionAdapterLibmemcachedConstructWithPrefix(): void
    {
        $options           = getOptionsLibmemcached();
        $options['prefix'] = 'my-custom-prefix-';

        $serializerFactory = new SerializerFactory();
        $factory           = new AdapterFactory($serializerFactory);

        $memcachedSession = new Libmemcached($factory, $options);

        $actual = $memcachedSession->write(
            'my-session-prefixed-key',
            'test-data'
        );

        $this->assertTrue($actual);

        $memcachedStorage = $factory->newInstance('libmemcached', $options);

        $expected = 'my-custom-prefix-';
        $actual   = $memcachedStorage->getPrefix();
        $this->assertEquals($expected, $actual);

        $expected = 'test-data';
        $actual   = $memcachedStorage->get('my-session-prefixed-key');
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Session\Adapter\Redis :: __construct() - with custom prefix
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-10-23
     */
    public function testSessionAdapterRedisConstructWithPrefix(): void
    {
        $options           = getOptionsRedis();
        $options['prefix'] = 'my-custom-prefix-';

        $serializerFactory = new SerializerFactory();
        $factory           = new AdapterFactory($serializerFactory);

        $redisSession = new Redis($factory, $options);

        $actual = $redisSession->write(
            'my-session-prefixed-key',
            'test-data'
        );

        $this->assertTrue($actual);

        $redisStorage = $factory->newInstance('redis', $options);

        $expected = 'my-custom-prefix-';
        $actual   = $redisStorage->getPrefix();
        $this->assertEquals($expected, $actual);

        $expected = 'test-data';
        $actual   = $redisStorage->get('my-session-prefixed-key');
        $this->assertEquals($expected, $actual);
    }
}
