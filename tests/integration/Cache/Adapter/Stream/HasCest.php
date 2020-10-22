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

namespace Phalcon\Tests\Integration\Cache\Adapter\Stream;

use Codeception\Stub;
use Phalcon\Support\Exception as HelperException;
use Phalcon\Cache\Adapter\Stream;
use Phalcon\Storage\Exception as CacheException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\HelperFactory;
use IntegrationTester;

use function outputDir;
use function uniqid;

class HasCest
{
    /**
     * Tests Phalcon\Cache\Adapter\Stream :: has()
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws CacheException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterStreamHas(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Stream - has()');

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
     * Tests Phalcon\Cache\Adapter\Stream :: has() - cannot open file
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws CacheException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterStreamHasCannotOpenFile(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Stream - has() - cannot open file');

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
     * Tests Phalcon\Cache\Adapter\Stream :: has() - empty payload
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws CacheException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterStreamHasEmptyPayload(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Stream - has() - empty payload');

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
