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

namespace Phalcon\Tests\Integration\Storage\Adapter\Apcu;

use Codeception\Stub;
use IntegrationTester;
use Phalcon\Storage\Adapter\Apcu;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception;
use Phalcon\Support\HelperFactory;
use Phalcon\Tests\Fixtures\Traits\ApcuTrait;

use function uniqid;

class ClearCest
{
    use ApcuTrait;

    /**
     * Tests Phalcon\Storage\Adapter\Apcu :: clear()
     *
     * @param IntegrationTester $I
     *
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterApcuClear(IntegrationTester $I)
    {
        $I->wantToTest('Storage\Adapter\Apcu - clear()');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Apcu($helper, $serializer);

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
     * Tests Phalcon\Storage\Adapter\Apcu :: clear() - twice
     *
     * @param IntegrationTester $I
     *
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterApcuClearTwice(IntegrationTester $I)
    {
        $I->wantToTest('Storage\Adapter\Apcu - clear() - twice');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Apcu($helper, $serializer);

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

    /**
     * Tests Phalcon\Storage\Adapter\Apcu :: clear() - iterator error
     *
     * @param IntegrationTester $I
     *
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterApcuClearIteratorError(IntegrationTester $I)
    {
        $I->wantToTest('Storage\Adapter\Apcu - clear() - iterator error');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = Stub::construct(
            Apcu::class,
            [
                $helper,
                $serializer
            ],
            [
                'phpApcuIterator' => false,
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
    }

    /**
     * Tests Phalcon\Storage\Adapter\Apcu :: clear() - delete error
     *
     * @param IntegrationTester $I
     *
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterApcuClearDeleteError(IntegrationTester $I)
    {
        $I->wantToTest('Storage\Adapter\Apcu - clear() - delete error');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = Stub::construct(
            Apcu::class,
            [
                $helper,
                $serializer
            ],
            [
                'phpApcuDelete' => false,
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
    }
}
