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
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\DataMapper\Table\AbstractTable;
use Phalcon\DataMapper\Table\Exception\NoPrimaryKeyException;
use Phalcon\DataMapper\Table\Exception\PrimaryValueChangedException;
use Phalcon\DataMapper\Table\Exception\PrimaryValueMissingException;
use Phalcon\DataMapper\Table\Exception\PrimaryValueNotScalarException;
use Phalcon\DataMapper\Table\Exception\UnexpectedRowCountAffectedException;
use Phalcon\DataMapper\Table\TableLocator;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Fixtures\DataMapper\Table\Invoices\InvoicesRow;
use Phalcon\Tests\Fixtures\DataMapper\Table\InvoicesWithEvents\InvoicesWithEventsTable;
use Phalcon\Tests\Fixtures\DataMapper\Table\NoPrimaryKey\NoPrimaryKeyTable;
use Phalcon\Tests\Fixtures\DataMapper\Table\OrdersXProducts\OrdersXProductsRow;
use Phalcon\Tests\Fixtures\DataMapper\Table\OrdersXProducts\OrdersXProductsTable;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;
use Phalcon\Tests\Fixtures\Migrations\OrdersProductsMigration;
use Phalcon\Tests\Fixtures\Storage;

use function array_keys;
use function uniqid;

final class TableEventsTest extends AbstractDatabaseTestCase
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
     * @var InvoicesWithEventsTable|AbstractTable
     */
    private InvoicesWithEventsTable $table;

    public function setUp(): void
    {
        parent::setUp();

        $connection = self::getConnection();

        /**
         * This is here to clear the table
         */
        (new InvoicesMigration($connection));
        $this->locator    = TableLocator::new($connection);
        $this->table      = $this->locator->get(InvoicesWithEventsTable::class);
        $this->connection = $connection;
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testDeleteRow(): void
    {
        Storage::reset();
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

        $this->table->deleteRow($newRow);
        $expected = [
            'beforeInsertRow',
            'modifyInsert',
            'modifyInsertRow',
            'afterInsertRow',
            'beforeDeleteRow',
            'modifyDelete',
            'modifyDeleteRow',
            'afterDeleteRow',
        ];
        $messages = Storage::getAll();
        $messages = Storage::getAll();
        $actual   = array_keys($messages);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testFetchRow(): void
    {
        Storage::reset();
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

        $title   = uniqid('tit-');
        $rowData = [
            'inv_id'          => 10,
            'inv_cst_id'      => 10,
            'inv_status_flag' => 1,
            'inv_title'       => $title,
            'inv_total'       => 100.12,
            'inv_created_at'  => '2018-01-01 01:02:03',
        ];

        $newRow = $this->table->newRow($rowData);
        $actual = $this->table->insertRow($newRow);
        $this->assertInstanceOf(PDOStatement::class, $actual);

        $row = $this->table->fetchRow(4);
        $this->assertNull($row);

        $expected = [
            'beforeInsertRow',
            'modifyInsert',
            'modifyInsertRow',
            'afterInsertRow',
            'modifySelect',
        ];
        $messages = Storage::getAll();
        $actual   = array_keys($messages);
        $this->assertSame($expected, $actual);

        Storage::reset();
        $row = $this->table->fetchRow(1);
        $this->assertInstanceOf(InvoicesRow::class, $row);

        $expected = [
            'modifySelect',
            'modifySelectedRow',
        ];
        $messages = Storage::getAll();
        $actual   = array_keys($messages);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testFetchRows(): void
    {
        Storage::reset();
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

        $rows = $this->table->fetchRows([1, 2, 3]);
        $actual = $rows;
        $this->assertIsArray($actual);
        $this->assertCount(3, $actual);

        $expected = [
            'modifySelect',
            'modifySelectedRow',
        ];
        $messages = Storage::getAll();
        $actual   = array_keys($messages);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testInsertRow(): void
    {
        Storage::reset();
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

        $expected = [
            'beforeInsertRow',
            'modifyInsert',
            'modifyInsertRow',
            'afterInsertRow',
        ];
        $messages = Storage::getAll();
        $actual   = array_keys($messages);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testUpdateRow(): void
    {
        Storage::reset();
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

        $expected = [
            'beforeInsertRow',
            'modifyInsert',
            'modifyInsertRow',
            'afterInsertRow',
            'beforeUpdateRow',
            'modifyUpdate',
            'modifyUpdateRow',
            'afterUpdateRow',
        ];
        $messages = Storage::getAll();
        $actual   = array_keys($messages);
        $this->assertSame($expected, $actual);
    }
}
