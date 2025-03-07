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

namespace Phalcon\Tests\Unit\Session\Adapter;

use Phalcon\Session\Exception;
use Phalcon\Tests\AbstractServicesTestCase;
use Phalcon\Tests\Fixtures\Session\Adapter\StreamGlobFixture;
use Phalcon\Tests\Fixtures\Traits\DiTrait;

use function cacheDir;
use function file_put_contents;
use function getOptionsSessionStream;
use function sleep;
use function uniqid;

final class GcTest extends AbstractServicesTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Adapter\Libmemcached :: gc()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionAdapterLibmemcachedGc(): void
    {
        $adapter = $this->newService('sessionLibmemcached');

        /**
         * Add two session keys
         */
        $this->setMemcachedKey('sess-memc-gc_1', uniqid(), 1);
        $this->setMemcachedKey('sess-memc-gc_2', uniqid(), 1);
        /**
         * Sleep to make sure that the time expired
         */
        sleep(2);
        $actual = $adapter->gc(1);
        $this->assertNotFalse($actual);
        $this->doesNotHaveMemcachedKey('sess-memc-gc_1');
        $this->doesNotHaveRedisKey('sess-memc-gc_2');
    }

    /**
     * Tests Phalcon\Session\Adapter\Noop :: gc()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionAdapterNoopGc(): void
    {
        $adapter = $this->newService('sessionNoop');

        $actual = $adapter->gc(1);
        $this->assertNotFalse($actual);
    }

    /**
     * Tests Phalcon\Session\Adapter\Redis :: gc()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionAdapterRedisGc(): void
    {
        $adapter = $this->newService('sessionRedis');

        $actual = $adapter->gc(1);
        $this->assertNotFalse($actual);
    }

    /**
     * Tests Phalcon\Session\Adapter\Stream :: gc()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionAdapterStreamGc(): void
    {
        $adapter = $this->newService('sessionStream');

        /**
         * Add two session files
         */
        $actual = file_put_contents(cacheDir('sessions/gc_1'), uniqid());
        $this->assertNotFalse($actual);

        $actual = file_put_contents(cacheDir('sessions/gc_2'), uniqid());
        $this->assertNotFalse($actual);

        /**
         * Sleep to make sure that the time expired
         */
        sleep(2);

        $actual = $adapter->gc(1);
        $this->assertNotFalse($actual);

        $this->assertFileDoesNotExist(cacheDir('sessions/gc_1'));
        $this->assertFileDoesNotExist(cacheDir('sessions/gc_2'));
    }

    /**
     * Tests Phalcon\Session\Adapter\Stream :: gc() -
     * glob() false returns exception
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionAdapterStreamGcGlobThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unexpected gc error');

        $adapter = new StreamGlobFixture(getOptionsSessionStream());

        $actual = $adapter->gc(1);
    }
}
