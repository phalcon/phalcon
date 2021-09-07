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
use Memcached;
use Phalcon\Cache\Adapter\Libmemcached;
use Phalcon\Storage\Exception as CacheException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as HelperException;
use Phalcon\Support\HelperFactory;
use Phalcon\Tests\Fixtures\Traits\LibmemcachedTrait;

use function getOptionsLibmemcached;

class GetAdapterCest
{
    use LibmemcachedTrait;

    /**
     * Tests Phalcon\Cache\Adapter\Libmemcached :: getAdapter()
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws CacheException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterLibmemcachedGetAdapter(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Libmemcached - getAdapter()');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Libmemcached(
            $helper,
            $serializer,
            getOptionsLibmemcached()
        );

        $expected = Memcached::class;
        $actual   = $adapter->getAdapter();
        $I->assertInstanceOf($expected, $actual);
    }
}
