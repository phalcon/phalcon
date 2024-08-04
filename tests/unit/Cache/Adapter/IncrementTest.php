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

use Codeception\Example;
use Phalcon\Tests\UnitTestCase;
use Phalcon\Cache\Adapter\Apcu;
use Phalcon\Cache\Adapter\Libmemcached;
use Phalcon\Cache\Adapter\Memory;
use Phalcon\Cache\Adapter\Redis;
use Phalcon\Cache\Adapter\Stream;
use Phalcon\Storage\SerializerFactory;

use function getOptionsLibmemcached;
use function getOptionsRedis;
use function outputDir;
use function uniqid;

final class IncrementTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Cache\Adapter\* :: increment()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testCacheAdapterClear(
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
        $expected = $unknown;
        $actual   = $adapter->increment($key);
        $this->assertEquals($expected, $actual);
    }

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
                1,
            ],
            [
                Libmemcached::class,
                getOptionsLibmemcached(),
                'memcached',
                false,
            ],
            [
                Memory::class,
                [],
                '',
                false,
            ],
            //            [
            //                Redis::class,
            //                getOptionsRedis(),
            //                'redis',
            //                1,
            //            ],
            [
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
