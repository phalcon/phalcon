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

use Phalcon\Support\Exception as HelperException;
use Phalcon\Cache\Adapter\AdapterInterface;
use Phalcon\Cache\Adapter\Stream;
use Phalcon\Storage\Exception as CacheException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\HelperFactory;
use IntegrationTester;

use function outputDir;

class ConstructCest
{
    /**
     * Tests Phalcon\Cache\Adapter\Stream :: __construct()
     */
    /**
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws CacheException
     */
    public function storageAdapterStreamConstruct(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Stream - __construct()');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $helper,
            $serializer,
            [
                'storageDir' => outputDir(),
            ]
        );

        $expected = Stream::class;
        $I->assertInstanceOf($expected, $adapter);
        $expected = AdapterInterface::class;
        $I->assertInstanceOf($expected, $adapter);
    }

    /**
     * Tests Phalcon\Cache\Adapter\Stream :: __construct() - exception
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterStreamConstructException(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Stream - __construct() - exception');

        $I->expectThrowable(
            new CacheException('The "storageDir" must be specified in the options'),
            function () {
                $helper     = new HelperFactory();
                $serializer = new SerializerFactory();
                $adapter    = new Stream($helper, $serializer);
            }
        );
    }
}
