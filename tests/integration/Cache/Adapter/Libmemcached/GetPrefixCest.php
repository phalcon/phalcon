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

namespace Phalcon\Tests\Integration\Cache\Adapter\Libmemcached;

use Phalcon\Cache\Adapter\Libmemcached;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\HelperFactory;
use Phalcon\Tests\Fixtures\Traits\LibmemcachedTrait;
use IntegrationTester;

use function getOptionsLibmemcached;

class GetPrefixCest
{
    use LibmemcachedTrait;

    /**
     * Tests Phalcon\Cache\Adapter\Libmemcached :: getPrefix()
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterLibmemcachedGetSetPrefix(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Libmemcached - getPrefix()');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Libmemcached(
            $helper,
            $serializer,
            array_merge(
                getOptionsLibmemcached(),
                [
                    'prefix' => 'my-prefix',
                ]
            )
        );

        $expected = 'my-prefix';
        $actual   = $adapter->getPrefix();
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Cache\Adapter\Libmemcached :: getPrefix() - default
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterLibmemcachedGetSetPrefixDefault(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Libmemcached - getPrefix() - default');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Libmemcached(
            $helper,
            $serializer,
            getOptionsLibmemcached()
        );

        $expected = 'ph-memc-';
        $actual   = $adapter->getPrefix();
        $I->assertEquals($expected, $actual);
    }
}
