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

namespace Phalcon\Tests\Integration\Storage\Adapter\Libmemcached;

use Phalcon\Storage\Adapter\Libmemcached;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Tests\Fixtures\Traits\LibmemcachedTrait;
use UnitTester;
use function getOptionsLibmemcached;

class GetPrefixCest
{
    use LibmemcachedTrait;

    /**
     * Tests Phalcon\Storage\Adapter\Libmemcached :: getPrefix()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterLibmemcachedGetSetPrefix(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Libmemcached - getPrefix()');

        $serializer = new SerializerFactory();

        $adapter = new Libmemcached(
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
     * Tests Phalcon\Storage\Adapter\Libmemcached :: getPrefix() - default
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterLibmemcachedGetSetPrefixDefault(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Libmemcached - getPrefix() - default');

        $serializer = new SerializerFactory();
        $adapter    = new Libmemcached($serializer, getOptionsLibmemcached());

        $expected = 'ph-memc-';
        $actual   = $adapter->getPrefix();
        $I->assertEquals($expected, $actual);
    }
}
