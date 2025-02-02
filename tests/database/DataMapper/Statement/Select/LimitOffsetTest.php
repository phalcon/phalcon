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

final class LimitOffsetTest extends AbstractStatementTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: limit()/offset()
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementSelectLimitOffset(): void
    {
        $driver = env('driver');
        $select = Select::new($driver);

        $select
            ->from('co_invoices')
            ->limit(10)
        ;

        $expected = 'SELECT * FROM co_invoices LIMIT 10';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);

        $select->offset(50);

        $expected = 'SELECT * FROM co_invoices LIMIT 10 OFFSET 50';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: limit()/offset() -
     * MSSSQL
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementSelectLimitOffsetMssql(): void
    {
        $select = Select::new('sqlsrv');

        $select
            ->from('co_invoices')
            ->limit(10)
        ;

        $expected = 'SELECT TOP 10 * FROM co_invoices';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);

        $select->offset(50);

        $expected = 'SELECT * FROM co_invoices '
            . 'OFFSET 50 ROWS FETCH NEXT 10 ROWS ONLY';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: page()/perPage()
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementSelectPage(): void
    {
        $driver = env('driver');
        $select = Select::new($driver);

        $select
            ->from('co_invoices')
            ->page(7)
        ;

        $expected = 'SELECT * FROM co_invoices LIMIT 10 OFFSET 60';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);

        $select->perPage(50);

        $expected = 'SELECT * FROM co_invoices LIMIT 50 OFFSET 300';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);

        $select->resetLimit();

        $select
            ->page(2)
            ->limit(5)
        ;

        $expected = 'SELECT * FROM co_invoices LIMIT 5';
        $actual   = $select->getStatement();

        $this->assertSame($expected, $actual);
        $select->resetLimit();

        $select
            ->page(2)
            ->offset(10)
        ;

        $expected = 'SELECT * FROM co_invoices OFFSET 10';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: page()/perPage()
     * calculations
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementSelectPageCalculations(): void
    {
        $driver = env('driver');
        $select = Select::new($driver);

        $select
            ->from('co_invoices')
        ;

        $expected = 'SELECT * FROM co_invoices';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);

        $select->page(3);

        $expected = 'SELECT * FROM co_invoices LIMIT 10 OFFSET 20';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);

        $select->limit(10);

        $expected = 'SELECT * FROM co_invoices LIMIT 10';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);

        $select
            ->page(3)
            ->perPage(50)
        ;

        $expected = 'SELECT * FROM co_invoices LIMIT 50 OFFSET 100';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);

        $select->offset(10);

        $expected = 'SELECT * FROM co_invoices OFFSET 10';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);
    }
}
