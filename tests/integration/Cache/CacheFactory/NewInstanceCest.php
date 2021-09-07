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

namespace Phalcon\Tests\Integration\Cache\CacheFactory;

use IntegrationTester;
use Phalcon\Cache\AdapterFactory;
use Phalcon\Cache\Cache;
use Phalcon\Cache\CacheFactory;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\HelperFactory;
use Psr\SimpleCache\CacheInterface;

class NewInstanceCest
{
    /**
     * Tests Phalcon\Cache\CacheFactory :: newInstance()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function cacheCacheFactoryNewInstance(IntegrationTester $I)
    {
        $I->wantToTest('Cache\CacheFactory - newInstance()');

        $helper         = new HelperFactory();
        $serializer     = new SerializerFactory();
        $adapterFactory = new AdapterFactory($helper, $serializer);
        $cacheFactory   = new CacheFactory($adapterFactory);
        $adapter        = $cacheFactory->newInstance('apcu');

        $I->assertInstanceOf(Cache::class, $adapter);
        $I->assertInstanceOf(CacheInterface::class, $adapter);
    }
}
