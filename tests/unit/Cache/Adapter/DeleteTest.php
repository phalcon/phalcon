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
use Phalcon\Cache\Adapter\Weak;
use Phalcon\Storage\SerializerFactory;

use stdClass;

use function getOptionsLibmemcached;
use function getOptionsRedis;
use function outputDir;
use function sprintf;
use function uniqid;

final class DeleteTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Cache\Adapter\* :: delete()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testCacheAdapterDelete(
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
        $adapter->set($key, 'test');
        $actual = $adapter->has($key);
        $this->assertTrue($actual);

        $actual = $adapter->delete($key);
        $this->assertTrue($actual);

        $actual = $adapter->has($key);
        $this->assertFalse($actual);

        /**
         * Call clear twice to ensure it returns false
         */
        $actual = $adapter->delete($key);
        $this->assertFalse($actual);

        /**
         * Delete unknown
         */
        $key    = uniqid();
        $actual = $adapter->delete($key);
        $this->assertFalse($actual);
    }

    /**
     * Tests Phalcon\Cache\Adapter\Weak :: delete()
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2023-07-17
     */
    public function testCacheAdapterWeakDelete(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Weak($serializer);

        $obj1     = new stdClass();
        $obj1->id = 1;
        $obj2     = new stdClass();
        $obj2->id = 2;


        $key1 = uniqid();
        $key2 = uniqid();
        $adapter->set($key1, $obj1);
        $adapter->set($key2, $obj2);

        $actual = $adapter->has($key1);
        $this->assertTrue($actual);
        $actual = $adapter->has($key2);
        $this->assertTrue($actual);

        unset($obj1);
        gc_collect_cycles();
        $this->assertEquals(null, $adapter->get($key1));

        $temp = $adapter->get($key2);
        unset($obj2);
        gc_collect_cycles();
        $this->assertEquals($temp, $adapter->get($key2));

        unset($temp);
        $actual = $adapter->delete($key2);
        $this->assertTrue($actual);
        $actual = $adapter->delete($key2);
        $this->assertFalse($actual);

        $key    = uniqid();
        $actual = $adapter->delete($key);
        $this->assertFalse($actual);
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
