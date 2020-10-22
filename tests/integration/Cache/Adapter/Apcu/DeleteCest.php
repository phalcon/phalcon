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

namespace Phalcon\Tests\Integration\Cache\Adapter\Apcu;

use Phalcon\Support\Exception;
use Phalcon\Cache\Adapter\Apcu;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\HelperFactory;
use Phalcon\Tests\Fixtures\Traits\ApcuTrait;
use IntegrationTester;

class DeleteCest
{
    use ApcuTrait;

    /**
     * Tests Phalcon\Cache\Adapter\Apcu :: delete()
     *
     * @param IntegrationTester $I
     *
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterApcuDelete(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Apcu - delete()');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Apcu($helper, $serializer);

        $key = 'cache-data';
        $adapter->set($key, 'test');
        $actual = $adapter->has($key);
        $I->assertTrue($actual);

        $actual = $adapter->delete($key);
        $I->assertTrue($actual);

        $actual = $adapter->has($key);
        $I->assertFalse($actual);
    }

    /**
     * Tests Phalcon\Cache\Adapter\Apcu :: delete() - twice
     *
     * @param IntegrationTester $I
     *
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterApcuDeleteTwice(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Apcu - delete() - twice');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Apcu($helper, $serializer);

        $key = 'cache-data';
        $adapter->set($key, 'test');
        $actual = $adapter->has($key);
        $I->assertTrue($actual);

        $actual = $adapter->delete($key);
        $I->assertTrue($actual);

        $actual = $adapter->delete($key);
        $I->assertFalse($actual);
    }

    /**
     * Tests Phalcon\Cache\Adapter\Apcu :: delete() - unknown
     *
     * @param IntegrationTester $I
     *
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterApcuDeleteUnknown(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Apcu - delete() - unknown');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Apcu($helper, $serializer);

        $key    = 'cache-data';
        $actual = $adapter->delete($key);
        $I->assertFalse($actual);
    }
}
