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

use IntegrationTester;
use Phalcon\Cache\Adapter\Redis;
use Phalcon\Storage\Exception as CacheException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as HelperException;
use Phalcon\Support\HelperFactory;
use Phalcon\Tests\Fixtures\Traits\RedisTrait;

use function getOptionsRedis;

class GetAdapterCest
{
    use RedisTrait;

    /**
     * Tests Phalcon\Cache\Adapter\Redis :: getAdapter()
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws CacheException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterRedisGetAdapter(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Redis - getAdapter()');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Redis($helper, $serializer, getOptionsRedis());

        $class  = \Redis::class;
        $actual = $adapter->getAdapter();
        $I->assertInstanceOf($class, $actual);
    }
}
