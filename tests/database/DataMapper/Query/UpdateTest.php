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

namespace Phalcon\Tests\Database\DataMapper\Query;

use PDOStatement;
use Phalcon\DataMapper\Query\Select;
use Phalcon\DataMapper\Query\Update;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;

use function uniqid;

final class UpdateTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Update :: update
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmQueryUpdate(): void
    {
        $connection = self::getDataMapperConnection();
        $invoices   = new InvoicesMigration($connection);

        /**
         * Create an invoice
         */
        $title = uniqid('tit-');
        $invoices->insert(1, 1, 1, $title, 100.0, '2024-02-01 10:11:12');

        /**
         * Find it
         */
        $select = Select::new(
            self::getDatabaseDsn(),
            self::getDatabaseUsername(),
            self::getDatabasePassword()
        );
        $select
            ->from('co_invoices')
            ->where('inv_title = ', $title)
        ;

        $expected = [
            'inv_id'          => 1,
            'inv_cst_id'      => 1,
            'inv_status_flag' => 1,
            'inv_title'       => $title,
            'inv_total'       => 100.0,
            'inv_created_at'  => '2024-02-01 10:11:12',
        ];
        $actual   = $select->fetchOne();
        $this->assertSame($expected, $actual);

        /**
         * Update it
         */
        $update = Update::new(
            self::getDatabaseDsn(),
            self::getDatabaseUsername(),
            self::getDatabasePassword()
        );

        $statement = $update
            ->table('co_invoices')
            ->column('inv_cst_id', 2)
            ->column('inv_total', 200.0)
            ->column('inv_status_flag', 2)
            ->column('inv_created_at', '2024-12-11 20:21:22')
            ->where('inv_title = ', $title)
            ->perform()
        ;

        $this->assertInstanceOf(PDOStatement::class, $statement);

        /**
         * Find it again - it should be updated
         */
        $select = Select::new(
            self::getDatabaseDsn(),
            self::getDatabaseUsername(),
            self::getDatabasePassword()
        );
        $select
            ->from('co_invoices')
            ->where('inv_title = ', $title)
        ;

        $expected = [
            'inv_id'          => 1,
            'inv_cst_id'      => 2,
            'inv_status_flag' => 2,
            'inv_title'       => $title,
            'inv_total'       => 200.0,
            'inv_created_at'  => '2024-12-11 20:21:22',
        ];
        $actual   = $select->fetchOne();
        $this->assertSame($expected, $actual);
    }
}
