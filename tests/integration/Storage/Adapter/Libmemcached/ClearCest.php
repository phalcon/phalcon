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

namespace Phalcon\Tests\Integration\Storage\Adapter\Libmemcached;

use Phalcon\Helper\Exception as HelperException;
use Phalcon\Storage\Adapter\Libmemcached;
use Phalcon\Storage\Exception as StorageException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Tests\Fixtures\Traits\LibmemcachedTrait;
use UnitTester;
use function getOptionsLibmemcached;

class ClearCest
{
    use LibmemcachedTrait;

    /**
     * Tests Phalcon\Storage\Adapter\Libmemcached :: clear()
     *
     * @param UnitTester $I
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterLibmemcachedClear(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Libmemcached - clear()');

        $serializer = new SerializerFactory();
        $adapter    = new Libmemcached($serializer, getOptionsLibmemcached());

        $key1 = uniqid();
        $key2 = uniqid();

        $adapter->set($key1, 'test');

        $actual = $adapter->has($key1);
        $I->assertTrue($actual);

        $adapter->set($key1, 'test');
        $actual = $adapter->has($key1);
        $I->assertTrue($actual);

        $actual = $adapter->clear();
        $I->assertTrue($actual);

        $actual = $adapter->has($key1);
        $I->assertFalse($actual);

        $actual = $adapter->has($key2);
        $I->assertFalse($actual);
    }

    /**
     * Tests Phalcon\Storage\Adapter\Libmemcached :: clear() - twice
     *
     * @param UnitTester $I
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterLibmemcachedClearTwice(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Libmemcached - clear() - twice');

        $serializer = new SerializerFactory();
        $adapter    = new Libmemcached($serializer, getOptionsLibmemcached());

        $key = 'key-1';
        $adapter->set($key, 'test');
        $actual = $adapter->has($key);
        $I->assertTrue($actual);

        $key = 'key-2';
        $adapter->set($key, 'test');
        $actual = $adapter->has($key);
        $I->assertTrue($actual);

        $actual = $adapter->clear();
        $I->assertTrue($actual);

        $actual = $adapter->clear();
        $I->assertTrue($actual);
    }
}
