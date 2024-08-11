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

use Phalcon\Cache\Adapter\Apcu;
use Phalcon\Cache\Adapter\Libmemcached;
use Phalcon\Cache\Adapter\Memory;
use Phalcon\Cache\Adapter\Redis;
use Phalcon\Cache\Adapter\RedisCluster;
use Phalcon\Cache\Adapter\Stream;
use Phalcon\Cache\Adapter\Weak;
use Phalcon\Cache\Exception\Exception as StorageException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as HelperException;
use Phalcon\Tests\Fixtures\Cache\Adapter\StreamFileGetContentsFixture;
use Phalcon\Tests\Fixtures\Cache\Adapter\StreamFopenFixture;
use Phalcon\Tests\AbstractUnitTestCase;
use stdClass;

use function getOptionsLibmemcached;
use function getOptionsRedis;
use function getOptionsRedisCluster;
use function outputDir;
use function uniqid;

final class HasTest extends AbstractUnitTestCase
{
    /**
     * @return array[]
     */
    public static function getExamples(): array
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
                RedisCluster::class,
                getOptionsRedisCluster(),
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
     * Tests Phalcon\Cache\Adapter\* :: has()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testCacheAdapterHas(
        string $class,
        array $options,
        ?string $extension
    ): void {
        if (!empty($extension)) {
            $this->checkExtensionIsLoaded($extension);
        }

        $serializer = new SerializerFactory();
        $adapter    = new $class($serializer, $options);

        $key = uniqid();

        $actual = $adapter->has($key);
        $this->assertFalse($actual);

        $adapter->set($key, 'test');
        $actual = $adapter->has($key);
        $this->assertTrue($actual);
    }

    /**
     * Tests Phalcon\Cache\Adapter\Stream :: has() - cannot open file
     *
     * @return void
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheAdapterStreamHasCannotOpenFile(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new StreamFopenFixture(
            $serializer,
            [
                'storageDir' => outputDir(),
            ],
        );

        $key    = uniqid();
        $actual = $adapter->set($key, 'test');
        $this->assertTrue($actual);

        $actual = $adapter->has($key);
        $this->assertFalse($actual);
    }

    /**
     * Tests Phalcon\Cache\Adapter\Stream :: has() - empty payload
     *
     * @return void
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheAdapterStreamHasEmptyPayload(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new StreamFileGetContentsFixture(
            $serializer,
            [
                'storageDir' => outputDir(),
            ],
        );

        $key    = uniqid();
        $actual = $adapter->set($key, 'test');
        $this->assertTrue($actual);

        $actual = $adapter->has($key);
        $this->assertFalse($actual);
    }

    /**
     * Tests Phalcon\Cache\Adapter\Weak :: has()
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2023-07-17
     */
    public function testCacheAdapterWeakHas(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Weak($serializer);

        $obj1 = new stdClass();

        $key1   = uniqid();
        $actual = $adapter->has($key1);
        $this->assertFalse($actual);

        $adapter->set($key1, $obj1);

        $actual = $adapter->has($key1);
        $this->assertTrue($actual);
    }
}
