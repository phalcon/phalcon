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

final class FetchValueTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: fetchValue()
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoConnectionFetchValue(): void
    {
        /** @var Connection $connection */
        $connection = self::getDataMapperConnection();
        $migration  = new InvoicesMigration($connection);
        $migration->clear();

        $result = $migration->insert(1, 1, 1, null, 101);
        $this->assertSame(1, $result);

        $all = $connection->fetchValue(
            'select inv_total from co_invoices WHERE inv_cst_id = ?',
            [
                0 => 1,
            ]
        );
        $this->assertSame(101.0, $all);
    }
}
