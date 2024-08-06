<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\DataMapper\Pdo\Connection;

use Phalcon\Tests\DatabaseTestCase;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;

final class FetchValueTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: fetchValue()
     *
     * @since  2020-01-25
     *
     * @group  pgsql
     * @group  mysql
     * @group  sqlite
     */
    public function testDmPdoConnectionFetchValue(): void
    {
        /** @var Connection $connection */
        $connection = $this->getDataMapperConnection();
        $migration  = new InvoicesMigration($connection);
        $migration->clear();

        $result = $migration->insert(1, 1, 1, null, 101);
        $this->assertEquals(1, $result);

        $all = $connection->fetchValue(
            'select inv_total from co_invoices WHERE inv_cst_id = ?',
            [
                0 => 1,
            ]
        );
        $this->assertEquals(101, $all);
    }
}
