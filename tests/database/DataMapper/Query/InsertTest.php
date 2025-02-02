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
use Phalcon\DataMapper\Query\Insert;
use Phalcon\DataMapper\Query\Select;
use Phalcon\Tests\AbstractDatabaseTestCase;

use function uniqid;

final class InsertTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Insert :: insert()
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmQueryInsert(): void
    {
        $title = uniqid('tit-');

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

        /**
         * Find it - should not exist
         */
        $expected = [];
        $actual   = $select->fetchOne();
        $this->assertSame($expected, $actual);

        $insert = Insert::new(
            self::getDatabaseDsn(),
            self::getDatabaseUsername(),
            self::getDatabasePassword()
        );

        $statement = $insert
            ->into('co_invoices')
            ->column('inv_id', 1)
            ->column('inv_cst_id', 1)
            ->column('inv_status_flag', 1)
            ->column('inv_title', $title)
            ->column('inv_total', 100.0)
            ->column('inv_created_at', '2024-02-01 10:11:12')
            ->perform()
        ;

        $this->assertInstanceOf(PDOStatement::class, $statement);

        $expected = 1;
        $actual   = (int)$insert->getLastInsertId();
        $this->assertSame($expected, $actual);

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
    }
}
