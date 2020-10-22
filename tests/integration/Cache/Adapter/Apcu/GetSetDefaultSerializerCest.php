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

class GetSetDefaultSerializerCest
{
    use ApcuTrait;

    /**
     * Tests Phalcon\Cache\Adapter\Apcu ::
     * getDefaultSerializer()/setDefaultSerializer()
     *
     * @param IntegrationTester $I
     *
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterApcuGetKeys(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Apcu - getDefaultSerializer()/setDefaultSerializer()');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Apcu($helper, $serializer);

        $expected = 'php';
        $actual   = $adapter->getDefaultSerializer();
        $I->assertEquals($expected, $actual);

        $adapter->setDefaultSerializer('Base64');

        $expected = 'base64';
        $actual   = $adapter->getDefaultSerializer();
        $I->assertEquals($expected, $actual);
    }
}
