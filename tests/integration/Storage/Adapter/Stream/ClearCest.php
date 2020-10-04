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

use Codeception\Stub;
use Phalcon\Support\Exception as HelperException;
use Phalcon\Storage\Adapter\Stream;
use Phalcon\Storage\Exception as StorageException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\HelperFactory;
use UnitTester;

use function outputDir;
use function uniqid;

class ClearCest
{
    /**
     * Tests Phalcon\Storage\Adapter\Stream :: clear()
     *
     * @param UnitTester $I
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterStreamClear(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Stream - clear()');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $helper,
            $serializer,
            [
                'storageDir' => outputDir(),
            ]
        );

        $key1 = uniqid();
        $key2 = uniqid();
        $adapter->set($key1, 'test');
        $actual = $adapter->has($key1);
        $I->assertTrue($actual);

        $adapter->set($key2, 'test');

        $actual = $adapter->has($key2);
        $I->assertTrue($actual);
        $actual = $adapter->clear();
        $I->assertTrue($actual);
        $actual = $adapter->has($key1);
        $I->assertFalse($actual);
        $actual = $adapter->has($key2);
        $I->assertFalse($actual);

        $I->safeDeleteDirectory(outputDir('ph-strm'));
    }

    /**
     * Tests Phalcon\Storage\Adapter\Stream :: clear() - twice
     *
     * @param UnitTester $I
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterStreamClearTwice(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Stream - clear() - twice');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $helper,
            $serializer,
            [
                'storageDir' => outputDir(),
            ]
        );

        $key1 = uniqid();
        $key2 = uniqid();
        $adapter->set($key1, 'test');
        $actual = $adapter->has($key1);
        $I->assertTrue($actual);

        $adapter->set($key2, 'test');
        $actual = $adapter->has($key2);
        $I->assertTrue($actual);

        $actual = $adapter->clear();
        $I->assertTrue($actual);

        $actual = $adapter->clear();
        $I->assertTrue($actual);

        $I->safeDeleteDirectory(outputDir('ph-strm'));
    }

    /**
     * Tests Phalcon\Storage\Adapter\Stream :: clear() - cannot delete file
     *
     * @param UnitTester $I
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterStreamClearCannotDeleteFile(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Stream - clear() - cannot delete file');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = Stub::construct(
            Stream::class,
            [
                $helper,
                $serializer,
                [
                    'storageDir' => outputDir(),
                ],
            ],
            [
                'phpUnlink' => false,
            ]
        );

        $key1 = uniqid();
        $key2 = uniqid();
        $adapter->set($key1, 'test');
        $actual = $adapter->has($key1);
        $I->assertTrue($actual);

        $adapter->set($key2, 'test');
        $actual = $adapter->has($key2);
        $I->assertTrue($actual);

        $actual = $adapter->clear();
        $I->assertFalse($actual);

        $I->safeDeleteDirectory(outputDir('ph-strm'));
    }
}
