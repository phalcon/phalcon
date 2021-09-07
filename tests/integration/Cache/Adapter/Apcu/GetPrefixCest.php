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

use IntegrationTester;
use Phalcon\Cache\Adapter\Apcu;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception;
use Phalcon\Support\HelperFactory;
use Phalcon\Tests\Fixtures\Traits\ApcuTrait;

class GetPrefixCest
{
    use ApcuTrait;

    /**
     * Tests Phalcon\Cache\Adapter\Apcu :: getPrefix()
     *
     * @param IntegrationTester $I
     *
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterApcuGetSetPrefix(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Apcu - getPrefix()');

        $serializer = new SerializerFactory();

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Apcu(
            $helper,
            $serializer,
            [
                'prefix' => 'my-prefix',
            ]
        );

        $expected = 'my-prefix';
        $actual   = $adapter->getPrefix();
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Cache\Adapter\Apcu :: getPrefix() - default
     *
     * @param IntegrationTester $I
     *
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterApcuGetSetPrefixDefault(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Apcu - getPrefix() - default');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Apcu($helper, $serializer);

        $expected = 'ph-apcu-';
        $actual   = $adapter->getPrefix();
        $I->assertEquals($expected, $actual);
    }
}
