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

class HasCest
{
    /**
     * Tests Phalcon\Storage\Adapter\Stream :: has()
     *
     * @param UnitTester $I
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterStreamHas(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Stream - has()');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $helper,
            $serializer,
            [
                'storageDir' => outputDir(),
            ]
        );

        $key    = uniqid();
        $actual = $adapter->has($key);
        $I->assertFalse($actual);

        $adapter->set($key, 'test');
        $actual = $adapter->has($key);
        $I->assertTrue($actual);
    }

    /**
     * Tests Phalcon\Storage\Adapter\Stream :: has() - cannot open file
     *
     * @param UnitTester $I
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterStreamHasCannotOpenFile(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Stream - has() - cannot open file');

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
                'phpFopen' => false,
            ]
        );

        $key    = uniqid();
        $actual = $adapter->set($key, 'test');
        $I->assertTrue($actual);

        $actual = $adapter->has($key);
        $I->assertFalse($actual);
    }

    /**
     * Tests Phalcon\Storage\Adapter\Stream :: has() - empty payload
     *
     * @param UnitTester $I
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterStreamHasEmptyPayload(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Stream - has() - empty payload');

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
                'phpFileGetContents' => false,
            ]
        );

        $key    = uniqid();
        $actual = $adapter->set($key, 'test');
        $I->assertTrue($actual);

        $actual = $adapter->has($key);
        $I->assertFalse($actual);
    }
}
