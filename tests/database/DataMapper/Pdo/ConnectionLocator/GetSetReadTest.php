<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Pdo\ConnectionLocator;

use Phalcon\DataMapper\Pdo\ConnectionLocator;
use Phalcon\DataMapper\Pdo\Exception\ConnectionNotFound;
use Phalcon\Tests\AbstractDatabaseTestCase;

use function in_array;
use function spl_object_hash;

final class GetSetReadTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\ConnectionLocator :: getRead() -
     * empty
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoConnectionLocatorGetReadEmpty(): void
    {
        $master  = self::getDataMapperConnection();
        $locator = new ConnectionLocator(
            function () use ($master) {
                return $master;
            }
        );

        $expected = spl_object_hash($master);
        $actual   = spl_object_hash($locator->getRead());
        $this->assertSame($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\ConnectionLocator :: getRead() -
     * exception
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoConnectionLocatorGetReadException(): void
    {
        $this->expectException(ConnectionNotFound::class);
        $this->expectExceptionMessage(
            "Connection not found: read:unknown"
        );

        $read1   = self::getDataMapperConnection();
        $locator = new ConnectionLocator(
            function () {
                return self::getDataMapperConnection();
            },
            [
                "read1" => function () use ($read1) {
                    return $read1;
                },
            ]
        );

        $locator->getRead("unknown");
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\ConnectionLocator :: getRead() -
     * random
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoConnectionLocatorGetReadRandom(): void
    {
        $read1   = self::getDataMapperConnection();
        $read2   = self::getDataMapperConnection();
        $locator = new ConnectionLocator(
            function () {
                return self::getDataMapperConnection();
            },
            [
                "read1" => function () use ($read1) {
                    return $read1;
                },
                "read2" => function () use ($read2) {
                    return $read2;
                },
            ]
        );

        $hashes = [
            spl_object_hash($read1),
            spl_object_hash($read2),
        ];

        $actual = $locator->getRead();
        $this->assertTrue(in_array(spl_object_hash($actual), $hashes));
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\ConnectionLocator ::
     * getRead()/setRead()
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoConnectionLocatorGetSetRead(): void
    {
        $master  = self::getDataMapperConnection();
        $read1   = self::getDataMapperConnection();
        $read2   = self::getDataMapperConnection();
        $locator = new ConnectionLocator(
            function () use ($master) {
                return $master;
            },
            [
                "read1" => function () use ($read1) {
                    return $read1;
                },
                "read2" => function () use ($read2) {
                    return $read2;
                },
            ]
        );

        $expected = spl_object_hash($read1);
        $actual   = spl_object_hash($locator->getRead("read1"));
        $this->assertSame($expected, $actual);

        $expected = spl_object_hash($read1);
        $actual   = spl_object_hash($locator->getRead("read1"));
        $this->assertSame($expected, $actual);
    }
}
