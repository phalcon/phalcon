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

namespace Phalcon\Tests\Unit\Storage\Adapter;

use Phalcon\Storage\Adapter\Apcu;
use Phalcon\Storage\Adapter\Libmemcached;
use Phalcon\Storage\Adapter\Memory;
use Phalcon\Storage\Adapter\Redis;
use Phalcon\Storage\Adapter\RedisCluster;
use Phalcon\Storage\Adapter\Stream;
use Phalcon\Storage\Adapter\Weak;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Tests\UnitTestCase;
use stdClass;

use function array_merge;
use function getOptionsLibmemcached;
use function getOptionsRedis;
use function getOptionsRedisCluster;
use function outputDir;
use function uniqid;

final class GetSetTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Storage\Adapter\Weak :: get()/set()
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2023-07-17
     */
    public function cacheAdapterWeakGetSet(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Weak($serializer);

        $key = uniqid();
        $obj = new stdClass();
        $result = $adapter->set($key, "test");
        $this->assertFalse($result);
        $result = $adapter->set($key, $obj);
        $this->assertTrue($result);
        $result = $adapter->has($key);
        $this->assertTrue($result);

        /**
         * There is no TTl.
         */
        $result = $adapter->set($key, $obj, 0);
        $this->assertTrue($result);

        $result = $adapter->has($key);
        $this->assertTrue($result);
    }

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
                'redis',
                RedisCluster::class,
                array_merge(
                    getOptionsRedisCluster(),
                    [
                        'defaultSerializer' => 'Base64',
                    ]
                ),
                uniqid(),
            ],
            [
                'redis',
                RedisCluster::class,
                array_merge(
                    getOptionsRedisCluster(),
                    [
                        'persistent' => true,
                    ]
                ),
                uniqid(),
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
     * Tests Phalcon\Storage\Adapter\* :: get()/set()
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
}
