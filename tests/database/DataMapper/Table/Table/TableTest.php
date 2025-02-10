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

namespace Phalcon\Tests\Database\DataMapper\Table\Table;

use PDO;
use PDOStatement;
use Phalcon\DataMapper\Table\AbstractTable;
use Phalcon\DataMapper\Table\Exception\NoPrimaryKeyException;
use Phalcon\DataMapper\Table\Exception\PrimaryValueChangedException;
use Phalcon\DataMapper\Table\Exception\PrimaryValueMissingException;
use Phalcon\DataMapper\Table\Exception\PrimaryValueNotScalarException;
use Phalcon\DataMapper\Table\Exception\UnexpectedRowCountAffectedException;
use Phalcon\DataMapper\Table\TableLocator;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Fixtures\DataMapper\Table\Invoices\InvoicesRow;
use Phalcon\Tests\Fixtures\DataMapper\Table\Invoices\InvoicesTable;
use Phalcon\Tests\Fixtures\DataMapper\Table\NoPrimaryKey\NoPrimaryKeyTable;
use Phalcon\Tests\Fixtures\DataMapper\Table\OrdersXProducts\OrdersXProductsRow;
use Phalcon\Tests\Fixtures\DataMapper\Table\OrdersXProducts\OrdersXProductsTable;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;
use Phalcon\Tests\Fixtures\Migrations\OrdersProductsMigration;

use function uniqid;

final class TableTest extends AbstractDatabaseTestCase
{
    /**
     * @var PDO
     */
    private PDO $connection;
    /**
     * @var TableLocator
     */
    private TableLocator $locator;
    /**
     * @var InvoicesTable|AbstractTable
     */
    private InvoicesTable $table;

