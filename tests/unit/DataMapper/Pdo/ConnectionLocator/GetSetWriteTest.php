<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\DataMapper\Pdo\ConnectionLocator;

use Phalcon\Tests\DatabaseTestCase;
use Phalcon\DataMapper\Pdo\ConnectionLocator;
use Phalcon\DataMapper\Pdo\Exception\ConnectionNotFound;

use function in_array;
use function spl_object_hash;

final class GetSetWriteTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\ConnectionLocator ::
     * getWrite()/setWrite()
     *
     * @since  2020-01-25
     *
     * @group  common
     */
    public function testDmPdoConnectionLocatorGetSetWrite(): void
    {
        $master  = $this->getDataMapperConnection();
        $write1  = $this->getDataMapperConnection();
        $write2  = $this->getDataMapperConnection();
        $locator = new ConnectionLocator(
            $master,
            [],
            [
                "write1" => function () use ($write1) {
                    return $write1;
                },
                "write2" => function () use ($write2) {
                    return $write2;
                },
            ]
        );

        $actual = $locator->getWrite("write1");
        $this->assertEquals(spl_object_hash($write1), spl_object_hash($actual));

        $actual = $locator->getWrite("write2");
        $this->assertEquals(spl_object_hash($write2), spl_object_hash($actual));
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\ConnectionLocator :: getWrite() -
     * empty
     *
     * @since  2020-01-25
     */
    public function testDmPdoConnectionLocatorGetWriteEmpty(): void
    {
        $master  = $this->getDataMapperConnection();
        $locator = new ConnectionLocator($master);

        $actual = $locator->getWrite("write1");
        $this->assertEquals(spl_object_hash($master), spl_object_hash($actual));
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\ConnectionLocator :: getWrite() -
     * exception
     *
     * @since  2020-01-25
     */
    public function testDmPdoConnectionLocatorGetWriteException(): void
    {
        $this->expectException(ConnectionNotFound::class);
        $this->expectExceptionMessage("Connection not found: write:unknown");

        $master  = $this->getDataMapperConnection();
        $write1  = $this->getDataMapperConnection();
        $locator = new ConnectionLocator(
            $master,
            [],
            [
                "write1" => function () use ($write1) {
                    return $write1;
                },
            ]
        );

        $locator->getWrite("unknown");
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\ConnectionLocator :: getWrite() -
     * random
     *
     * @since  2020-01-25
     */
    public function testDmPdoConnectionLocatorGetWriteRandom(): void
    {
        $master  = $this->getDataMapperConnection();
        $write1  = $this->getDataMapperConnection();
        $write2  = $this->getDataMapperConnection();
        $locator = new ConnectionLocator(
            $master,
            [],
            [
                "write1" => function () use ($write1) {
                    return $write1;
                },
                "write2" => function () use ($write2) {
                    return $write2;
                },
            ]
        );

        $hashes = [
            spl_object_hash($write1),
            spl_object_hash($write2),
        ];

        $actual = $locator->getWrite();
        $this->assertTrue(in_array(spl_object_hash($actual), $hashes));
    }
}
