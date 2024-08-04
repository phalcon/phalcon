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

namespace Phalcon\Tests\Unit\Cache\Adapter\Redis;

use Phalcon\Tests\UnitTestCase;
use Phalcon\Cache\Adapter\Redis;
use Phalcon\Cache\Exception as CacheException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as HelperException;
use Phalcon\Tests\Fixtures\Traits\RedisTrait;

use function getOptionsRedis;
use function uniqid;

final class IncrementTest extends UnitTestCase
{
    use RedisTrait;

    /**
     * Tests Phalcon\Cache\Adapter\Redis :: increment()
     *
     * @return void
     *
     * @throws HelperException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheAdapterRedisIncrement(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Redis($serializer, getOptionsRedis());

        $key      = uniqid();
        $expected = 1;
        $actual   = $adapter->increment($key, 1);
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
        $this->assertEquals($expected, $actual);
    }
}
