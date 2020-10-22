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

class DeleteCest
{
    use RedisTrait;

    /**
     * Tests Phalcon\Cache\Adapter\Redis :: delete()
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws CacheException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterRedisDelete(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Redis - delete()');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Redis($helper, $serializer, getOptionsRedis());

        $key = 'cache-data';
        $adapter->set($key, 'test');
        $actual = $adapter->has($key);
        $I->assertTrue($actual);

        $actual = $adapter->delete($key);
        $I->assertTrue($actual);

        $actual = $adapter->has($key);
        $I->assertFalse($actual);
    }

    /**
     * Tests Phalcon\Cache\Adapter\Redis :: delete() - twice
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws CacheException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterRedisDeleteTwice(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Redis - delete() - twice');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Redis($helper, $serializer, getOptionsRedis());

        $key = 'cache-data';
        $adapter->set($key, 'test');
        $actual = $adapter->has($key);
        $I->assertTrue($actual);

        $actual = $adapter->delete($key);
        $I->assertTrue($actual);

        $actual = $adapter->has($key);
        $I->assertFalse($actual);

        $actual = $adapter->delete($key);
        $I->assertFalse($actual);
    }

    /**
     * Tests Phalcon\Cache\Adapter\Redis :: delete() - unknown
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws CacheException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterRedisDeleteUnknown(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Redis - delete() - unknown');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Redis($helper, $serializer, getOptionsRedis());

        $key    = 'cache-data';
        $actual = $adapter->delete($key);
        $I->assertFalse($actual);
    }
}
