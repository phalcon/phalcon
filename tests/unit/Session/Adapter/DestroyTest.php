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

use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\AbstractServicesTestCase;

use function cacheDir;
use function file_put_contents;
use function serialize;
use function uniqid;

final class DestroyTest extends AbstractServicesTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Adapter\Libmemcached :: destroy()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionAdapterLibmemcachedDestroy(): void
    {
        $adapter = $this->newService('sessionLibmemcached');

        $value  = uniqid();
        $key    = 'sess-memc-test1';
        $actual = serialize($value);

        $this->setMemcachedKey($key, $actual, 0);

        $actual = serialize($value);
        $this->hasMemcachedKey($key);

        $actual = $adapter->destroy('test1');
        $this->assertTrue($actual);

        $this->doesNotHaveMemcachedKey($key);
    }

    /**
     * Tests Phalcon\Session\Adapter\Noop :: destroy()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionAdapterNoopDestroy(): void
    {
        $adapter = $this->newService('sessionNoop');

        $actual = $adapter->destroy('test1');
        $this->assertTrue($actual);
    }

    /**
     * Tests Phalcon\Session\Adapter\Redis :: destroy()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionAdapterRedisDestroy(): void
    {
        $adapter = $this->newService('sessionRedis');

        $value = uniqid();

        $this->setRedisKey(
            'string',
            'sess-reds-test1',
            serialize($value)
        );

        $actual = $adapter->destroy('test1');
        $this->assertTrue($actual);

        $this->doesNotHaveRedisKey('sess-reds-test1');
    }

    /**
     * Tests Phalcon\Session\Adapter\Stream :: destroy()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testSessionAdapterStreamDestroy(): void
    {
        $adapter = $this->newService('sessionStream');

        /**
         * Create a file in the session folder
         */
        file_put_contents(
            cacheDir('sessions/test1'),
            uniqid()
        );

        $actual = $adapter->destroy('test1');
        $this->assertTrue($actual);

        $this->assertFileDoesNotExist(cacheDir('sessions/test1'));
    }
}
