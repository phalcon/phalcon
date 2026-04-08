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

namespace Phalcon\Tests\Unit\Cache\Adapter;

use ArrayObject;
use Phalcon\Cache\Adapter\Apcu;
use Phalcon\Cache\Adapter\Libmemcached;
use Phalcon\Cache\Adapter\Memory;
use Phalcon\Cache\Adapter\Redis;
use Phalcon\Cache\Adapter\RedisCluster;
use Phalcon\Cache\Adapter\Stream;
use Phalcon\Cache\Adapter\Weak;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Tests\AbstractUnitTestCase;
use SplObjectStorage;
use SplQueue;
use stdClass;

use function array_merge;
use function file_get_contents;
use function getOptionsLibmemcached;
use function getOptionsRedis;
use function getOptionsRedisCluster;
use function outputDir;
use function sort;
use function uniqid;

final class GetSetTest extends AbstractUnitTestCase
{
    /**
     * @return array[]
     */
    public static function getExamples(): array
    {
        return [
            [
                'apcu',
                Apcu::class,
                [],
                null,
            ],
            [
                'apcu',
                Apcu::class,
                [],
                true,
            ],
            [
                'apcu',
                Apcu::class,
                [],
                false,
            ],
            [
                'apcu',
                Apcu::class,
                [],
                123456,
            ],
            [
                'apcu',
                Apcu::class,
                [],
                123.456,
            ],
            [
                'apcu',
                Apcu::class,
                [],
                uniqid(),
            ],
            [
                'apcu',
                Apcu::class,
                [],
                new stdClass(),
            ],
            [
                'memcached',
                Libmemcached::class,
                getOptionsLibmemcached(),
                null,
            ],
            [
                'memcached',
                Libmemcached::class,
                getOptionsLibmemcached(),
                true,
            ],
            [
                'memcached',
                Libmemcached::class,
                getOptionsLibmemcached(),
                false,
            ],
            [
                'memcached',
                Libmemcached::class,
                getOptionsLibmemcached(),
                123456,
            ],
            [
                'memcached',
                Libmemcached::class,
                getOptionsLibmemcached(),
                123.456,
            ],
            [
                'memcached',
                Libmemcached::class,
                getOptionsLibmemcached(),
                uniqid(),
            ],
            [
                'memcached',
                Libmemcached::class,
                getOptionsLibmemcached(),
                new stdClass(),
            ],
            [
                'memcached',
                Libmemcached::class,
                array_merge(
                    getOptionsLibmemcached(),
                    [
                        'defaultSerializer' => 'Base64',
                    ]
                ),
                uniqid(),
            ],
            [
                '',
                Memory::class,
                [],
                null,
            ],
            [
                '',
                Memory::class,
                [],
                true,
            ],
            [
                '',
                Memory::class,
                [],
                false,
            ],
            [
                '',
                Memory::class,
                [],
                123456,
            ],
            [
                '',
                Memory::class,
                [],
                123.456,
            ],
            [
                '',
                Memory::class,
                [],
                uniqid(),
            ],
            [
                '',
                Memory::class,
                [],
                new stdClass(),
            ],
            [
                'redis',
                Redis::class,
                getOptionsRedis(),
                null,
            ],
            [
                'redis',
                Redis::class,
                getOptionsRedis(),
                true,
            ],
            [
                'redis',
                Redis::class,
                getOptionsRedis(),
                false,
            ],
            [
                'redis',
                Redis::class,
                getOptionsRedis(),
                123456,
            ],
            [
                'redis',
                Redis::class,
                getOptionsRedis(),
                123.456,
            ],
            [
                'redis',
                Redis::class,
                getOptionsRedis(),
                uniqid(),
            ],
            [
                'redis',
                Redis::class,
                getOptionsRedis(),
                new stdClass(),
            ],
            [
                'redis',
                Redis::class,
                array_merge(
                    getOptionsRedis(),
                    [
                        'defaultSerializer' => 'Base64',
                    ]
                ),
                uniqid(),
            ],
            [
                'redis',
                Redis::class,
                array_merge(
                    getOptionsRedis(),
                    [
                        'persistent' => true,
                    ]
                ),
                uniqid(),
            ],
            [
                'redis',
                RedisCluster::class,
                getOptionsRedisCluster(),
                null,
            ],
            [
                'redis',
                RedisCluster::class,
                getOptionsRedisCluster(),
                true,
            ],
            [
                'redis',
                RedisCluster::class,
                getOptionsRedisCluster(),
                false,
            ],
            [
                'redis',
                RedisCluster::class,
                getOptionsRedisCluster(),
                123456,
            ],
            [
                'redis',
                RedisCluster::class,
                getOptionsRedisCluster(),
                123.456,
            ],
            [
                'redis',
                RedisCluster::class,
                getOptionsRedisCluster(),
                uniqid(),
            ],
            [
                'redis',
                RedisCluster::class,
                getOptionsRedisCluster(),
                new stdClass(),
            ],
            [
                '',
                Stream::class,
                [
                    'storageDir' => outputDir(),
                ],
                null,
            ],
            [
                '',
                Stream::class,
                [
                    'storageDir' => outputDir(),
                ],
                true,
            ],
            [
                '',
                Stream::class,
                [
                    'storageDir' => outputDir(),
                ],
                false,
            ],
            [
                '',
                Stream::class,
                [
                    'storageDir' => outputDir(),
                ],
                123456,
            ],
            [
                '',
                Stream::class,
                [
                    'storageDir' => outputDir(),
                ],
                123.456,
            ],
            [
                '',
                Stream::class,
                [
                    'storageDir' => outputDir(),
                ],
                uniqid(),
            ],
            [
                '',
                Stream::class,
                [
                    'storageDir' => outputDir(),
                ],
                new stdClass(),
            ],
        ];
    }

