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

namespace Phalcon\Tests\Integration\Storage\Adapter\Redis;

use Phalcon\Storage\Adapter\AdapterInterface;
use Phalcon\Storage\Adapter\Redis;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\HelperFactory;
use Phalcon\Tests\Fixtures\Traits\RedisTrait;
use UnitTester;

use function getOptionsRedis;

class ConstructCest
{
    use RedisTrait;

    /**
     * Tests Phalcon\Storage\Adapter\Redis :: __construct()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterRedisConstruct(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Redis - __construct()');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Redis($helper, $serializer, getOptionsRedis());

        $expected = Redis::class;
        $I->assertInstanceOf($expected, $adapter);

        $expected = AdapterInterface::class;
        $I->assertInstanceOf($expected, $adapter);
    }
}
