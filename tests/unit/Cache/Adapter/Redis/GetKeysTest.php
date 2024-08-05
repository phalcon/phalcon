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

final class GetKeysTest extends UnitTestCase
{
    use RedisTrait;

    /**
     * Tests Phalcon\Cache\Adapter\Redis :: getKeys()
     *
     * @return void
     *
     * @throws HelperException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheAdapterRedisGetKeys(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Redis($serializer, getOptionsRedis());

        $this->assertTrue($adapter->clear());

        $adapter->set('key-1', 'test');
        $adapter->set('key-2', 'test');
        $adapter->set('one-1', 'test');
        $adapter->set('one-2', 'test');

        $actual = $adapter->has('key-1');
        $this->assertTrue($actual);
        $actual = $adapter->has('key-2');
        $this->assertTrue($actual);
        $actual = $adapter->has('one-1');
        $this->assertTrue($actual);
        $actual = $adapter->has('one-2');
        $this->assertTrue($actual);

        $expected = [
            'ph-reds-key-1',
            'ph-reds-key-2',
            'ph-reds-one-1',
            'ph-reds-one-2',
        ];
        $actual   = $adapter->getKeys();
        sort($actual);
        $this->assertEquals($expected, $actual);

        $expected = [
            'ph-reds-one-1',
            'ph-reds-one-2',
        ];
        $actual   = $adapter->getKeys("one");
        sort($actual);
        $this->assertEquals($expected, $actual);
    }
}
