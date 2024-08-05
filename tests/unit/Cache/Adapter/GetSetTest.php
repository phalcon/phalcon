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
use Memcached as NativeMemcached;
use Phalcon\Cache\Adapter\Apcu;
use Phalcon\Cache\Adapter\Libmemcached;
use Phalcon\Cache\Adapter\Memory;
use Phalcon\Cache\Adapter\Redis;
use Phalcon\Cache\Adapter\Stream;
use Phalcon\Cache\Adapter\Weak;
use Phalcon\Storage\SerializerFactory;
use Redis as NativeRedis;
use stdClass;

use function getOptionsLibmemcached;
use function getOptionsRedis;
use function outputDir;
use function sprintf;
use function uniqid;

final class GetSetTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Cache\Adapter\* :: get()/set()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testCacheAdapterGetSetWithZeroTtl(
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
     * Tests Phalcon\Cache\Adapter\Weak :: get()/set()
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2023-07-17
     */
    public function testCacheAdapterWeakGetSet(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Weak($serializer);

        $key    = uniqid();
        $obj    = new stdClass();
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
}
