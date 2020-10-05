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

namespace Phalcon\Tests\Integration\Storage\Adapter\Stream;

use Phalcon\Support\Exception as HelperException;
use Phalcon\Storage\Adapter\Stream;
use Phalcon\Storage\Exception as StorageException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\HelperFactory;
use UnitTester;

use function outputDir;
use function sort;
use function uniqid;

class GetKeysCest
{
    /**
     * Tests Phalcon\Storage\Adapter\Stream :: getKeys()
     *
     * @param UnitTester $I
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterStreamGetKeys(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Stream - getKeys()');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $helper,
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
        $I->assertEquals($expected, $actual);

        $expected = [
            'ph-strm' . $key3,
            'ph-strm' . $key4,
        ];
        $actual   = $adapter->getKeys("one");
        sort($actual);
        $I->assertEquals($expected, $actual);

        $I->safeDeleteDirectory(outputDir('ph-strm'));
    }

    /**
     * Tests Phalcon\Storage\Adapter\Stream :: getKeys()
     *
     * @param UnitTester $I
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author ekmst <https://github.com/ekmst>
     * @since  2020-09-09
     * @issue  cphalcon/#14190
     */
    public function storageAdapterStreamGetKeysIssue14190(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Stream - getKeys() - issue 14190');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $helper,
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

        $I->assertEquals($expected, $actual);

        foreach ($expected as $key) {
            $actual = $adapter->delete($key);
            $I->assertTrue($actual);
        }

        $I->safeDeleteDirectory(outputDir('basePrefix-'));
    }

    /**
     * Tests Phalcon\Storage\Adapter\Stream :: getKeys()
     *
     * @param UnitTester $I
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author ekmst <https://github.com/ekmst>
     * @since  2020-09-09
     * @issue  cphalcon/#14190
     */
    public function storageAdapterStreamGetKeysPrefix(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Stream - getKeys() - prefix');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $helper,
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
        $I->assertEquals($expected, $actual);

        $expected = [
            'pref-somekey',
            'pref-somekey1',
        ];

        $actual = $adapter->getKeys('so');
        sort($actual);
        $I->assertEquals($expected, $actual);

        $actual = $adapter->clear();
        $I->assertTrue($actual);

        $I->safeDeleteDirectory(outputDir('pref-'));
    }
}
