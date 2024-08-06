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

namespace Phalcon\Tests\Unit\Storage\Adapter;

use Phalcon\Storage\Adapter\AdapterInterface;
use Phalcon\Storage\Adapter\Apcu;
use Phalcon\Storage\Adapter\Libmemcached;
use Phalcon\Storage\Adapter\Memory;
use Phalcon\Storage\Adapter\Redis;
use Phalcon\Storage\Adapter\Stream;
use Phalcon\Storage\Adapter\Weak;
use Phalcon\Storage\Exception as StorageException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception;
use Phalcon\Support\Exception as HelperException;
use Phalcon\Tests\UnitTestCase;
use stdClass;

use function getOptionsLibmemcached;
use function getOptionsRedis;
use function outputDir;
use function phpversion;
use function uniqid;
use function version_compare;

final class GetKeysTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Storage\Adapter\Apcu :: getKeys()
     *
     * @return void
     *
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testStorageAdapterApcuGetKeys(): void
    {
        $this->checkExtensionIsLoaded('apcu');

        $serializer = new SerializerFactory();
        $adapter    = new Apcu($serializer);

        $this->assertTrue($adapter->clear());

        $this->runTests($adapter, 'ph-apcu-');
    }

    /**
     * Tests Phalcon\Storage\Adapter\Libmemcached :: getKeys()
     *
     * @return void
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testStorageAdapterLibmemcachedGetKeys(): void
    {
        $this->checkExtensionIsLoaded('memcached');

        $serializer = new SerializerFactory();
        $adapter    = new Libmemcached(
            $serializer,
            getOptionsLibmemcached()
        );

        $memcachedServerVersions   = $adapter->getAdapter()
                                             ->getVersion()
        ;
        $memcachedExtensionVersion = phpversion('memcached');

        foreach ($memcachedServerVersions as $memcachedServerVersion) {
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

        $this->runTests($adapter, 'ph-memc-');
    }

    /**
     * Tests Phalcon\Storage\Adapter\Memory :: getKeys()
     *
     * @return void
     *
     * @throws HelperException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testStorageAdapterMemoryGetKeys(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Memory($serializer);

        $this->assertTrue($adapter->clear());

        $this->runTests($adapter, 'ph-memo-');
    }

    /**
     * Tests Phalcon\Storage\Adapter\Redis :: getKeys()
     *
     * @return void
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testStorageAdapterRedisGetKeys(): void
    {
        $this->checkExtensionIsLoaded('redis');

        $serializer = new SerializerFactory();
        $adapter    = new Redis($serializer, getOptionsRedis());

        $this->assertTrue($adapter->clear());

        $this->runTests($adapter, 'ph-reds-');
    }

    /**
     * Tests Phalcon\Storage\Adapter\Stream :: getKeys()
     *
     * @return void
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testStorageAdapterStreamGetKeys(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $serializer,
            [
                'storageDir' => outputDir(),
            ]
        );

        $this->assertTrue($adapter->clear());

        $this->runTests($adapter, 'ph-strm');

        $this->safeDeleteDirectory(outputDir('ph-strm'));
    }

    /**
     * Tests Phalcon\Storage\Adapter\Stream :: getKeys()
     *
     * @return void
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author ekmst <https://github.com/ekmst>
     * @since  2020-09-09
     * @issue  cphalcon/#14190
     */
    public function testStorageAdapterStreamGetKeysIssue14190(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $serializer,
            [
                'storageDir' => outputDir(),
                'prefix'     => 'basePrefix-',
            ]
        );

        $adapter->clear();

        $actual = $adapter->set('key', 'test');
        $this->assertNotFalse($actual);
        $actual = $adapter->set('key1', 'test');
        $this->assertNotFalse($actual);

        $expected = [
            'basePrefix-key',
            'basePrefix-key1',
        ];

        $actual = $adapter->getKeys();
        sort($actual);

        $this->assertSame($expected, $actual);

        foreach ($expected as $key) {
            $actual = $adapter->delete($key);
            $this->assertTrue($actual);
        }

        $this->safeDeleteDirectory(outputDir('basePrefix-'));
    }

    /**
     * Tests Phalcon\Storage\Adapter\Stream :: getKeys()
     *
     * @return void
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author ekmst <https://github.com/ekmst>
     * @since  2020-09-09
     * @issue  cphalcon/#14190
     */
    public function testStorageAdapterStreamGetKeysPrefix(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $serializer,
            [
                'storageDir' => outputDir(),
                'prefix'     => 'pref-',
            ]
        );

        $actual = $adapter->clear();
        $this->assertTrue($actual);
        $actual = $adapter->getKeys();
        $this->assertEmpty($actual);

        $actual = $adapter->set('key', 'test');
        $this->assertNotFalse($actual);
        $actual = $adapter->set('key1', 'test');
        $this->assertNotFalse($actual);
        $actual = $adapter->set('somekey', 'test');
        $this->assertNotFalse($actual);
        $actual = $adapter->set('somekey1', 'test');
        $this->assertNotFalse($actual);

        $expected = [
            'pref-key',
            'pref-key1',
            'pref-somekey',
            'pref-somekey1',
        ];
        $actual   = $adapter->getKeys();
        sort($actual);
        $this->assertSame($expected, $actual);

        $expected = [
            'pref-somekey',
            'pref-somekey1',
        ];

        $actual = $adapter->getKeys('so');
        sort($actual);
        $this->assertSame($expected, $actual);

        $actual = $adapter->clear();
        $this->assertTrue($actual);

        $this->safeDeleteDirectory(outputDir('pref-'));
    }

    /**
     * Tests Phalcon\Storage\Adapter\Weak :: getKeys()
     *
     * @return void
     *
     * @throws HelperException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testStorageAdapterWeakGetKeys(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Weak($serializer);

        $this->assertTrue($adapter->clear());

        $obj1 = new stdClass();
        $obj2 = new stdClass();
        $obj3 = new stdClass();


        $adapter->set('key-1', $obj1);
        $adapter->set('key-2', $obj2);
        $adapter->set('key-3', $obj3);
        $adapter->set('one-1', $obj1);
        $adapter->set('one-2', $obj2);
        $adapter->set('one-3', $obj3);

        $expected = [
            'key-1',
            'key-2',
            'key-3',
            'one-1',
            'one-2',
            'one-3',
        ];
        $actual   = $adapter->getKeys();
        sort($actual);
        $this->assertSame($expected, $actual);

        $expected = [
            'one-1',
            'one-2',
            'one-3',
        ];
        $actual   = $adapter->getKeys("one");
        sort($actual);
        $this->assertSame($expected, $actual);
    }

    private function runTests(
        AdapterInterface $adapter,
        string $prefix
    ): void {
        [$key1, $key2, $key3, $key4] = $this->setupTest($adapter);

        $expected = [
            $prefix . $key1,
            $prefix . $key2,
            $prefix . $key3,
            $prefix . $key4,
        ];
        $actual   = $adapter->getKeys();
        sort($actual);
        $this->assertSame($expected, $actual);

        $expected = [
            $prefix . $key3,
            $prefix . $key4,
        ];
        $actual   = $adapter->getKeys("one");
        sort($actual);
        $this->assertSame($expected, $actual);
    }

    private function setupTest(AdapterInterface $adapter): array
    {
        $key1 = uniqid('key');
        $key2 = uniqid('key');
        $key3 = uniqid('one');
        $key4 = uniqid('one');

        $result = $adapter->set($key1, 'test');
        $this->assertNotFalse($result);
        $result = $adapter->set($key2, 'test');
        $this->assertNotFalse($result);
        $result = $adapter->set($key3, 'test');
        $this->assertNotFalse($result);
        $result = $adapter->set($key4, 'test');
        $this->assertNotFalse($result);

        $actual = $adapter->has($key1);
        $this->assertTrue($actual);
        $actual = $adapter->has($key2);
        $this->assertTrue($actual);
        $actual = $adapter->has($key3);
        $this->assertTrue($actual);
        $actual = $adapter->has($key4);
        $this->assertTrue($actual);

        return [$key1, $key2, $key3, $key4];
    }
}
