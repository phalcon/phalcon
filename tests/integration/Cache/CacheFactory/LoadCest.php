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
use Phalcon\Cache\Exception\Exception as CacheException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\HelperFactory;
use Phalcon\Tests\Fixtures\Traits\FactoryTrait;
use Psr\SimpleCache\CacheInterface;

/**
 * Class LoadCest
 *
 * @package Phalcon\Tests\Integration\Cache\CacheFactory
 */
class LoadCest
{
    use FactoryTrait;

    /**
     * @param IntegrationTester $I
     */
    public function _before(IntegrationTester $I)
    {
        $this->init();
    }

    /**
     * Tests Phalcon\Cache\CacheFactory :: load()
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function cacheCacheFactoryLoad(IntegrationTester $I)
    {
        $I->wantToTest('Cache\CacheFactory - load()');

        $options = $this->config->cache;
        $factory = new CacheFactory(
            new AdapterFactory(
                new HelperFactory(),
                new SerializerFactory()
            )
        );

        $adapter = $factory->load($options);

        $I->assertInstanceOf(Cache::class, $adapter);
        $I->assertInstanceOf(CacheInterface::class, $adapter);

    }

    /**
     * Tests Phalcon\Cache\CacheFactory :: load()
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function cacheCacheFactoryLoadArray(IntegrationTester $I)
    {
        $I->wantToTest('Cache\CacheFactory - load() - array');

        $options = $this->arrayConfig['cache'];
        $factory = new CacheFactory(
            new AdapterFactory(
                new HelperFactory(),
                new SerializerFactory()
            )
        );

        $adapter = $factory->load($options);

        $I->assertInstanceOf(Cache::class, $adapter);
        $I->assertInstanceOf(CacheInterface::class, $adapter);
    }

    /**
     * Tests Phalcon\Cache\CacheFactory :: load() - exceptions
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function cacheCacheFactoryLoadExceptions(IntegrationTester $I)
    {
        $I->wantToTest('Cache\CacheFactory - load() - exceptions');


        $options = $this->arrayConfig['cache'];
        $factory = new CacheFactory(
            new AdapterFactory(
                new HelperFactory(),
                new SerializerFactory()
            )
        );

        $I->expectThrowable(
            new CacheException(
                'Config must be array or Phalcon\Config\Config object'
            ),
            function () use ($factory) {
                $factory->load(1234);
            }
        );

        $I->expectThrowable(
            new CacheException(
                'You must provide "adapter" option in factory config parameter.'
            ),
            function () use ($factory, $options) {
                $newOptions = $options;
                unset($newOptions['adapter']);

                $factory->load($newOptions);
            }
        );
    }
}
