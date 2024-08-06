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

use DateInterval;
use Phalcon\Storage\Adapter\AdapterInterface;
use Phalcon\Storage\Adapter\Apcu;
use Phalcon\Storage\Adapter\Libmemcached;
use Phalcon\Storage\Adapter\Memory;
use Phalcon\Storage\Adapter\Redis;
use Phalcon\Storage\Adapter\Stream;
use Phalcon\Storage\Adapter\Weak;
use Phalcon\Storage\Exception as StorageException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as SupportException;
use Phalcon\Tests\Fixtures\Storage\Adapter\Libmemcached as LibmemcachedFixture;
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
     * Tests Phalcon\Storage\Adapter\* :: __construct()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testStorageAdapterConstruct(
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
     * Tests Phalcon\Storage\Adapter\Libmemcached :: __construct() - empty
     * options
     *
     * @return void
     *
     * @throws SupportException
     * @since  2020-09-09
     *
     * @author Phalcon Team <team@phalcon.io>
     */
    public function testStorageAdapterLibmemcachedConstructEmptyOptions(): void
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
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Storage\Adapter\Libmemcached :: __construct() - getTtl
     * options
     *
     * @return void
     *
     * @throws SupportException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testStorageAdapterLibmemcachedConstructGetTtl(): void
    {
        $this->checkExtensionIsLoaded('memcached');
        $serializer = new SerializerFactory();
        $adapter    = new LibmemcachedFixture(
            $serializer,
            getOptionsLibmemcached()
        );

        $expected = 3600;
        $actual   = $adapter->getTtl(null);
        $this->assertSame($expected, $actual);

        $expected = 20;
        $actual   = $adapter->getTtl(20);
        $this->assertSame($expected, $actual);

        $time     = new DateInterval('PT5S');
        $expected = 5;
        $actual   = $adapter->getTtl($time);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Storage\Adapter\Stream :: __construct() - exception
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testStorageAdapterStreamConstructException(): void
    {
        $this->expectException(StorageException::class);
        $this->expectExceptionMessage(
            "The 'storageDir' must be specified in the options"
        );

        $serializer = new SerializerFactory();
        (new Stream($serializer));
    }
}
