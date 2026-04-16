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

final class ResetTest extends AbstractStatementTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: reset()
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementReset(): void
    {
        $driver = env('driver');
        $select = Select::new($driver);

        /**
         * The query does not make sense but it is just to test the method
         */
        $select
            ->with('cte1', 'SELECT * FROM co_customers')
            ->columns(['inv_id', 'inv_cst_id', 'COUNT(inv_total)'])
            ->from('co_invoices')
            ->having('inv_total = :total')
            ->bindValue('total', 100)
            ->appendWhere('inv_total > ', 100)
            ->limit(10)
            ->offset(50)
            ->groupBy('inv_status_flag')
            ->orderBy(['inv_cst_id'])
            ->setFlag('LOW_PRIORITY')
        ;

        $expected = 'WITH ' . $select->quote($driver, 'cte1') . ' AS (SELECT * FROM co_customers) '
            . 'SELECT '
            . 'LOW_PRIORITY '
            . 'inv_id, inv_cst_id, COUNT(inv_total) '
            . 'FROM co_invoices '
            . 'WHERE inv_total > :_1_1_ '
            . 'GROUP BY inv_status_flag '
            . 'HAVING inv_total = :total '
            . 'ORDER BY inv_cst_id '
            . 'LIMIT 10 '
            . 'OFFSET 50';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);

        /**
         * resetWith
         */
        $select->resetWith();

        $expected = 'SELECT '
            . 'LOW_PRIORITY '
            . 'inv_id, inv_cst_id, COUNT(inv_total) '
            . 'FROM co_invoices '
            . 'WHERE inv_total > :_1_1_ '
            . 'GROUP BY inv_status_flag '
            . 'HAVING inv_total = :total '
            . 'ORDER BY inv_cst_id '
            . 'LIMIT 10 '
            . 'OFFSET 50';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);


        /**
         * resetColumns()
         */
        $actual = $select->hasColumns();
        $this->assertTrue($actual);

        $select->resetColumns();

        $actual = $select->hasColumns();
        $this->assertFalse($actual);

        $expected = 'SELECT '
            . 'LOW_PRIORITY '
            . '* '
            . 'FROM co_invoices '
            . 'WHERE inv_total > :_1_1_ '
            . 'GROUP BY inv_status_flag '
            . 'HAVING inv_total = :total '
            . 'ORDER BY inv_cst_id '
            . 'LIMIT 10 '
            . 'OFFSET 50';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);

        /**
         * resetFlags()
         */
        $select->resetFlags();

        $expected = 'SELECT '
            . '* '
            . 'FROM co_invoices '
            . 'WHERE inv_total > :_1_1_ '
            . 'GROUP BY inv_status_flag '
            . 'HAVING inv_total = :total '
            . 'ORDER BY inv_cst_id '
            . 'LIMIT 10 '
            . 'OFFSET 50';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);

        /**
         * resetFrom()
         */
        $select->resetFrom();

        $expected = 'SELECT '
            . '* '
            . 'WHERE inv_total > :_1_1_ '
            . 'GROUP BY inv_status_flag '
            . 'HAVING inv_total = :total '
            . 'ORDER BY inv_cst_id '
            . 'LIMIT 10 '
            . 'OFFSET 50';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);

        /**
         * resetGroupBy()
         */
        $select->resetGroupBy();

        $expected = 'SELECT '
            . '* '
            . 'WHERE inv_total > :_1_1_ '
            . 'HAVING inv_total = :total '
            . 'ORDER BY inv_cst_id '
            . 'LIMIT 10 '
            . 'OFFSET 50';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);

        /**
         * resetHaving()
         */
        $select->resetHaving();

        $expected = 'SELECT '
            . '* '
            . 'WHERE inv_total > :_1_1_ '
            . 'ORDER BY inv_cst_id '
            . 'LIMIT 10 '
            . 'OFFSET 50';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);

        /**
         * resetLimit()
         */
        $select->resetLimit();

        $expected = 'SELECT '
            . '* '
            . 'WHERE inv_total > :_1_1_ '
            . 'ORDER BY inv_cst_id';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);

        /**
         * resetOrderBy()
         */
        $select->resetOrderBy();

        $expected = 'SELECT '
            . '* '
            . 'WHERE inv_total > :_1_1_';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);

        /**
         * resetWhere()
         */
        $select->resetWhere();

        $expected = 'SELECT '
            . '*';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);

        /**
         * reset()
         */
        $select
            ->columns(['inv_id', 'inv_cst_id', 'COUNT(inv_total)'])
            ->from('co_invoices')
            ->having('inv_total = :total')
            ->bindValue('total', 100)
            ->appendWhere('inv_total > ', 100)
            ->limit(10)
            ->offset(50)
            ->groupBy('inv_status_flag')
            ->orderBy(['inv_cst_id'])
            ->setFlag('LOW_PRIORITY')
        ;

        $select->reset();

        $expected = 'SELECT *';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);
    }
}
