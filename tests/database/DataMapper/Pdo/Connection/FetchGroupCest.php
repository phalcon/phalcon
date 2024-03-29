<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Pdo\Connection;

use DatabaseTester;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;

class FetchGroupCest
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: fetchGroup()
     *
     * @since  2020-01-25
     *
     * @group  pgsql
     * @group  mysql
     * @group  sqlite
     */
    public function dMPdoConnectionFetchGroup(DatabaseTester $I)
    {
        $I->wantToTest('DataMapper\Pdo\Connection - fetchGroup()');

        /** @var Connection $connection */
        $connection = $I->getDataMapperConnection();
        $migration  = new InvoicesMigration($connection);
        $migration->clear();

        $result = $migration->insert(1, 1, 1, null, 101);
        $I->assertEquals(1, $result);
        $result = $migration->insert(2, 1, 0, null, 102);
        $I->assertEquals(1, $result);
        $result = $migration->insert(3, 1, 1, null, 103);
        $I->assertEquals(1, $result);
        $result = $migration->insert(4, 1, 0, null, 104);
        $I->assertEquals(1, $result);

        $all = $connection->fetchGroup(
            'SELECT inv_status_flag, inv_id, inv_total from co_invoices'
        );

        $I->assertEquals(2, $all[0][0]['inv_id']);
        $I->assertEquals(4, $all[0][1]['inv_id']);
        $I->assertEquals(1, $all[1][0]['inv_id']);
        $I->assertEquals(3, $all[1][1]['inv_id']);
    }
}
