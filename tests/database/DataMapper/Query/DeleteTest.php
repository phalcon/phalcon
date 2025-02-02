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
use Phalcon\DataMapper\Query\Delete;
use Phalcon\DataMapper\Query\Select;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;

use function uniqid;

final class DeleteTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Delete :: delete()
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmQueryDelete(): void
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
         * Delete it
         */
        $delete = Delete::new(
            self::getDatabaseDsn(),
            self::getDatabaseUsername(),
            self::getDatabasePassword()
        );

        $statement = $delete
            ->table('co_invoices')
            ->where('inv_title = ', $title)
            ->perform()
        ;

        $this->assertInstanceOf(PDOStatement::class, $statement);

        /**
         * Find it again - should not exist
         */
        $expected = [];
        $actual   = $select->fetchOne();
        $this->assertSame($expected, $actual);
    }
}
