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

use Phalcon\Support\Exception as HelperExceptions;
use Phalcon\Storage\Adapter\Libmemcached;
use Phalcon\Storage\Exception as StorageException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\HelperFactory;
use Phalcon\Tests\Fixtures\Traits\LibmemcachedTrait;
use UnitTester;

use function getOptionsLibmemcached;

class HasCest
{
    use LibmemcachedTrait;

    /**
     * Tests Phalcon\Storage\Adapter\Libmemcached :: get()
     *
     * @param UnitTester $I
     *
     * @throws HelperExceptions
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterLibmemcachedGetSetHas(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Libmemcached - has()');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Libmemcached(
            $helper,
            $serializer,
            getOptionsLibmemcached()
        );

        $key = uniqid();

        $actual = $adapter->has($key);
        $I->assertFalse($actual);

        $adapter->set($key, 'test');

        $actual = $adapter->has($key);
        $I->assertTrue($actual);
    }
}
