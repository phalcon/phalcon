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

use Phalcon\Helper\Exception as HelperException;
use Phalcon\Storage\Adapter\Redis;
use Phalcon\Storage\Exception as StorageException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Tests\Fixtures\Traits\RedisTrait;
use UnitTester;
use function getOptionsRedis;

class HasCest
{
    use RedisTrait;

    /**
     * Tests Phalcon\Storage\Adapter\Redis :: get()
     *
     * @param UnitTester $I
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterRedisGetSetHas(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Redis - has()');

        $serializer = new SerializerFactory();
        $adapter    = new Redis($serializer, getOptionsRedis());

        $key    = uniqid();
        $actual = $adapter->has($key);
        $I->assertFalse($actual);

        $adapter->set($key, 'test');
        $actual = $adapter->has($key);
        $I->assertTrue($actual);
    }
}
