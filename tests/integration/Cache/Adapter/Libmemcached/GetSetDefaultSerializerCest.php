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

namespace Phalcon\Tests\Integration\Cache\Adapter\Libmemcached;

use IntegrationTester;
use Phalcon\Cache\Adapter\Libmemcached;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Tests\Fixtures\Traits\LibmemcachedTrait;

use function getOptionsLibmemcached;

class GetSetDefaultSerializerCest
{
    use LibmemcachedTrait;

    /**
     * Tests Phalcon\Cache\Adapter\Libmemcached ::
     * getDefaultSerializer()/setDefaultSerializer()
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterLibmemcachedGetKeys(IntegrationTester $I)
    {
        $I->wantToTest(
            'Cache\Adapter\Libmemcached - getDefaultSerializer()/setDefaultSerializer()'
        );

        $serializer = new SerializerFactory();
        $adapter    = new Libmemcached(
            $serializer,
            getOptionsLibmemcached()
        );

        $expected = 'php';
        $actual   = $adapter->getDefaultSerializer();
        $I->assertEquals($expected, $actual);

        $adapter->setDefaultSerializer('Base64');
        $expected = 'base64';
        $actual   = $adapter->getDefaultSerializer();
        $I->assertEquals($expected, $actual);
    }
}
