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

namespace Phalcon\Tests\Integration\Cache\Adapter\Memory;

use Phalcon\Support\Exception as HelperException;
use Phalcon\Cache\Adapter\Memory;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\HelperFactory;
use IntegrationTester;

class ClearCest
{
    /**
     * Tests Phalcon\Cache\Adapter\Memory :: clear()
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterMemoryClear(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Memory - clear()');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Memory($helper, $serializer);

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
    }

    /**
     * Tests Phalcon\Cache\Adapter\Memory :: clear() - twice
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterMemoryClearTwice(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Memory - clear() - twice');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Memory($helper, $serializer);

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
    }
}
