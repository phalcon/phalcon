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

final class FetchAllTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: fetchAll()
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoConnectionFetchAll(): void
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

        $all = $connection->fetchAll(
            'SELECT * from co_invoices'
        );
        $this->assertCount(4, $all);

        $this->assertSame(1, $all[0]['inv_id']);
        $this->assertSame(2, $all[1]['inv_id']);
        $this->assertSame(3, $all[2]['inv_id']);
        $this->assertSame(4, $all[3]['inv_id']);

        $all = $connection->yieldAll(
            'SELECT * from co_invoices'
        );

        $results = [];
        foreach ($all as $key => $item) {
            $results[$key] = $item;
        }
        $this->assertCount(4, $results);

        $this->assertSame(1, $results[0]['inv_id']);
        $this->assertSame(2, $results[1]['inv_id']);
        $this->assertSame(3, $results[2]['inv_id']);
        $this->assertSame(4, $results[3]['inv_id']);
    }
}
