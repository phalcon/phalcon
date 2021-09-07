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

use IntegrationTester;
use Phalcon\Cache\Adapter\Stream;
use Phalcon\Storage\Exception as CacheException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as HelperException;
use Phalcon\Support\HelperFactory;

use function outputDir;

class GetSetDefaultSerializerCest
{
    /**
     * Tests Phalcon\Cache\Adapter\Stream ::
     * getDefaultSerializer()/setDefaultSerializer()
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws CacheException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterStreamGetKeys(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Stream - getDefaultSerializer()/setDefaultSerializer()');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $helper,
            $serializer,
            [
                'storageDir' => outputDir(),
            ]
        );

        $expected = 'php';
        $actual   = $adapter->getDefaultSerializer();
        $I->assertEquals($expected, $actual);

        $adapter->setDefaultSerializer('Base64');
        $expected = 'base64';
        $actual   = $adapter->getDefaultSerializer();
        $I->assertEquals($expected, $actual);
    }
}