    public function setUp(): void
    {
        parent::setUp();

        $connection = self::getConnection();

        /**
         * This is here to clear the table
         */
        (new InvoicesMigration($connection));
        $this->locator    = TableLocator::new($connection);
        $this->table      = $this->locator->get(InvoicesTable::class);
        $this->connection = $connection;
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testDeleteRow(): void
    {
        $title = uniqid('tit-');
        $data  = [
            'inv_id'          => 10,
            'inv_cst_id'      => 10,
            'inv_status_flag' => 1,
            'inv_title'       => $title,
            'inv_total'       => 100.12,
            'inv_created_at'  => '2018-01-01 01:02:03',
        ];

        $newRow = $this->table->newRow($data);
        $actual = $this->table->insertRow($newRow);
        $this->assertInstanceOf(PDOStatement::class, $actual);

        /**
         * Get the row again
         */
        $row = $this->table->fetchRow(10);

        /**
         * Delete it
         */
        $actual = $this->table->deleteRow($row);
        $this->assertInstanceOf(PDOStatement::CLASS, $actual);

        /**
         * Check if it was deleted
         */
        $actual = $this->table->fetchRow(1);
        $this->assertNull($actual);

        /**
         * Delete it again
         */
        $actual = $this->table->deleteRow($row);
        $this->assertNull($actual);

        /**
         * Try to change the action of the record and delete again
         */
        $row->setLastAction($row::SELECT);
        $this->expectException(UnexpectedRowCountAffectedException::class);
        $this->expectExceptionMessage(
            'Expected 1 row affected [actual: 0].'
        );
        $this->table->deleteRow($row);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testDeleteRowNoPrimaryKey(): void
    {
        $table = $this->locator->get(NoPrimaryKeyTable::class);
        $name  = uniqid('nam-');
        $data = [
            'nokey_id'   => 1,
            'nokey_name' => $name,
        ];
        $newRow = $table->newRow($data);
        $table->insertRow($newRow);

        $this->expectException(NoPrimaryKeyException::class);
        $this->expectExceptionMessage(
            'Cannot delete row on table [no_primary_key] without a primary key.'
        );
        $table->deleteRow($newRow);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testFetchRowCompositeKey(): void
    {
        $table = $this->locator->get(OrdersXProductsTable::class);

        $migration = new OrdersProductsMigration($this->connection);
        $migration->insert(1, 1, 10);

        $row = $table->fetchRow(
            [
                'oxp_ord_id' => 1,
                'oxp_prd_id' => 1,
            ]
        );
        $this->assertInstanceOf(OrdersXProductsRow::class, $row);

        $expected = [
            'oxp_ord_id'   => 1,
            'oxp_prd_id'   => 1,
            'oxp_quantity' => 10,
        ];
        $actual   = $row->getCopy();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testFetchRowCompositeKeyMissingThrowsException(): void
    {
        $table = $this->locator->get(OrdersXProductsTable::class);

        $migration = new OrdersProductsMigration($this->connection);
        $migration->insert(1, 1, 10);

        /**
         * Exception for missing primary value
         */
        $this->expectException(PrimaryValueMissingException::class);
        $this->expectExceptionMessage(
            'The scalar value for primary key [oxp_prd_id] is missing.'
        );

        $row = $table->fetchRow(
            [
                'oxp_ord_id' => 1,
            ]
        );
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testFetchRowCompositeKeyPrimaryValueNotScalarThrowsException(): void
    {
        $table = $this->locator->get(OrdersXProductsTable::class);

        $migration = new OrdersProductsMigration($this->connection);
        $migration->insert(1, 1, 10);

        /**
         * Exception for not scalar value
         */
        $this->expectException(PrimaryValueNotScalarException::class);
        $this->expectExceptionMessage(
            'The value for primary key [oxp_prd_id] is not scalar, got [array].'
        );

        $row = $table->fetchRow(
            [
                'oxp_ord_id' => 1,
                'oxp_prd_id' => [1],
            ]
        );
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testFetchRow(): void
    {
        $title = uniqid('tit-');
        $data  = [
            0 => [
                'inv_id'          => 1,
                'inv_cst_id'      => 1,
                'inv_status_flag' => 1,
                'inv_title'       => $title . '-1',
                'inv_total'       => 100.12,
                'inv_created_at'  => '2018-01-01 01:02:13',
            ],
            1 => [
                'inv_id'          => 2,
                'inv_cst_id'      => 2,
                'inv_status_flag' => 2,
                'inv_title'       => $title . '-2',
                'inv_total'       => 200.23,
                'inv_created_at'  => '2018-01-01 01:02:23',
            ],
            2 => [
                'inv_id'          => 3,
                'inv_cst_id'      => 3,
                'inv_status_flag' => 2,
                'inv_title'       => $title . '-3',
                'inv_total'       => 300.34,
                'inv_created_at'  => '2018-01-01 01:02:33',
            ],
        ];

        $migration = new InvoicesMigration($this->connection);
        foreach ($data as $row) {
            $migration->insert(
                $row['inv_id'],
                $row['inv_cst_id'],
                $row['inv_status_flag'],
                $row['inv_title'],
                $row['inv_total'],
                $row['inv_created_at']
            );
        }

        $row = $this->table->fetchRow(4);
        $this->assertNull($row);

        $row = $this->table->fetchRow(1);
        $expected = $data[0];
        $actual   = $row->getCopy();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testFetchRows(): void
    {
        $title = uniqid('tit-');
        $data  = [
            0 => [
                'inv_id'          => 1,
                'inv_cst_id'      => 1,
                'inv_status_flag' => 1,
                'inv_title'       => $title . '-1',
                'inv_total'       => 100.12,
                'inv_created_at'  => '2018-01-01 01:02:13',
            ],
            1 => [
                'inv_id'          => 2,
                'inv_cst_id'      => 2,
                'inv_status_flag' => 2,
                'inv_title'       => $title . '-2',
                'inv_total'       => 200.23,
                'inv_created_at'  => '2018-01-01 01:02:23',
            ],
            2 => [
                'inv_id'          => 3,
                'inv_cst_id'      => 3,
                'inv_status_flag' => 2,
                'inv_title'       => $title . '-3',
                'inv_total'       => 300.34,
                'inv_created_at'  => '2018-01-01 01:02:33',
            ],
        ];

        $migration = new InvoicesMigration($this->connection);
        foreach ($data as $row) {
            $migration->insert(
                $row['inv_id'],
                $row['inv_cst_id'],
                $row['inv_status_flag'],
                $row['inv_title'],
                $row['inv_total'],
                $row['inv_created_at']
            );
        }

        $select = $this->table->select();

        $rows = $this->table->fetchRows([1, 2, 3]);

        $actual = $rows;
        $this->assertIsArray($actual);
        $this->assertCount(3, $actual);

        $this->assertInstanceOf(InvoicesRow::class, $rows[0]);
        $this->assertInstanceOf(InvoicesRow::class, $rows[1]);
        $this->assertInstanceOf(InvoicesRow::class, $rows[2]);

        $expected = $data[0];
        $actual   = $rows[0]->getCopy();
        $this->assertSame($expected, $actual);

        $expected = $data[1];
        $actual   = $rows[1]->getCopy();
        $this->assertSame($expected, $actual);

        $expected = $data[2];
        $actual   = $rows[2]->getCopy();
        $this->assertSame($expected, $actual);

        $actual = $select
            ->where('inv_id = ', -1)
            ->fetchRows()
        ;
        $this->assertIsArray($actual);
        $this->assertEmpty($actual);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testFetchRowsCompositeKey(): void
    {
        $table = $this->locator->get(OrdersXProductsTable::class);

        $data      = [
            [
                'oxp_ord_id'   => 1,
                'oxp_prd_id'   => 1,
                'oxp_quantity' => 10,
            ],
            [
                'oxp_ord_id'   => 2,
                'oxp_prd_id'   => 1,
                'oxp_quantity' => 20,
            ],
            [
                'oxp_ord_id'   => 3,
                'oxp_prd_id'   => 1,
                'oxp_quantity' => 30,
            ],
        ];
        $migration = new OrdersProductsMigration($this->connection);
        foreach ($data as $record) {
            $migration->insert(
                $record['oxp_ord_id'],
                $record['oxp_prd_id'],
                $record['oxp_quantity']
            );
        }

        $conditions = [
            [
                'oxp_ord_id' => 1,
                'oxp_prd_id' => 1,
            ],
            [
                'oxp_ord_id' => 2,
                'oxp_prd_id' => 1,
            ],
            [
                'oxp_ord_id' => 3,
                'oxp_prd_id' => 1,
            ],
        ];
        $rows       = $table->fetchRows($conditions);
        $this->assertIsArray($rows);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testInsertRow(): void
    {
        $title = uniqid('tit-');
        $data  = [
            'inv_id'          => 10,
            'inv_cst_id'      => 10,
            'inv_status_flag' => 1,
            'inv_title'       => $title,
            'inv_total'       => 100.12,
            'inv_created_at'  => '2018-01-01 01:02:03',
        ];

        $newRow = $this->table->newRow($data);
        $actual = $this->table->insertRow($newRow);
        $this->assertInstanceOf(PDOStatement::class, $actual);

        $expected  = '10';
        $invoiceId = $newRow->get('inv_id');
        $this->assertSame($expected, $invoiceId);

        $dbConnection = $this->table->getReadConnection();
        $dbRow        = $dbConnection->fetchOne(
            'SELECT * FROM co_invoices WHERE inv_id = :inv_id',
            ['inv_id' => $invoiceId]
        );

        /**
         * The ID will be returned as string
         */
        $dbRow['inv_id'] = (string)$dbRow['inv_id'];
        $expected        = $dbRow;
        $actual          = $newRow->getCopy();
        $this->assertSame($expected, $actual);

        /**
         * Silence PDO exception for this test
         */
        $connection = $this->table->getReadConnection();
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

        /**
         * Insert twice throws exception
         */
        $this->expectException(UnexpectedRowCountAffectedException::class);
        $this->expectExceptionMessage('Expected 1 row affected [actual: 0]');

        $actual = $this->table->insertRow($newRow);
        $this->assertInstanceOf(PDOStatement::class, $actual);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testInsertRowWithoutAutoinc(): void
    {
        $title = uniqid('tit-');
        $data  = [
            'inv_id'          => 10,
            'inv_cst_id'      => 10,
            'inv_status_flag' => 1,
            'inv_title'       => $title,
            'inv_total'       => 100.12,
            'inv_created_at'  => '2018-01-01 01:02:03',
        ];

        $newRow = $this->table->newRow($data);
        $actual = $this->table->insertRow($newRow);
        $this->assertInstanceOf(PDOStatement::class, $actual);

        $expected  = '10';
        $invoiceId = $newRow->get('inv_id');
        $this->assertSame($expected, $invoiceId);

        $data = [
            'inv_cst_id'      => 20,
            'inv_status_flag' => 2,
            'inv_title'       => $title,
            'inv_total'       => 200.22,
            'inv_created_at'  => '2018-01-01 01:02:03',
        ];

        $newRow = $this->table->newRow($data);
        $actual = $this->table->insertRow($newRow);
        $this->assertInstanceOf(PDOStatement::class, $actual);

        $invoiceId = $newRow->get('inv_id');
        $this->assertGreaterThan(10, $invoiceId);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testUpdateRow(): void
    {
        $title = uniqid('tit-');
        $data  = [
            'inv_id'          => 10,
            'inv_cst_id'      => 10,
            'inv_status_flag' => 1,
            'inv_title'       => $title,
            'inv_total'       => 100.12,
            'inv_created_at'  => '2018-01-01 01:02:03',
        ];

        $newRow = $this->table->newRow($data);
        $actual = $this->table->insertRow($newRow);
        $this->assertInstanceOf(PDOStatement::class, $actual);

        $expected  = '10';
        $invoiceId = $newRow->get('inv_id');
        $this->assertSame($expected, $invoiceId);

        $newRow
            ->set('inv_cst_id', 20)
            ->set('inv_status_flag', 2)
            ->set('inv_title', $title . '-2')
            ->set('inv_total', 200.24)
            ->set('inv_created_at', '2018-01-01 01:02:23')
        ;

        $actual = $this->table->updateRow($newRow);
        $this->assertInstanceOf(PDOStatement::class, $actual);

        $dbConnection = $this->table->getReadConnection();
        $dbRow        = $dbConnection->fetchOne(
            'SELECT * FROM co_invoices WHERE inv_id = :inv_id',
            ['inv_id' => $invoiceId]
        );

        /**
         * Double update returns null
         */
        $actual = $this->table->updateRow($newRow);
        $this->assertNull($actual);

        /**
         * The ID will be returned as string
         */
        $dbRow['inv_id']          = (string)$dbRow['inv_id'];
        $dbRow['inv_cst_id']      = 20;
        $dbRow['inv_status_flag'] = 2;
        $dbRow['inv_title']       = $title . '-2';
        $dbRow['inv_total']       = 200.24;
        $dbRow['inv_created_at']  = '2018-01-01 01:02:23';

        $expected = $dbRow;
        $actual   = $newRow->getCopy();
        $this->assertSame($expected, $actual);

        /**
         * Delete the record using the connection
         */
        $this->table->getWriteConnection()->perform(
            'DELETE FROM co_invoices WHERE inv_id = ?',
            [$newRow->get('inv_id')]
        );

        /**
         * Update the record again
         */
        $this->expectException(UnexpectedRowCountAffectedException::class);
        $this->expectExceptionMessage('Expected 1 row affected [actual: 0]');
        $newRow->set('inv_cst_id', 30);
        $this->table->updateRow($newRow);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testUpdateRowNoPrimaryKey(): void
    {
        $table = $this->locator->get(NoPrimaryKeyTable::class);
        $name  = uniqid('nam-');
        $data = [
            'nokey_id'   => 1,
            'nokey_name' => $name,
        ];
        $newRow = $table->newRow($data);
        $table->insertRow($newRow);

        $this->expectException(NoPrimaryKeyException::class);
        $this->expectExceptionMessage(
            'Cannot update row on table [no_primary_key] without a primary key.'
        );
        $newRow->set('nokey_name', uniqid('nam-'));
        $table->updateRow($newRow);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testUpdateRowUpdatedPrimaryKeyThrowsException(): void
    {
        $this->expectException(PrimaryValueChangedException::class);
        $this->expectExceptionMessage(
            "Primary key value for [inv_id] changed from ['10'] to [20]."
        );
        $title = uniqid('tit-');
        $data  = [
            'inv_id'          => 10,
            'inv_cst_id'      => 10,
            'inv_status_flag' => 1,
            'inv_title'       => $title,
            'inv_total'       => 100.12,
            'inv_created_at'  => '2018-01-01 01:02:03',
        ];

        $newRow = $this->table->newRow($data);
        $actual = $this->table->insertRow($newRow);
        $this->assertInstanceOf(PDOStatement::class, $actual);

        $expected  = '10';
        $invoiceId = $newRow->get('inv_id');
        $this->assertSame($expected, $invoiceId);

        $newRow->set('inv_id', 20);

        $actual = $this->table->updateRow($newRow);
    }
}
