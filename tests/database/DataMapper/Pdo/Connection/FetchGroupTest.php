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

final class FetchGroupTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: fetchGroup()
     *
     * @since  2020-01-25
     *
     * @group  common
     */
    public function testDmPdoConnectionFetchGroup(): void
    {
        /** @var Connection $connection */
        $connection = self::getDataMapperConnection();
        $migration  = new InvoicesMigration($connection);
        $migration->clear();

        $result = $migration->insert(1, 1, 1, null, 101);
        $this->assertEquals(1, $result);
        $result = $migration->insert(2, 1, 0, null, 102);
        $this->assertEquals(1, $result);
        $result = $migration->insert(3, 1, 1, null, 103);
        $this->assertEquals(1, $result);
        $result = $migration->insert(4, 1, 0, null, 104);
        $this->assertEquals(1, $result);

        $all = $connection->fetchGroup(
            'SELECT inv_status_flag, inv_id, inv_total from co_invoices'
        );

        $this->assertEquals(2, $all[0][0]['inv_id']);
        $this->assertEquals(4, $all[0][1]['inv_id']);
        $this->assertEquals(1, $all[1][0]['inv_id']);
        $this->assertEquals(3, $all[1][1]['inv_id']);
    }
}
