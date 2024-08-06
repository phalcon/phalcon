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

use function date;
use function uniqid;

final class CommitInTransactionRollBackTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection ::
     * commit()/inTransaction()
     *
     * @since  2020-01-25
     *
     * @group  common
     */
    public function testDmPdoConnectionCommitInTransaction(): void
    {
        /** @var Connection $connection */
        $connection = self::getDataMapperConnection();
        (new InvoicesMigration($connection));
        $connection->beginTransaction();

        $this->assertTrue($connection->inTransaction());

        $invId = 2;
        $title = uniqid('inv-');
        $date  = date('Y-m-d H:i:s');
        $sql   = "insert into co_invoices (inv_id, inv_cst_id, inv_status_flag, "
            . "inv_title, inv_total, inv_created_at) values ("
            . "{$invId}, 1, 1, '{$title}', 102, '{$date}')";

        $result = $connection->exec($sql);
        $this->assertEquals(1, $result);

        $connection->commit();

        /**
         * Committed record
         */
        $all = $connection
            ->fetchOne(
                'select * from co_invoices WHERE inv_id = ?',
                [
                    0 => $invId,
                ]
            )
        ;

        $this->assertIsArray($all);
        $this->assertEquals($invId, $all['inv_id']);
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: rollBack()
     *
     * @since  2020-01-25
     *
     * @group  common
     */
    public function testDmPdoConnectionRollBack(): void
    {
        /** @var Connection $connection */
        $connection = self::getDataMapperConnection();
        (new InvoicesMigration($connection));
        $connection->beginTransaction();

        $this->assertTrue($connection->inTransaction());

        $invId = 2;
        $title = uniqid('inv-');
        $date  = date('Y-m-d H:i:s');
        $sql   = "insert into co_invoices (inv_id, inv_cst_id, inv_status_flag, "
            . "inv_title, inv_total, inv_created_at) values ("
            . "{$invId}, 1, 1, '{$title}', 102, '{$date}')";

        $result = $connection->exec($sql);
        $this->assertEquals(1, $result);

        /**
         * Committed record
         */
        $all = $connection
            ->fetchOne(
                'select * from co_invoices WHERE inv_id = ?',
                [
                    0 => $invId,
                ]
            )
        ;

        $connection->rollBack();

        $all = $connection
            ->fetchOne(
                'select * from co_invoices WHERE inv_id = ?',
                [
                    0 => $invId,
                ]
            )
        ;

        $this->assertIsArray($all);
        $this->assertEmpty($all);
    }
}
