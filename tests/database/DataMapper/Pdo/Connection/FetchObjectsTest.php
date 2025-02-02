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
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;
use Phalcon\Tests\Fixtures\Resultset;
use stdClass;

final class FetchObjectsTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: fetchObjects()
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoConnectionFetchObjects(): void
    {
        /** @var Connection $connection */
        $connection = self::getDataMapperConnection();
        $migration  = new InvoicesMigration($connection);
        $migration->clear();

        $result = $migration->insert(1);
        $this->assertSame(1, $result);
        $result = $migration->insert(2);
        $this->assertSame(1, $result);
        $result = $migration->insert(3);
        $this->assertSame(1, $result);
        $result = $migration->insert(4);
        $this->assertSame(1, $result);

        $all = $connection->fetchObjects(
            'SELECT * from co_invoices'
        );
        $this->assertCount(4, $all);

        $this->assertInstanceOf(stdClass::class, $all[0]);
        $this->assertInstanceOf(stdClass::class, $all[1]);
        $this->assertInstanceOf(stdClass::class, $all[2]);
        $this->assertInstanceOf(stdClass::class, $all[3]);

        $this->assertSame(1, $all[0]->inv_id);
        $this->assertSame(2, $all[1]->inv_id);
        $this->assertSame(3, $all[2]->inv_id);
        $this->assertSame(4, $all[3]->inv_id);

        $all = $connection->yieldObjects(
            'SELECT * from co_invoices'
        );

        $results = [];
        foreach ($all as $key => $item) {
            $results[$key] = $item;
        }

        $this->assertCount(4, $results);

        $this->assertInstanceOf(stdClass::class, $results[0]);
        $this->assertInstanceOf(stdClass::class, $results[1]);
        $this->assertInstanceOf(stdClass::class, $results[2]);
        $this->assertInstanceOf(stdClass::class, $results[3]);

        $this->assertSame(1, $results[0]->inv_id);
        $this->assertSame(2, $results[1]->inv_id);
        $this->assertSame(3, $results[2]->inv_id);
        $this->assertSame(4, $results[3]->inv_id);
    }

    /**
     * Tests Phalcon\DataMapper\Pdo\Connection :: fetchObjects() - ctor
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoConnectionFetchObjectsCtor(): void
    {
        /** @var Connection $connection */
        $connection = self::getDataMapperConnection();
        $migration  = new InvoicesMigration($connection);
        $migration->clear();

        $result = $migration->insert(1);
        $this->assertSame(1, $result);
        $result = $migration->insert(2);
        $this->assertSame(1, $result);
        $result = $migration->insert(3);
        $this->assertSame(1, $result);
        $result = $migration->insert(4);
        $this->assertSame(1, $result);

        $all = $connection->fetchObjects(
            'SELECT * from co_invoices',
            [],
            Resultset::class,
            [
                'darth',
            ]
        );
        $this->assertCount(4, $all);

        $this->assertInstanceOf(Resultset::class, $all[0]);
        $this->assertInstanceOf(Resultset::class, $all[1]);
        $this->assertInstanceOf(Resultset::class, $all[2]);
        $this->assertInstanceOf(Resultset::class, $all[3]);

        $this->assertSame(1, $all[0]->inv_id);
        $this->assertSame(2, $all[1]->inv_id);
        $this->assertSame(3, $all[2]->inv_id);
        $this->assertSame(4, $all[3]->inv_id);

        $this->assertSame('darth', $all[0]->calculated);
        $this->assertSame('darth', $all[1]->calculated);
        $this->assertSame('darth', $all[2]->calculated);
        $this->assertSame('darth', $all[3]->calculated);

        $all = $connection->yieldObjects(
            'SELECT * from co_invoices',
            [],
            Resultset::class,
            [
                'darth',
            ]
        );

        $results = [];
        foreach ($all as $key => $item) {
            $results[$key] = $item;
        }

        $this->assertCount(4, $results);

        $this->assertInstanceOf(Resultset::class, $results[0]);
        $this->assertInstanceOf(Resultset::class, $results[1]);
        $this->assertInstanceOf(Resultset::class, $results[2]);
        $this->assertInstanceOf(Resultset::class, $results[3]);

        $this->assertSame(1, $results[0]->inv_id);
        $this->assertSame(2, $results[1]->inv_id);
        $this->assertSame(3, $results[2]->inv_id);
        $this->assertSame(4, $results[3]->inv_id);

        $this->assertSame('darth', $results[0]->calculated);
        $this->assertSame('darth', $results[1]->calculated);
        $this->assertSame('darth', $results[2]->calculated);
        $this->assertSame('darth', $results[3]->calculated);
    }
}
