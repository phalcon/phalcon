<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Pdo\Connection;

use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;

final class FetchColumnTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: fetchColumn()
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoConnectionFetchCol(): void
    {
        /** @var Connection $connection */
        $connection = self::getDataMapperConnection();
        $migration  = new InvoicesMigration($connection);
        $migration->clear();

        $result = $migration->insert(1, 1, 1, null, 101);
        $this->assertSame(1, $result);
        $result = $migration->insert(2, 1, 1, null, 102);
        $this->assertSame(1, $result);
        $result = $migration->insert(3, 1, 1, null, 103);
        $this->assertSame(1, $result);

        $all = $connection->fetchColumn(
            'select * from co_invoices'
        );

        $this->assertIsArray($all);
        $this->assertSame(1, $all[0]);
        $this->assertSame(2, $all[1]);
        $this->assertSame(3, $all[2]);

        $all = $connection->fetchColumn(
            'select * from co_invoices',
            [],
            4
        );

        /**
         * Intentionally casting things as an array because I don't want
         * drivers to fail based on how many decimals they can hold
         */
        $this->assertIsArray($all);
        $this->assertSame(101, (int)$all[0]);
        $this->assertSame(102, (int)$all[1]);
        $this->assertSame(103, (int)$all[2]);

        $all = $connection->yieldColumn(
            'select * from co_invoices'
        );

        $results = [];
        foreach ($all as $key => $item) {
            $results[$key] = $item;
        }

        $this->assertSame(1, $results[0]);
        $this->assertSame(2, $results[1]);
        $this->assertSame(3, $results[2]);
    }
}
