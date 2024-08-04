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

namespace Phalcon\Tests\Unit\Cache\Adapter\Libmemcached;

use Phalcon\Tests\UnitTestCase;
use Phalcon\Cache\Adapter\Libmemcached;
use Phalcon\Cache\Exception as CacheException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as HelperException;
use Phalcon\Tests\Fixtures\Traits\LibmemcachedTrait;

use function getOptionsLibmemcached;

final class GetKeysTest extends UnitTestCase
{
    use LibmemcachedTrait;

    /**
     * Tests Phalcon\Cache\Adapter\Libmemcached :: getKeys()
     *
     * @return void
     *
     * @throws HelperException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheAdapterLibmemcachedGetKeys(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Libmemcached(
            $serializer,
            getOptionsLibmemcached()
        );

        $memcachedServerVersions   = $adapter->getAdapter()
                                             ->getVersion()
        ;
        $memcachedExtensionVersion = phpversion('memcached');

        foreach ($memcachedServerVersions as $server => $memcachedServerVersion) {
            // https://www.php.net/manual/en/memcached.getallkeys.php#123793
            // https://bugs.launchpad.net/libmemcached/+bug/1534062
            if (
                version_compare($memcachedServerVersion, '1.4.23', '>=') &&
                version_compare($memcachedExtensionVersion, '3.0.1', '<')
            ) {
                $this->markTestSkipped(
                    'getAllKeys() does not work in certain Memcached versions'
                );
            }

            // https://github.com/php-memcached-dev/php-memcached/issues/367
            if (version_compare($memcachedServerVersion, '1.5.0', '>=')) {
                $this->markTestSkipped(
                    'getAllKeys() does not work in certain Memcached versions'
                );
            }
        }

        $this->assertTrue($adapter->clear());

        $adapter->set('key-1', 'test');
        $adapter->set('key-2', 'test');
        $adapter->set('one-1', 'test');
        $adapter->set('one-2', 'test');

        $actual = $adapter->has('key-1');
        $this->assertTrue($actual);
        $actual = $adapter->has('key-2');
        $this->assertTrue($actual);
        $actual = $adapter->has('one-1');
        $this->assertTrue($actual);
        $actual = $adapter->has('one-2');
        $this->assertTrue($actual);

        $expected = [
            'ph-memc-key-1',
            'ph-memc-key-2',
            'ph-memc-one-1',
            'ph-memc-one-2',
        ];
        $actual   = $adapter->getKeys();
        sort($actual);
        $this->assertEquals($expected, $actual);

        $expected = [
            'ph-memc-one-1',
            'ph-memc-one-2',
        ];
        $actual   = $adapter->getKeys("one");
        sort($actual);
        $this->assertEquals($expected, $actual);
    }
}
