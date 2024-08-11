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

namespace Phalcon\Tests\Database\DataMapper\Query\Select;

use Phalcon\DataMapper\Query\QueryFactory;
use Phalcon\Tests\AbstractDatabaseTestCase;

final class ResetTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Select :: reset()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQueryReset(): void
    {
        $connection = self::getDataMapperConnection();
        $factory    = new QueryFactory();
        $select     = $factory->newSelect($connection);

        /**
         * The query does not make sense but it is just to test the method
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
            ->setFlag("LOW_PRIORITY")
        ;

        $expected = 'SELECT '
            . 'LOW_PRIORITY '
            . 'inv_id, inv_cst_id, COUNT(inv_total) '
            . 'FROM co_invoices '
            . 'WHERE inv_total > :__1__ '
            . 'GROUP BY inv_status_flag '
            . 'HAVING inv_total = :total '
            . 'ORDER BY inv_cst_id '
            . 'LIMIT 10 '
            . 'OFFSET 50';
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);

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
            . 'WHERE inv_total > :__1__ '
            . 'GROUP BY inv_status_flag '
            . 'HAVING inv_total = :total '
            . 'ORDER BY inv_cst_id '
            . 'LIMIT 10 '
            . 'OFFSET 50';
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);

        /**
         * resetFlags()
         */
        $select->resetFlags();

        $expected = 'SELECT '
            . '* '
            . 'FROM co_invoices '
            . 'WHERE inv_total > :__1__ '
            . 'GROUP BY inv_status_flag '
            . 'HAVING inv_total = :total '
            . 'ORDER BY inv_cst_id '
            . 'LIMIT 10 '
            . 'OFFSET 50';
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);

        /**
         * resetFrom()
         */
        $select->resetFrom();

        $expected = 'SELECT '
            . '* '
            . 'WHERE inv_total > :__1__ '
            . 'GROUP BY inv_status_flag '
            . 'HAVING inv_total = :total '
            . 'ORDER BY inv_cst_id '
            . 'LIMIT 10 '
            . 'OFFSET 50';
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);

        /**
         * resetGroupBy()
         */
        $select->resetGroupBy();

        $expected = 'SELECT '
            . '* '
            . 'WHERE inv_total > :__1__ '
            . 'HAVING inv_total = :total '
            . 'ORDER BY inv_cst_id '
            . 'LIMIT 10 '
            . 'OFFSET 50';
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);

        /**
         * resetHaving()
         */
        $select->resetHaving();

        $expected = 'SELECT '
            . '* '
            . 'WHERE inv_total > :__1__ '
            . 'ORDER BY inv_cst_id '
            . 'LIMIT 10 '
            . 'OFFSET 50';
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);

        /**
         * resetLimit()
         */
        $select->resetLimit();

        $expected = 'SELECT '
            . '* '
            . 'WHERE inv_total > :__1__ '
            . 'ORDER BY inv_cst_id';
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);

        /**
         * resetOrderBy()
         */
        $select->resetOrderBy();

        $expected = 'SELECT '
            . '* '
            . 'WHERE inv_total > :__1__';
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);

        /**
         * resetWhere()
         */
        $select->resetWhere();

        $expected = 'SELECT '
            . '*';
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);

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
            ->setFlag("LOW_PRIORITY")
        ;

        $select->reset();

        $expected = 'SELECT *';
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);
    }
}
