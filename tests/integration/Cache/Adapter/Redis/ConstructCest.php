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

use Phalcon\Cache\Adapter\AdapterInterface;
use Phalcon\Cache\Adapter\Redis;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\HelperFactory;
use Phalcon\Tests\Fixtures\Traits\RedisTrait;
use IntegrationTester;

use function getOptionsRedis;

class ConstructCest
{
    use RedisTrait;

    /**
     * Tests Phalcon\Cache\Adapter\Redis :: __construct()
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterRedisConstruct(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Redis - __construct()');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Redis($helper, $serializer, getOptionsRedis());

        $expected = Redis::class;
        $I->assertInstanceOf($expected, $adapter);

        $expected = AdapterInterface::class;
        $I->assertInstanceOf($expected, $adapter);
    }
}
