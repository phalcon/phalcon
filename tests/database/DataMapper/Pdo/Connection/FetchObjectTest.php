<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Pdo\Connection;

use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\Tests\DatabaseTestCase;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;
use Phalcon\Tests\Fixtures\Resultset;
use stdClass;

final class FetchObjectTest extends DatabaseTestCase
{
    /**
     * Tests Phalcon\DataMapper\Pdo\Connection :: fetchObject() - ctor
     *
     * @since  2020-01-25
     */
    public function connectionFetchObjectCtor(): void
    {
        /** @var Connection $connection */
        $connection = $this->getDataMapperConnection();
        $migration  = new InvoicesMigration($connection);
        $migration->clear();

        $result = $migration->insert(1, 1, 1, null, 101);
        $this->assertEquals(1, $result);

        $all = $connection->fetchObject(
            'select inv_id, inv_total from co_invoices WHERE inv_id = ?',
            [
                0 => 1,
            ],
            Resultset::class,
            [
                'vader',
            ]
        );

        $this->assertInstanceOf(Resultset::class, $all);
        $this->assertEquals('vader', $all->calculated);
        $this->assertEquals(1, $all->inv_id);
        $this->assertEquals(101, $all->inv_total);
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: fetchObject()
     *
     * @since  2020-01-25
     *
     * @group  common
     */
    public function testDmPdoConnectionFetchObject(): void
    {
        /** @var Connection $connection */
        $connection = $this->getDataMapperConnection();
        $migration  = new InvoicesMigration($connection);
        $migration->clear();

        $result = $migration->insert(1, 1, 1, null, 101);
        $this->assertEquals(1, $result);

        $all = $connection->fetchObject(
            'select inv_id, inv_total from co_invoices WHERE inv_id = ?',
            [
                0 => 1,
            ]
        );

        $this->assertInstanceOf(stdClass::class, $all);
        $this->assertEquals(1, $all->inv_id);
        $this->assertEquals(101, $all->inv_total);
    }
}
