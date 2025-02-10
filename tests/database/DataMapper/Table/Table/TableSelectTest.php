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

use Phalcon\DataMapper\Table\AbstractTableSelect;
use Phalcon\DataMapper\Table\TableLocator;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Fixtures\DataMapper\Table\Invoices\InvoicesRow;
use Phalcon\Tests\Fixtures\DataMapper\Table\Invoices\InvoicesTable;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;

use function uniqid;

final class TableSelectTest extends AbstractDatabaseTestCase
{
    /**
     * @var array
     */
    private array $data = [];

    /**
     * @var AbstractTableSelect
     */
    private AbstractTableSelect $select;

    public function setUp(): void
    {
        parent::setUp();

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

        $connection = self::getConnection();
        $migration  = new InvoicesMigration($connection);
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

        $this->data   = $data;
        $table        = TableLocator::new($connection)->get(InvoicesTable::class);
        $this->select = $table->select();
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testFetchCount(): void
    {
        $actual = $this
            ->select
            ->limit(2)
            ->fetchRows()
        ;
        $this->assertCount(2, $actual);


        $actual = $this
            ->select
            ->where('inv_status_flag = 2')
            ->fetchCount()
        ;
        $this->assertSame(2, $actual);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testFetchRow(): void
    {
        $expected = $this->data[0];
        $actual   = $this
            ->select
            ->where('inv_id = ', 1)
            ->fetchRow()
        ;
        $this->assertInstanceOf(InvoicesRow::class, $actual);
        $this->assertSame($expected, $actual->getCopy());

        $actual = $this
            ->select
            ->where('inv_id = ', -1)
            ->fetchRow()
        ;
        $this->assertNull($actual);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testFetchRows(): void
    {
        $rows = $this
            ->select
            ->where('inv_id IN ', [1, 2, 3])
            ->fetchRows()
        ;

        $actual = $rows;
        $this->assertIsArray($actual);
        $this->assertCount(3, $actual);

        $this->assertInstanceOf(InvoicesRow::class, $rows[0]);
        $this->assertInstanceOf(InvoicesRow::class, $rows[1]);
        $this->assertInstanceOf(InvoicesRow::class, $rows[2]);

        $expected = $this->data[0];
        $actual   = $rows[0]->getCopy();
        $this->assertSame($expected, $actual);

        $expected = $this->data[1];
        $actual   = $rows[1]->getCopy();
        $this->assertSame($expected, $actual);

        $expected = $this->data[2];
        $actual   = $rows[2]->getCopy();
        $this->assertSame($expected, $actual);

        $actual = $this
            ->select
            ->where('inv_id = ', -1)
            ->fetchRows()
        ;
        $this->assertIsArray($actual);
        $this->assertEmpty($actual);
    }
}
