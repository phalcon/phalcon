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

namespace Phalcon\Tests\Integration\Cache\Adapter\Redis;

use Phalcon\Support\Exception as HelperException;
use Phalcon\Cache\Adapter\Redis;
use Phalcon\Storage\Exception as CacheException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\HelperFactory;
use Phalcon\Tests\Fixtures\Traits\RedisTrait;
use IntegrationTester;

use function getOptionsRedis;

class IncrementCest
{
    use RedisTrait;

    /**
     * Tests Phalcon\Cache\Adapter\Redis :: increment()
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws CacheException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterRedisIncrement(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Redis - increment()');

        $I->skipTest('Check this');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Redis($helper, $serializer, getOptionsRedis());

        $key    = 'cache-data';
        $actual = $adapter->set($key, 1);
        $I->assertTrue($actual);

        $expected = 2;
        $actual   = $adapter->increment($key);
        $I->assertEquals($expected, $actual);

        $actual = $adapter->get($key);
        $I->assertEquals($expected, $actual);

        $expected = 10;
        $actual   = $adapter->increment($key, 8);
        $I->assertEquals($expected, $actual);

        $actual = $adapter->get($key);
        $I->assertEquals($expected, $actual);

        /**
         * unknown key
         */
        $key    = 'unknown';
        $actual = $adapter->increment($key);
        $I->assertFalse($actual);
    }
}
