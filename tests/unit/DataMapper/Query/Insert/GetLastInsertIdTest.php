<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\DataMapper\Query\Insert;

use Codeception\Stub;
use Phalcon\Tests\DatabaseTestCase;
use Phalcon\DataMapper\Query\QueryFactory;
use Phalcon\Tests\Fixtures\Migrations\Invoices;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;

use function uniqid;

final class GetLastInsertIdTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Insert :: getLastInsertId()
     *
     * @since  2020-01-20
     *
     * @group  pgsql
     * @group  mysql
     * @group  sqlite
     */
    public function testDmQueryInsertGetLastInsertId(): void
    {
        $connection     = $this->getDataMapperConnection();
        $mockConnection = Stub::make(
            $connection,
            [
                'lastInsertId' => "12345",
            ]
        );
        $factory        = new QueryFactory();
        $insert         = $factory->newInsert($mockConnection);

        $name = uniqid('inv-');
        $insert
            ->into('co_invoices')
            ->columns(
                [
                    'inv_cst_id'      => 1,
                    'inv_status_flag' => 1,
                    'inv_title'       => $name,
                    'inv_total'       => 100.00,
                ]
            )
            ->set(
                'inv_created_at',
                $this->getDatabaseNow($connection->getDriverName())
            )
        ;

        $invId = $insert->getLastInsertId();
        $this->assertEquals("12345", $invId);
    }

    /**
     * Database Tests Phalcon\DataMapper\Query\Insert :: getLastInsertId() -
     * real
     *
     * @since  2020-01-20
     *
     * @group  pgsql
     * @group  mysql
     * @group  sqlite
     */
    public function testDmQueryInsertGetLastInsertIdReal(): void
    {
        $connection = $this->getDataMapperConnection();
        $factory    = new QueryFactory();
        $insert     = $factory->newInsert($connection);
        (new InvoicesMigration($connection));

        $name = uniqid('inv-');
        $insert
            ->into('co_invoices')
            ->columns(
                [
                    'inv_cst_id'      => 1,
                    'inv_status_flag' => 1,
                    'inv_title'       => $name,
                    'inv_total'       => 100.00,
                ]
            )
            ->set(
                'inv_created_at',
                $this->getDatabaseNow($connection->getDriverName())
            )
        ;

        $insert->perform();
        $invId = $insert->getLastInsertId();

        $sql           = 'SELECT inv_id '
            . 'FROM co_invoices '
            . 'WHERE inv_title = "' . $name . '"';
        $result        = $connection->fetchOne($sql);
        $existingInvId = $result['inv_id'];

        $this->assertEquals($existingInvId, $invId);
    }
}
