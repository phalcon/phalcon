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

namespace Phalcon\Tests\Integration\Storage\Adapter\Libmemcached;

use Phalcon\Storage\Adapter\Libmemcached;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\HelperFactory;
use Phalcon\Tests\Fixtures\Traits\LibmemcachedTrait;
use UnitTester;

use function getOptionsLibmemcached;

class GetSetDefaultSerializerCest
{
    use LibmemcachedTrait;

    /**
     * Tests Phalcon\Storage\Adapter\Libmemcached ::
     * getDefaultSerializer()/setDefaultSerializer()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterLibmemcachedGetKeys(UnitTester $I)
    {
        $I->wantToTest(
            'Storage\Adapter\Libmemcached - getDefaultSerializer()/setDefaultSerializer()'
        );

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Libmemcached(
            $helper,
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
