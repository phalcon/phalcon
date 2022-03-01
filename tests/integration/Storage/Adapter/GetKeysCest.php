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

namespace Phalcon\Tests\Integration\Storage\Adapter;

use Codeception\Stub;
use IntegrationTester;
use Phalcon\Storage\Adapter\Apcu;
use Phalcon\Storage\Adapter\Libmemcached;
use Phalcon\Storage\Adapter\Memory;
use Phalcon\Storage\Adapter\Redis;
use Phalcon\Storage\Adapter\Stream;
use Phalcon\Storage\Exception as StorageException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception;
use Phalcon\Support\Exception as HelperException;

use function getOptionsLibmemcached;
use function getOptionsRedis;
use function outputDir;
use function phpversion;
use function uniqid;
use function version_compare;

class GetKeysCest
{
    /**
     * Tests Phalcon\Storage\Adapter\Apcu :: getKeys()
     *
     * @param IntegrationTester $I
     *
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterApcuGetKeys(IntegrationTester $I)
    {
        $I->wantToTest('Storage\Adapter\Apcu - getKeys()');

        $I->checkExtensionIsLoaded('apcu');

        $serializer = new SerializerFactory();
        $adapter    = new Apcu($serializer);

        $I->assertTrue($adapter->clear());

        $adapter->set('key-1', 'test');
        $adapter->set('key-2', 'test');
        $adapter->set('one-1', 'test');
        $adapter->set('one-2', 'test');

        $I->assertTrue($adapter->has('key-1'));
        $I->assertTrue($adapter->has('key-2'));
        $I->assertTrue($adapter->has('one-1'));
        $I->assertTrue($adapter->has('one-2'));

        $expected = [
            'ph-apcu-key-1',
            'ph-apcu-key-2',
            'ph-apcu-one-1',
            'ph-apcu-one-2',
        ];
        $actual   = $adapter->getKeys();
        sort($actual);
        $I->assertSame($expected, $actual);

        $expected = [
            'ph-apcu-one-1',
            'ph-apcu-one-2',
        ];
        $actual   = $adapter->getKeys("one");
        sort($actual);
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Storage\Adapter\Apcu :: getKeys() - iterator error
     *
     * @param IntegrationTester $I
     *
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterApcuGetKeysIteratorError(IntegrationTester $I)
    {
        $I->wantToTest('Storage\Adapter\Apcu - getKeys() - iterator error');

        $I->checkExtensionIsLoaded('apcu');

        $serializer = new SerializerFactory();
        $adapter    = Stub::construct(
            Apcu::class,
            [
                $serializer,
            ],
            [
                'phpApcuIterator' => false,
            ]
        );

        $adapter->set('key-1', 'test');
        $adapter->set('key-2', 'test');
        $adapter->set('one-1', 'test');
        $adapter->set('one-2', 'test');

        $I->assertTrue($adapter->has('key-1'));
        $I->assertTrue($adapter->has('key-2'));
        $I->assertTrue($adapter->has('one-1'));
        $I->assertTrue($adapter->has('one-2'));

        $actual = $adapter->getKeys();
        $I->assertIsArray($actual);
        $I->assertEmpty($actual);
    }

    /**
     * Tests Phalcon\Storage\Adapter\Libmemcached :: getKeys()
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterLibmemcachedGetKeys(IntegrationTester $I)
    {
        $I->wantToTest('Storage\Adapter\Libmemcached - getKeys()');

        $I->checkExtensionIsLoaded('memcached');

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
                $I->skipTest(
                    'getAllKeys() does not work in certain Memcached versions'
                );
            }

            // https://github.com/php-memcached-dev/php-memcached/issues/367
            if (version_compare($memcachedServerVersion, '1.5.0', '>=')) {
                $I->skipTest(
                    'getAllKeys() does not work in certain Memcached versions'
                );
            }
        }

        $I->assertTrue($adapter->clear());

        $adapter->set('key-1', 'test');
        $adapter->set('key-2', 'test');
        $adapter->set('one-1', 'test');
        $adapter->set('one-2', 'test');

        $actual = $adapter->has('key-1');
        $I->assertTrue($actual);
        $actual = $adapter->has('key-2');
        $I->assertTrue($actual);
        $actual = $adapter->has('one-1');
        $I->assertTrue($actual);
        $actual = $adapter->has('one-2');
        $I->assertTrue($actual);

        $expected = [
            'ph-memc-key-1',
            'ph-memc-key-2',
            'ph-memc-one-1',
            'ph-memc-one-2',
        ];
        $actual   = $adapter->getKeys();
        sort($actual);
        $I->assertSame($expected, $actual);

        $expected = [
            'ph-memc-one-1',
            'ph-memc-one-2',
        ];
        $actual   = $adapter->getKeys("one");
        sort($actual);
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Storage\Adapter\Memory :: getKeys()
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterMemoryGetKeys(IntegrationTester $I)
    {
        $I->wantToTest('Storage\Adapter\Memory - getKeys()');

        $serializer = new SerializerFactory();
        $adapter    = new Memory($serializer);

        $I->assertTrue($adapter->clear());

        $adapter->set('key-1', 'test');
        $adapter->set('key-2', 'test');
        $adapter->set('one-1', 'test');
        $adapter->set('one-2', 'test');

        $actual = $adapter->has('key-1');
        $I->assertTrue($actual);
        $actual = $adapter->has('key-2');
        $I->assertTrue($actual);
        $actual = $adapter->has('one-1');
        $I->assertTrue($actual);
        $actual = $adapter->has('one-2');
        $I->assertTrue($actual);

        $expected = [
            'ph-memo-key-1',
            'ph-memo-key-2',
            'ph-memo-one-1',
            'ph-memo-one-2',
        ];
        $actual   = $adapter->getKeys();
        sort($actual);
        $I->assertSame($expected, $actual);

        $expected = [
            'ph-memo-one-1',
            'ph-memo-one-2',
        ];
        $actual   = $adapter->getKeys("one");
        sort($actual);
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Storage\Adapter\Redis :: getKeys()
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterRedisGetKeys(IntegrationTester $I)
    {
        $I->wantToTest('Storage\Adapter\Redis - getKeys()');

        $I->checkExtensionIsLoaded('redis');

        $serializer = new SerializerFactory();
        $adapter    = new Redis($serializer, getOptionsRedis());

        $I->assertTrue($adapter->clear());

        $adapter->set('key-1', 'test');
        $adapter->set('key-2', 'test');
        $adapter->set('one-1', 'test');
        $adapter->set('one-2', 'test');

        $actual = $adapter->has('key-1');
        $I->assertTrue($actual);
        $actual = $adapter->has('key-2');
        $I->assertTrue($actual);
        $actual = $adapter->has('one-1');
        $I->assertTrue($actual);
        $actual = $adapter->has('one-2');
        $I->assertTrue($actual);

        $expected = [
            'ph-reds-key-1',
            'ph-reds-key-2',
            'ph-reds-one-1',
            'ph-reds-one-2',
        ];
        $actual   = $adapter->getKeys();
        sort($actual);
        $I->assertSame($expected, $actual);

        $expected = [
            'ph-reds-one-1',
            'ph-reds-one-2',
        ];
        $actual   = $adapter->getKeys("one");
        sort($actual);
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Storage\Adapter\Stream :: getKeys()
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterStreamGetKeys(IntegrationTester $I)
    {
        $I->wantToTest('Storage\Adapter\Stream - getKeys()');

        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $serializer,
            [
                'storageDir' => outputDir(),
            ]
        );

        $I->assertTrue($adapter->clear());

        $key1 = uniqid('key');
        $key2 = uniqid('key');
        $key3 = uniqid('one');
        $key4 = uniqid('one');

        $result = $adapter->set($key1, 'test');
        $I->assertNotFalse($result);
        $result = $adapter->set($key2, 'test');
        $I->assertNotFalse($result);
        $result = $adapter->set($key3, 'test');
        $I->assertNotFalse($result);
        $result = $adapter->set($key4, 'test');
        $I->assertNotFalse($result);

        $I->assertTrue($adapter->has($key1));
        $I->assertTrue($adapter->has($key2));
        $I->assertTrue($adapter->has($key3));
        $I->assertTrue($adapter->has($key4));

        $expected = [
            'ph-strm' . $key1,
            'ph-strm' . $key2,
            'ph-strm' . $key3,
            'ph-strm' . $key4,
        ];
        $actual   = $adapter->getKeys();
        sort($actual);
        $I->assertSame($expected, $actual);

        $expected = [
            'ph-strm' . $key3,
            'ph-strm' . $key4,
        ];
        $actual   = $adapter->getKeys("one");
        sort($actual);
        $I->assertSame($expected, $actual);

        $I->safeDeleteDirectory(outputDir('ph-strm'));
    }

    /**
     * Tests Phalcon\Storage\Adapter\Stream :: getKeys()
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author ekmst <https://github.com/ekmst>
     * @since  2020-09-09
     * @issue  cphalcon/#14190
     */
    public function storageAdapterStreamGetKeysIssue14190(IntegrationTester $I)
    {
        $I->wantToTest('Storage\Adapter\Stream - getKeys() - issue 14190');

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
        $I->assertNotFalse($actual);
        $actual = $adapter->set('key1', 'test');
        $I->assertNotFalse($actual);

        $expected = [
            'basePrefix-key',
            'basePrefix-key1',
        ];

        $actual = $adapter->getKeys();
        sort($actual);

        $I->assertSame($expected, $actual);

        foreach ($expected as $key) {
            $actual = $adapter->delete($key);
            $I->assertTrue($actual);
        }

        $I->safeDeleteDirectory(outputDir('basePrefix-'));
    }

