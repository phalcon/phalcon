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

class ExecCest
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: exec()
     *
     * @since  2020-01-25
     *
     * @group  pgsql
     * @group  mysql
     * @group  sqlite
     */
    public function dMPdoConnectionExec(DatabaseTester $I)
    {
        $I->wantToTest('DataMapper\Pdo\Connection - exec()');

        /** @var Connection $connection */
        $connection = $I->getDataMapperConnection();
        $migration  = new InvoicesMigration($connection);
        $migration->clear();

        $result = $migration->insert(1);
        $I->assertEquals(1, $result);
        $result = $migration->insert(2);
        $I->assertEquals(1, $result);
        $result = $migration->insert(3);
        $I->assertEquals(1, $result);
        $result = $migration->insert(4);
        $I->assertEquals(1, $result);

        $all = $connection->exec(
            'update co_invoices set inv_total = inv_total + 100'
        );

        $I->assertEquals(4, $all);
    }
}
