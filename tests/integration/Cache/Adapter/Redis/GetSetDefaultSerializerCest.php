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

use Phalcon\Cache\Adapter\Redis;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\HelperFactory;
use IntegrationTester;

use function getOptionsRedis;

class GetSetDefaultSerializerCest
{
    /**
     * Tests Phalcon\Cache\Adapter\Redis ::
     * getDefaultSerializer()/setDefaultSerializer()
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterRedisGetKeys(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Redis - getDefaultSerializer()/setDefaultSerializer()');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Redis($helper, $serializer, getOptionsRedis());

        $expected = 'php';
        $actual   = $adapter->getDefaultSerializer();
        $I->assertEquals($expected, $actual);

        $adapter->setDefaultSerializer('Base64');
        $expected = 'base64';
        $actual   = $adapter->getDefaultSerializer();
        $I->assertEquals($expected, $actual);
    }
}
