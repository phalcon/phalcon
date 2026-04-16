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

namespace Phalcon\Tests\Database\DataMapper\Statement\Select;

use Phalcon\DataMapper\Statement\Select;
use Phalcon\Tests\AbstractStatementTestCase;

use function env;

final class WhereTest extends AbstractStatementTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: orWhere() - bind values
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementSelectOrWhereBind(): void
    {
        $driver = env('driver');
        $select = Select::new($driver);

        $select
            ->from('co_invoices')
            ->appendWhere('inv_total > ', 100)
            ->orWhere('inv_status_flag = :status')
            ->bindValue('status', 1)
        ;

        $expected = 'SELECT * FROM co_invoices '
            . 'WHERE inv_total > :_1_1_ '
            . 'OR inv_status_flag = :status';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);

        $expected = [
            '_1_1_'  => [100, 1],
            'status' => [1, 1],
        ];
        $actual   = $select->getBindValues();
        $this->assertSame($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: where()
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementSelectWhere(): void
    {
        $driver = env('driver');
        $select = Select::new($driver);

        $select
            ->from('co_invoices')
            ->where('inv_id > ', 1)
        ;

        $expected = 'SELECT * FROM co_invoices WHERE inv_id > :_1_1_';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);

        $expected = [
            '_1_1_' => [1, 1],
        ];
        $actual   = $select->getBindValues();
        $this->assertSame($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: where() - bind values
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementSelectWhereBind(): void
    {
        $driver = env('driver');
        $select = Select::new($driver);

        $select
            ->from('co_invoices')
            ->where('inv_id > 1')
            ->andWhere('inv_total > :total')
            ->andWhere('inv_cst_id IN ', [1, 2, 3])
            ->appendWhere(' AND inv_status_flag = ' . $select->bindInline(1))
            ->bindValue('total', 100)
        ;

        $expected = 'SELECT * FROM co_invoices '
            . 'WHERE inv_id > 1 AND inv_total > :total '
            . 'AND inv_cst_id IN (:_1_1_, :_1_2_, :_1_3_) '
            . 'AND inv_status_flag = :_1_4_';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);

        $expected = [
            '_1_1_' => [1, 1],
            '_1_2_' => [2, 1],
            '_1_3_' => [3, 1],
            '_1_4_' => [1, 1],
            'total' => [100, 1],
        ];
        $actual   = $select->getBindValues();
        $this->assertSame($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: where() subselect
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementSelectWhereSubSelect(): void
    {
        $driver = env('driver');
        $select = Select::new($driver);

        $select
            ->from('co_invoices')
            ->where(
                'inv_cst_id IN ',
                $select
                    ->subSelect()
                    ->columns(['cst_id'])
                    ->from('co_customers')
                    ->where('cst_status_flag = ', 1)
            )
        ;

        $expected = 'SELECT * '
            . 'FROM co_invoices '
            . 'WHERE inv_cst_id IN ('
            . 'SELECT cst_id '
            . 'FROM co_customers '
            . 'WHERE cst_status_flag = :_2_1_'
            . ')';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);
    }
}
