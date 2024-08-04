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

use Codeception\Example;
use Phalcon\Tests\UnitTestCase;
use Phalcon\Storage\Adapter\Apcu;
use Phalcon\Storage\Adapter\Libmemcached;
use Phalcon\Storage\Adapter\Memory;
use Phalcon\Storage\Adapter\Redis;
use Phalcon\Storage\Adapter\Stream;
use Phalcon\Storage\SerializerFactory;

use function getOptionsLibmemcached;
use function getOptionsRedis;
use function outputDir;
use function uniqid;

final class IncrementTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Storage\Adapter\* :: increment()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testStorageAdapterIncrement(
        string $className,
        string $class,
        array $options,
        string $extension,
        mixed $unknown
    ): void {
        if (!empty($extension)) {
            $this->checkExtensionIsLoaded($extension);
        }

        $serializer = new SerializerFactory();
        $adapter    = new $class($serializer, $options);

        $key    = uniqid();
        $result = $adapter->set($key, 1);
        $this->assertTrue($result);

        $expected = 2;
        $actual   = $adapter->increment($key);
        $this->assertSame($expected, $actual);

        $actual = $adapter->get($key);
        $this->assertSame($expected, $actual);

        $expected = 20;
        $actual   = $adapter->increment($key, 18);
        $this->assertSame($expected, $actual);

        $actual = $adapter->get($key);
        $this->assertSame($expected, $actual);

        /**
         * unknown key
         */
        $key      = uniqid();
        $expected = $unknown;
        $actual   = $adapter->increment($key);
        $this->assertSame($expected, $actual);

        if ('Stream' === $className) {
            $this->safeDeleteDirectory(outputDir('ph-strm'));
        }
    }

    /**
     * Tests Phalcon\Storage\Adapter\Redis :: increment()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testStorageAdapterRedisIncrement(): void
    {
        $this->checkExtensionIsLoaded('redis');

        $serializer = new SerializerFactory();
        $adapter    = new Redis($serializer, getOptionsRedis());

        $key      = uniqid();
        $expected = 1;
        $actual   = $adapter->increment($key, 1);
        $this->assertEquals($expected, $actual);

        $actual = $adapter->get($key);
        $this->assertEquals($expected, $actual);

        $expected = 2;
        $actual   = $adapter->increment($key);
        $this->assertEquals($expected, $actual);

        $actual = $adapter->get($key);
        $this->assertEquals($expected, $actual);

        $expected = 10;
        $actual   = $adapter->increment($key, 8);
        $this->assertEquals($expected, $actual);

        $actual = $adapter->get($key);
        $this->assertEquals($expected, $actual);

        /**
         * unknown key
         */
        $key      = uniqid();
        $expected = 1;
        $actual   = $adapter->increment($key);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array[]
     */
    public static function getExamples(): array
    {
        return [
            [
                'Apcu',
                Apcu::class,
                [],
                'apcu',
                1,
            ],
            [
                'Libmemcached',
                Libmemcached::class,
                getOptionsLibmemcached(),
                'memcached',
                false,
            ],
            [
                'Memory',
                Memory::class,
                [],
                '',
                false,
            ],
            [
                'Stream',
                Stream::class,
                [
                    'storageDir' => outputDir(),
                ],
                '',
                false,
            ],
        ];
    }
}
