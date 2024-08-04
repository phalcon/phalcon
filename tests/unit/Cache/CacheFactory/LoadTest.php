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

namespace Phalcon\Tests\Unit\Cache\CacheFactory;

use Phalcon\Config\Config;
use Phalcon\Tests\UnitTestCase;
use Phalcon\Cache\AdapterFactory;
use Phalcon\Cache\Cache;
use Phalcon\Cache\CacheFactory;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Tests\Fixtures\Traits\FactoryTrait;
use Phalcon\Cache\CacheInterface;

final class LoadTest extends UnitTestCase
{
    use FactoryTrait;

    public function setUp(): void
    {
        $this->init();
    }

    /**
     * Tests Phalcon\Cache\CacheFactory :: load()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-05-18
     */
    public function testCacheCacheFactoryLoad(): void
    {
        $options = $this->config->cache;
        $this->runTests($options);
    }

    /**
     * Tests Phalcon\Cache\CacheFactory :: load()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-05-18
     */
    public function testCacheCacheFactoryLoadArray(): void
    {
        $options = $this->arrayConfig['cache'];
        $this->runTests($options);
    }

    private function runTests(Config | array $options)
    {
        $cacheFactory = new CacheFactory(
            new AdapterFactory(
                new SerializerFactory()
            )
        );

        $adapter = $cacheFactory->load($options);

        $this->assertInstanceOf(Cache::class, $adapter);
        $this->assertInstanceOf(CacheInterface::class, $adapter);
    }
}
