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

use DateInterval;
use Exception;
use Phalcon\Storage\Adapter\AdapterInterface;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Tests\Fixtures\Storage\Adapter\Libmemcached;
use Phalcon\Tests\Fixtures\Traits\LibmemcachedTrait;
use UnitTester;
use function getOptionsLibmemcached;

class ConstructCest
{
    use LibmemcachedTrait;

    /**
     * Tests Phalcon\Storage\Adapter\Libmemcached :: __construct()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterLibmemcachedConstruct(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Libmemcached - __construct()');

        $serializer = new SerializerFactory();

        $adapter = new Libmemcached($serializer, getOptionsLibmemcached());

        $expected = Libmemcached::class;
        $actual   = $adapter;
        $I->assertInstanceOf($expected, $actual);

        $expected = AdapterInterface::class;
        $actual   = $adapter;
        $I->assertInstanceOf($expected, $actual);
    }

    /**
     * Tests Phalcon\Storage\Adapter\Libmemcached :: __construct() - empty
     * options
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterLibmemcachedConstructEmptyOptions(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Libmemcached - __construct() - empty options');

        $serializer = new SerializerFactory();
        $adapter    = new Libmemcached($serializer);

        $expected = [
            'servers' => [
                0 => [
                    'host'   => '127.0.0.1',
                    'port'   => 11211,
                    'weight' => 1,
                ],
            ],
        ];
        $actual   = $adapter->getOptions();
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Storage\Adapter\Libmemcached :: __construct() - getTtl
     * options
     *
     * @param UnitTester $I
     *
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterLibmemcachedConstructGetTtl(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Libmemcached - __construct() - getTtl');

        $serializer = new SerializerFactory();
        $adapter    = new Libmemcached($serializer);

        $expected = 3600;
        $actual   = $adapter->getTtl(null);
        $I->assertEquals($expected, $actual);

        $expected = 20;
        $actual   = $adapter->getTtl(20);
        $I->assertEquals($expected, $actual);

        $time     = new DateInterval('PT5S');
        $expected = 5;
        $actual   = $adapter->getTtl($time);
        $I->assertEquals($expected, $actual);
    }
}
