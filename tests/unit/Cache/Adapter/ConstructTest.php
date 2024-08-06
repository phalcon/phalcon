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

use DateInterval;
use Phalcon\Cache\Adapter\AdapterInterface;
use Phalcon\Cache\Adapter\Apcu;
use Phalcon\Cache\Adapter\Libmemcached;
use Phalcon\Cache\Adapter\Memory;
use Phalcon\Cache\Adapter\Redis;
use Phalcon\Cache\Adapter\Stream;
use Phalcon\Cache\Adapter\Weak;
use Phalcon\Storage\Exception as CacheException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as SupportException;
use Phalcon\Tests\Fixtures\Cache\Adapter\Libmemcached as LibmemcachedFixture;
use Phalcon\Tests\UnitTestCase;

use function getOptionsLibmemcached;
use function getOptionsRedis;
use function outputDir;

final class ConstructTest extends UnitTestCase
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
                Stream::class,
                [
                    'storageDir' => outputDir(),
                ],
                '',
            ],
            [
                Weak::class,
                [],
                '',
            ],
        ];
    }

    /**
     * Tests Phalcon\Cache\Adapter\* :: __construct()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testCacheAdapterConstruct(
        string $class,
        array $options,
        string $extension
    ): void {
        if (!empty($extension)) {
            $this->checkExtensionIsLoaded($extension);
        }

        $serializer = new SerializerFactory();
        $adapter    = new $class($serializer, $options);

        $this->assertInstanceOf($class, $adapter);
        $this->assertInstanceOf(AdapterInterface::class, $adapter);
    }

    /**
     * Tests Phalcon\Cache\Adapter\Libmemcached :: __construct() - empty
     * options
     *
     * @return void
     *
     * @throws SupportException
     * @since  2020-09-09
     *
     * @author Phalcon Team <team@phalcon.io>
     */
    public function testCacheAdapterLibmemcachedConstructEmptyOptions(): void
    {
        $this->checkExtensionIsLoaded('memcached');
        $serializer = new SerializerFactory();
        $adapter    = new LibmemcachedFixture($serializer);

        $expected = [
            'servers' => [
                0 => [
                    'host'   => '127.0.0.1',
                    'port'   => 11211,
                    'weight' => 1,
                ],
            ],
        ];
        $actual   = $adapter->getOptions();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Cache\Adapter\Libmemcached :: __construct() - getTtl
     * options
     *
     * @return void
     *
     * @throws SupportException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheAdapterLibmemcachedConstructGetTtl(): void
    {
        $this->checkExtensionIsLoaded('memcached');
        $serializer = new SerializerFactory();
        $adapter    = new LibmemcachedFixture(
            $serializer,
            getOptionsLibmemcached()
        );

        $expected = 3600;
        $actual   = $adapter->getTtl(null);
        $this->assertEquals($expected, $actual);

        $expected = 20;
        $actual   = $adapter->getTtl(20);
        $this->assertEquals($expected, $actual);

        $time     = new DateInterval('PT5S');
        $expected = 5;
        $actual   = $adapter->getTtl($time);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Cache\Adapter\Stream :: __construct() - exception
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheAdapterStreamConstructException(): void
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessage(
            "The 'storageDir' must be specified in the options"
        );

        $serializer = new SerializerFactory();
        (new Stream($serializer));
    }
}