    /**
     * Tests Phalcon\Storage\Adapter\Stream :: getKeys()
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author ekmst <https://github.com/ekmst>
     * @since  2020-09-09
     * @issue  cphalcon/#14190
     */
    public function storageAdapterStreamGetKeysPrefix(IntegrationTester $I)
    {
        $I->wantToTest('Storage\Adapter\Stream - getKeys() - prefix');

        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $serializer,
            [
                'storageDir' => outputDir(),
                'prefix'     => 'pref-',
            ]
        );

        $actual = $adapter->clear();
        $I->assertTrue($actual);
        $actual = $adapter->getKeys();
        $I->assertEmpty($actual);

        $actual = $adapter->set('key', 'test');
        $I->assertNotFalse($actual);
        $actual = $adapter->set('key1', 'test');
        $I->assertNotFalse($actual);
        $actual = $adapter->set('somekey', 'test');
        $I->assertNotFalse($actual);
        $actual = $adapter->set('somekey1', 'test');
        $I->assertNotFalse($actual);

        $expected = [
            'pref-key',
            'pref-key1',
            'pref-somekey',
            'pref-somekey1',
        ];
        $actual   = $adapter->getKeys();
        sort($actual);
        $I->assertSame($expected, $actual);

        $expected = [
            'pref-somekey',
            'pref-somekey1',
        ];

        $actual = $adapter->getKeys('so');
        sort($actual);
        $I->assertSame($expected, $actual);

        $actual = $adapter->clear();
        $I->assertTrue($actual);

        $I->safeDeleteDirectory(outputDir('pref-'));
    }
}
