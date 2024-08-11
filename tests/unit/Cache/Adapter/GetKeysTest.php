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

namespace Phalcon\Tests\Unit\Cache\Adapter;

use Phalcon\Cache\Adapter\RedisCluster;
use Phalcon\Cache\Adapter\AdapterInterface;
use Phalcon\Cache\Adapter\Apcu;
use Phalcon\Cache\Adapter\Libmemcached;
use Phalcon\Cache\Adapter\Memory;
use Phalcon\Cache\Adapter\Redis;
use Phalcon\Cache\Adapter\Stream;
use Phalcon\Cache\Adapter\Weak;
use Phalcon\Cache\Exception\Exception as StorageException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception;
use Phalcon\Support\Exception as HelperException;
use Phalcon\Tests\AbstractUnitTestCase;
use stdClass;

use function getOptionsLibmemcached;
use function getOptionsRedis;
use function outputDir;
use function phpversion;
use function uniqid;
use function version_compare;

final class GetKeysTest extends AbstractUnitTestCase
{
    /**
     *
     */
    public static function getAdapters(): array
    {
        return [
            [
                'apcu',
                Apcu::class,
                [],
                'ph-apcu-'
            ],
            [
                '',
                Memory::class,
                [],
                'ph-memo-'
            ],
            [
                'redis',
                Redis::class,
                getOptionsRedis(),
                'ph-reds-'
            ],
            [
                'redis',
                RedisCluster::class,
                getOptionsRedisCluster(),
                'ph-redc-'
            ],
            [
                '',
                Stream::class,
                [
                    'storageDir' => outputDir(),
                ],
                'ph-strm'
            ],
//            [
//                '',
//                Weak::class,
//                [],
//                'ph-wea-'
//            ],
        ];
    }

    /**
     * Tests Phalcon\Cache\Adapter\Redis :: getKeys()
     *
     * @dataProvider getAdapters
     *
     * @return void
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheAdapterGetKeys(
        string $extension,
        string $adapterClass,
        array $options,
        string $prefix
    ): void {
        if (!empty($extension)) {
            $this->checkExtensionIsLoaded($extension);
        }

        $serializer = new SerializerFactory();
        $adapter    = new $adapterClass($serializer, $options);

        $this->assertTrue($adapter->clear());

        $this->runTests($adapter, $prefix);

        if ('ph-strm' === $prefix) {
            $this->safeDeleteDirectory(outputDir('ph-strm'));
        }
    }

    /**
     * Tests Phalcon\Cache\Adapter\Libmemcached :: getKeys()
     *
     * @return void
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheAdapterLibmemcachedGetKeys(): void
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
     * Tests Phalcon\Cache\Adapter\Stream :: getKeys()
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
    public function testCacheAdapterStreamGetKeysIssue14190(): void
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
     * Tests Phalcon\Cache\Adapter\Stream :: getKeys()
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
    public function testCacheAdapterStreamGetKeysPrefix(): void
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
     * Tests Phalcon\Cache\Adapter\Weak :: getKeys()
     *
     * @return void
     *
     * @throws HelperException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheAdapterWeakGetKeys(): void
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
