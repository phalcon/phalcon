<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\DataMapper\Pdo\Connection;

use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\Tests\DatabaseTestCase;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;
use Phalcon\Tests\Fixtures\Resultset;
use stdClass;

final class FetchUniqueTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: fetchUnique()
     *
     * @since  2020-01-25
     *
     * @group  pgsql
     * @group  mysql
     * @group  sqlite
     */
    public function testDmPdoConnectionFetchUnique(): void
    {
        /** @var Connection $connection */
        $connection = $this->getDataMapperConnection();
        $migration  = new InvoicesMigration($connection);
        $migration->clear();

        $result = $migration->insert(1, 1);
        $this->assertEquals(1, $result);
        $result = $migration->insert(2, 2);
        $this->assertEquals(1, $result);
        $result = $migration->insert(3, 3);
        $this->assertEquals(1, $result);
        $result = $migration->insert(4, 4);
        $this->assertEquals(1, $result);

        $all = $connection->fetchUnique(
            'SELECT * from co_invoices ORDER BY inv_id'
        );
        $this->assertCount(4, $all);

        $this->assertEquals(1, $all[1]['inv_cst_id']);
        $this->assertEquals(2, $all[2]['inv_cst_id']);
        $this->assertEquals(3, $all[3]['inv_cst_id']);
        $this->assertEquals(4, $all[4]['inv_cst_id']);

        $all = $connection->yieldUnique(
            'SELECT * from co_invoices ORDER BY inv_id'
        );

        $results = [];
        foreach ($all as $key => $item) {
            $results[$key] = $item;
        }

        $this->assertCount(4, $results);

        $this->assertEquals(1, $results[1]['inv_cst_id']);
        $this->assertEquals(2, $results[2]['inv_cst_id']);
        $this->assertEquals(3, $results[3]['inv_cst_id']);
        $this->assertEquals(4, $results[4]['inv_cst_id']);
    }
}
