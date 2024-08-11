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

namespace Phalcon\Tests\Unit\Cache\Cache;

use Phalcon\Cache\AdapterFactory;
use Phalcon\Cache\Cache;
use Phalcon\Cache\Exception\Exception;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Tests\AbstractUnitTestCase;

use function uniqid;

final class GetSetTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Cache :: get() - exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheCacheGetSetExceptionGetInvalid(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The key contains invalid characters');

        $serializer = new SerializerFactory();
        $factory    = new AdapterFactory($serializer);
        $instance   = $factory->newInstance('apcu');

        $adapter = new Cache($instance);
        $value   = $adapter->get('abc$^');
    }

    /**
     * Tests Phalcon\Cache :: get() - exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheCacheGetSetExceptionSetInvalid(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The key contains invalid characters');

        $serializer = new SerializerFactory();
        $factory    = new AdapterFactory($serializer);
        $instance   = $factory->newInstance('apcu');

        $adapter = new Cache($instance);
        $value   = $adapter->set('abc$^', 'test');
    }

    /**
     * Tests Phalcon\Cache :: get()/set()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheCacheSetGet(): void
    {
        $serializer = new SerializerFactory();
        $factory    = new AdapterFactory($serializer);
        $instance   = $factory->newInstance('apcu');

        $adapter = new Cache($instance);

        $key1 = uniqid();
        $key2 = uniqid();
        $key3 = 'key.' . uniqid();


        $adapter->set($key1, 'test');
        $this->assertTrue($adapter->has($key1));

        $adapter->set($key2, 'test');
        $this->assertTrue($adapter->has($key2));

        $adapter->set($key3, 'test');
        $this->assertTrue($adapter->has($key3));
        $this->assertEquals('test', $adapter->get($key1));
        $this->assertEquals('test', $adapter->get($key2));
        $this->assertEquals('test', $adapter->get($key3));
    }
}
