<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Storage\Adapter;

use Memcached as NativeMemcached;
use Phalcon\Storage\Adapter\Apcu;
use Phalcon\Storage\Adapter\Libmemcached;
use Phalcon\Storage\Adapter\Memory;
use Phalcon\Storage\Adapter\Redis;
use Phalcon\Storage\Adapter\RedisCluster;
use Phalcon\Storage\Adapter\Stream;
use Phalcon\Storage\Adapter\Weak;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Talon\Talon;
use PHPUnit\Framework\Attributes\DataProvider;
use Redis as NativeRedis;
use RedisCluster as NativeRedisCluster;

final class GetAdapterTest extends AbstractUnitTestCase
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
                null,
                'apcu',
            ],
            [
                Libmemcached::class,
                [
                    'client'  => [],
                    'servers' => [
                        Talon::settings()->getServiceOptions('memcached')
                    ]
                ],
                NativeMemcached::class,
                'memcached',
            ],
            [
                Memory::class,
                [],
                null,
                '',
            ],
            [
                Redis::class,
                Talon::settings()->getServiceOptions('redis'),
                NativeRedis::class,
                'redis',
            ],
            [
                RedisCluster::class,
                Talon::settings()->getServiceOptions('redisCluster'),
                NativeRedisCluster::class,
                'redis',
            ],
            [
                Stream::class,
                [
                    'storageDir' => Talon::settings()->outputPath() . '/',
                ],
                null,
                '',
            ],
            [
                Weak::class,
                [],
                null,
                '',
            ],
        ];
    }

    /**
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    #[DataProvider('getExamples')]
    public function testStorageAdapterGetAdapter(
        string $class,
        array $options,
        ?string $expected,
        string $extension,
    ): void {
        if (!empty($extension)) {
            $this->checkExtensionIsLoaded($extension);
        }

        $serializer = new SerializerFactory();
        $adapter    = new $class($serializer, $options);

        $actual = $adapter->getAdapter();

        if (null === $expected) {
            $this->assertNull($actual);
        } else {
            $this->assertInstanceOf($expected, $actual);
        }
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/863
     */
    public function testStorageAdapterGetAdapterLibmemcachedClientOptions(): void
    {
        $this->checkExtensionIsLoaded('memcached');

        $adapter = new Libmemcached(
            new SerializerFactory(),
            [
                'client'       => [
                    NativeMemcached::OPT_POLL_TIMEOUT    => 500,
                    NativeMemcached::OPT_CONNECT_TIMEOUT => 100,
                ],
                'persistentId' => uniqid('ph-mcid-client-'),
                'servers'      => [
                    Talon::settings()->getServiceOptions('memcached'),
                ],
            ]
        );

        $connection = $adapter->getAdapter();

        $this->assertSame(500, $connection->getOption(NativeMemcached::OPT_POLL_TIMEOUT));
        $this->assertSame(100, $connection->getOption(NativeMemcached::OPT_CONNECT_TIMEOUT));
        $this->assertSame(2, $connection->getOption(NativeMemcached::OPT_SERVER_FAILURE_LIMIT));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/863
     */
    public function testStorageAdapterGetAdapterLibmemcachedFailoverOptions(): void
    {
        $this->checkExtensionIsLoaded('memcached');

        $adapter = new Libmemcached(
            new SerializerFactory(),
            [
                'persistentId' => uniqid('ph-mcid-failover-'),
                'servers'      => [
                    Talon::settings()->getServiceOptions('memcached'),
                ],
            ]
        );

        $connection = $adapter->getAdapter();

        $this->assertSame(50, $connection->getOption(NativeMemcached::OPT_CONNECT_TIMEOUT));
        $this->assertSame(
            NativeMemcached::DISTRIBUTION_CONSISTENT,
            $connection->getOption(NativeMemcached::OPT_DISTRIBUTION)
        );
        $this->assertSame(2, $connection->getOption(NativeMemcached::OPT_SERVER_FAILURE_LIMIT));
        $this->assertSame(1, $connection->getOption(NativeMemcached::OPT_REMOVE_FAILED_SERVERS));
        $this->assertSame(1, $connection->getOption(NativeMemcached::OPT_RETRY_TIMEOUT));

        /**
         * The options that the adapter does not set keep the library value
         */
        $native  = new NativeMemcached();
        $options = [
            NativeMemcached::OPT_NO_BLOCK,
            NativeMemcached::OPT_TCP_NODELAY,
            NativeMemcached::OPT_HASH,
        ];
        foreach ($options as $option) {
            $this->assertSame(
                $native->getOption($option),
                $connection->getOption($option)
            );
        }
    }
}
