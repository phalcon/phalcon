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

use Phalcon\Cache\AdapterFactory;
use Phalcon\Cache\Cache;
use Phalcon\Cache\CacheFactory;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Tests\AbstractUnitTestCase;
use Psr\SimpleCache\CacheInterface;

use function uniqid;

final class NewInstanceTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Cache\CacheFactory :: newInstance()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheCacheFactoryNewInstance(): void
    {
        $serializer     = new SerializerFactory();
        $adapterFactory = new AdapterFactory($serializer);
        $cacheFactory   = new CacheFactory($adapterFactory);
        $adapter        = $cacheFactory->newInstance('apcu');

        $this->assertInstanceOf(Cache::class, $adapter);
        $this->assertInstanceOf(CacheInterface::class, $adapter);
    }
}
