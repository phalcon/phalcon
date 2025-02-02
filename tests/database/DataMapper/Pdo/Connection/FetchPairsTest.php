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

final class FetchPairsTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: fetchPairs()
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoConnectionFetchPairs(): void
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
        $result = $migration->insert(4, 1, 1, null, 104);
        $this->assertSame(1, $result);

        $all = $connection->fetchPairs(
            'SELECT inv_id, inv_total from co_invoices'
        );
        $this->assertCount(4, $all);

        $expected = [
            1 => 101.00,
            2 => 102.00,
            3 => 103.00,
            4 => 104.00,
        ];

        $this->assertSame($expected, $all);

        $all = $connection->yieldPairs(
            'SELECT inv_id, inv_total from co_invoices'
        );

        $results = [];
        foreach ($all as $key => $item) {
            $results[$key] = $item;
        }

        $this->assertCount(4, $results);

        $expected = [
            1 => 101.00,
            2 => 102.00,
            3 => 103.00,
            4 => 104.00,
        ];

        $this->assertSame($expected, $results);
    }
}