    /**
     * Tests Phalcon\Cache\Adapter\* :: get()/set()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testStorageAdapterGetSet(
        string $extension,
        string $class,
        array $options,
        mixed $value
    ): void {
        if (!empty($extension)) {
            $this->checkExtensionIsLoaded($extension);
        }

        $serializer = new SerializerFactory();
        $adapter    = new $class($serializer, $options);

        $key = uniqid('k-');

        $result = $adapter->set($key, $value);
        $this->assertTrue($result);

        $result = $adapter->has($key);
        $this->assertTrue($result);

        /**
         * This will issue delete
         */
        $result = $adapter->set($key, $value, 0);
        $this->assertTrue($result);

        $result = $adapter->has($key);
        $this->assertFalse($result);
    }

    /**
     * @return array[]
     */
    public static function getAdapters(): array
    {
        return [
            [
                Apcu::class,
                [],
                'apcu',
            ],
            [
                Libmemcached::class,
                getOptionsLibmemcached(),
                'memcached',
            ],
            [
                Memory::class,
                [],
                '',
            ],
            [
                Redis::class,
                getOptionsRedis(),
                'redis',
            ],
            [
                Stream::class,
                [
                    'storageDir' => outputDir(),
                ],
                '',
            ],
        ];
    }

    /**
     * Tests Phalcon\Cache\Adapter\* :: get()/set()
     *
     * @dataProvider getAdapters
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testStorageAdapterGetSetWithZeroTtl(
        string $class,
        array $options,
        string $extension
    ): void {
        if (!empty($extension)) {
            $this->checkExtensionIsLoaded($extension);
        }

        $serializer = new SerializerFactory();
        $adapter    = new $class($serializer, $options);

        $key = uniqid();

        $result = $adapter->set($key, "test");
        $this->assertTrue($result);

        $result = $adapter->has($key);
        $this->assertTrue($result);

        /**
         * This will issue delete
         */
        $result = $adapter->set($key, "test", 0);
        $this->assertTrue($result);

        $result = $adapter->has($key);
        $this->assertFalse($result);
    }

    /**
     * Tests Phalcon\Cache\Adapter\Stream :: set() - file content
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheAdapterStreamSet(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $serializer,
            [
                'storageDir' => outputDir(),
            ]
        );

        $data   = 'Phalcon Framework';
        $actual = $adapter->set('test-key', $data);
        $this->assertTrue($actual);

        $target   = outputDir() . 'ph-strm/te/st/-k/';
        $expected = 's:3:"ttl";i:3600;s:7:"content";s:25:"s:17:"Phalcon Framework";";}';
        $actual   = file_get_contents($target . 'test-key');
        $this->assertStringContainsString($expected, $actual);

        $this->safeDeleteFile($target . 'test-key');
    }

    /**
     * Tests Phalcon\Cache\Adapter\Stream :: get() - with prefix
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2023-06-01
     * @issue  16348
     */
    public function testCacheAdapterStreamGetWithPrefix(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $serializer,
            [
                'storageDir' => outputDir(),
                'prefix'     => 'en',
            ]
        );

        $target = outputDir() . 'en/';

        $actual = $adapter->set('men', 123);
        $this->assertTrue($actual);
        $this->assertEquals(123, $adapter->get('men'));

        $actual = $adapter->set('barmen', 'abc');
        $this->assertTrue($actual);
        $this->assertEquals('abc', $adapter->get('barmen'));

        $actual = $adapter->set('bar', 'xyz');
        $this->assertTrue($actual);
        $this->assertEquals('xyz', $adapter->get('bar'));

        $expected = ['enbar', 'enbarmen', 'enmen'];
        $actual   = $adapter->getKeys();
        sort($actual);
        $this->assertEquals($expected, $actual);

        $this->safeDeleteDirectory($target);
    }

    /**
     * Tests Phalcon\Cache\Adapter\Weak :: get()/set()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheAdapterWeakGetSet(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Weak($serializer);

        $objects = [
            new stdClass(),
            new ArrayObject(),
            new SplObjectStorage(),
            new SplQueue(),
        ];

        foreach ($objects as $object) {
            $key    = uniqid();
            $result = $adapter->set($key, $object);
            $this->assertTrue($result);

            $expected = $object;
            $actual   = $adapter->get($key);
            $this->assertEquals($expected, $actual);
        }
    }
}
