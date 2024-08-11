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

final class SetMultipleTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Cache :: setMultiple()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheCacheSetMultiple(): void
    {
        $serializer = new SerializerFactory();
        $factory    = new AdapterFactory($serializer);
        $instance   = $factory->newInstance('apcu');

        $adapter = new Cache($instance);

        $key1 = uniqid();
        $key2 = uniqid();
        $adapter->setMultiple(
            [
                $key1 => 'test1',
                $key2 => 'test2',
            ]
        );

        $this->assertTrue($adapter->has($key1));
        $this->assertTrue($adapter->has($key2));

        $expected = [
            $key1     => 'test1',
            $key2     => 'test2',
            'unknown' => 'default-unknown',
        ];
        $actual   = $adapter->getMultiple([$key1, $key2, 'unknown'], 'default-unknown');
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Cache :: setMultiple() - exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheCacheSetMultipleExceptionInvalidCharacters(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The key contains invalid characters');

        $serializer = new SerializerFactory();
        $factory    = new AdapterFactory($serializer);
        $instance   = $factory->newInstance('apcu');

        $adapter = new Cache($instance);

        $adapter->setMultiple(
            [
                'abc$^' => 'test1',
                'abd$^' => 'test2',
            ]
        );
    }

    /**
     * Tests Phalcon\Cache :: setMultiple() - false
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheCacheSetMultipleFalse(): void
    {
        $serializer = new SerializerFactory();
        $factory    = new AdapterFactory($serializer);
        $instance   = $factory->newInstance('apcu');

        $mock = $this
            ->getMockBuilder(Cache::class)
            ->setConstructorArgs([$instance])
            ->getMock()
        ;
        $mock->method('set')->willReturn(false);

        $key1   = uniqid();
        $key2   = uniqid();
        $actual = $mock->setMultiple(
            [
                $key1 => 'test1',
                $key2 => 'test2',
            ]
        );

        $this->assertFalse($actual);
    }
}
