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

use IntegrationTester;
use Phalcon\Storage\Adapter\Apcu;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception;
use Phalcon\Tests\Fixtures\Traits\ApcuTrait;

class GetSetDefaultSerializerCest
{
    use ApcuTrait;

    /**
     * Tests Phalcon\Storage\Adapter\Apcu ::
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
        $I->wantToTest('Storage\Adapter\Apcu - getDefaultSerializer()/setDefaultSerializer()');

        $serializer = new SerializerFactory();
        $adapter    = new Apcu($serializer);

        $expected = 'php';
        $actual   = $adapter->getDefaultSerializer();
        $I->assertEquals($expected, $actual);

        $adapter->setDefaultSerializer('Base64');

        $expected = 'base64';
        $actual   = $adapter->getDefaultSerializer();
        $I->assertEquals($expected, $actual);
    }
}
