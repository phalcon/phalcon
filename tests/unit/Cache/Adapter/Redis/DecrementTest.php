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

use Phalcon\Cache\Adapter\Redis;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as HelperException;
use Phalcon\Tests\Fixtures\Traits\RedisTrait;
use Phalcon\Tests\UnitTestCase;

use function getOptionsRedis;
use function uniqid;

final class DecrementTest extends UnitTestCase
{
    use RedisTrait;

    /**
     * Tests Phalcon\Cache\Adapter\Redis :: decrement()
     *
     * @return void
     *
     * @throws HelperException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheAdapterRedisDecrement(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Redis($serializer, getOptionsRedis());

        $key      = uniqid();
        $expected = 100;
        $actual   = $adapter->increment($key, 100);
        $this->assertEquals($expected, $actual);

        $expected = 99;
        $actual   = $adapter->decrement($key);
        $this->assertEquals($expected, $actual);

        $actual = $adapter->get($key);
        $this->assertEquals($expected, $actual);

        $expected = 90;
        $actual   = $adapter->decrement($key, 9);
        $this->assertEquals($expected, $actual);

        $actual = $adapter->get($key);
        $this->assertEquals($expected, $actual);

        /**
         * unknown key
         */
        $key      = uniqid();
        $expected = -9;
        $actual   = $adapter->decrement($key, 9);
        $this->assertEquals($expected, $actual);
    }
}
