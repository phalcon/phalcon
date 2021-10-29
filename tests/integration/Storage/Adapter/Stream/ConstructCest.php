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

use IntegrationTester;
use Phalcon\Storage\Adapter\AdapterInterface;
use Phalcon\Storage\Adapter\Stream;
use Phalcon\Storage\Exception as StorageException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as HelperException;

use function outputDir;

class ConstructCest
{
    /**
     * Tests Phalcon\Storage\Adapter\Stream :: __construct()
     */
    /**
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws StorageException
     */
    public function storageAdapterStreamConstruct(IntegrationTester $I)
    {
        $I->wantToTest('Storage\Adapter\Stream - __construct()');

        $serializer = new SerializerFactory();
        $adapter    = new Stream(
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
     * Tests Phalcon\Storage\Adapter\Stream :: __construct() - exception
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterStreamConstructException(IntegrationTester $I)
    {
        $I->wantToTest('Storage\Adapter\Stream - __construct() - exception');

        $I->expectThrowable(
            new StorageException('The "storageDir" must be specified in the options'),
            function () {
                $serializer = new SerializerFactory();
                $adapter    = new Stream($serializer);
            }
        );
    }
}
