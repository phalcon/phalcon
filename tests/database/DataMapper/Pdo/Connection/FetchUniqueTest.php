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

final class FetchUniqueTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: fetchUnique()
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoConnectionFetchUnique(): void
    {
        /** @var Connection $connection */
        $connection = self::getDataMapperConnection();
        $migration  = new InvoicesMigration($connection);
        $migration->clear();

        $result = $migration->insert(1, 1);
        $this->assertSame(1, $result);
        $result = $migration->insert(2, 2);
        $this->assertSame(1, $result);
        $result = $migration->insert(3, 3);
        $this->assertSame(1, $result);
        $result = $migration->insert(4, 4);
        $this->assertSame(1, $result);

        $all = $connection->fetchUnique(
            'SELECT * from co_invoices ORDER BY inv_id'
        );
        $this->assertCount(4, $all);

        $this->assertSame(1, $all[1]['inv_cst_id']);
        $this->assertSame(2, $all[2]['inv_cst_id']);
        $this->assertSame(3, $all[3]['inv_cst_id']);
        $this->assertSame(4, $all[4]['inv_cst_id']);

        $all = $connection->yieldUnique(
            'SELECT * from co_invoices ORDER BY inv_id'
        );

        $results = [];
        foreach ($all as $key => $item) {
            $results[$key] = $item;
        }

        $this->assertCount(4, $results);

        $this->assertSame(1, $results[1]['inv_cst_id']);
        $this->assertSame(2, $results[2]['inv_cst_id']);
        $this->assertSame(3, $results[3]['inv_cst_id']);
        $this->assertSame(4, $results[4]['inv_cst_id']);
    }
}
