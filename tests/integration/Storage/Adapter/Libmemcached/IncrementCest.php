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

use Phalcon\Support\Exception as HelperException;
use Phalcon\Storage\Adapter\Libmemcached;
use Phalcon\Storage\Exception as StorageException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\HelperFactory;
use Phalcon\Tests\Fixtures\Traits\LibmemcachedTrait;
use UnitTester;

use function getOptionsLibmemcached;

class IncrementCest
{
    use LibmemcachedTrait;

    /**
     * Tests Phalcon\Storage\Adapter\Libmemcached :: increment()
     *
     * @param UnitTester $I
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterLibmemcachedIncrement(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Libmemcached - increment()');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Libmemcached(
            $helper,
            $serializer,
            getOptionsLibmemcached()
        );

        $key    = 'cache-data';
        $result = $adapter->set($key, 1);
        $I->assertTrue($result);

        $expected = 2;
        $actual   = $adapter->increment($key);
        $I->assertEquals($expected, $actual);

        $actual = $adapter->get($key);
        $I->assertEquals($expected, $actual);

        $expected = 10;
        $actual   = $adapter->increment($key, 8);
        $I->assertEquals($expected, $actual);

        $actual = $adapter->get($key);
        $I->assertEquals($expected, $actual);

        /**
         * unknown key
         */
        $key    = 'unknown';
        $result = $adapter->increment($key);
        $I->assertFalse($result);
    }
}
