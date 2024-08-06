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

namespace Phalcon\Tests\Unit\DataMapper\Query\Select;

use Codeception\Stub;
use Phalcon\Tests\DatabaseTestCase;
use Phalcon\DataMapper\Query\QueryFactory;

final class LimitOffsetTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Select :: limit()/offset()
     *
     * @since  2020-01-20
     *
     * @group  pgsql
     * @group  mysql
     * @group  sqlite
     */
    public function testDmQuerySelectLimitOffset(): void
    {
        $connection = $this->getDataMapperConnection();
        $factory    = new QueryFactory();
        $select     = $factory->newSelect($connection);

        $select
            ->from('co_invoices')
            ->limit(10)
        ;

        $expected = "SELECT * FROM co_invoices LIMIT 10";
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);

        $select->offset(50);

        $expected = "SELECT * FROM co_invoices LIMIT 10 OFFSET 50";
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Query\Select :: limit()/offset() -
     * MSSSQL
     *
     * @since  2020-01-20
     *
     * @group  pgsql
     * @group  mysql
     * @group  sqlite
     */
    public function testDmQuerySelectLimitOffsetMssql(): void
    {
        $connection = $this->getDataMapperConnection();
        $mockConnection = Stub::make(
            $connection,
            [
                'getDriverName' => 'sqlsrv',
            ]
        );
        $factory = new QueryFactory();
        $select = $factory->newSelect($mockConnection);

        $select
            ->from('co_invoices')
            ->limit(10)
        ;

        $expected = "SELECT TOP 10 * FROM co_invoices";
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);

        $select->offset(50);

        $expected = "SELECT * FROM co_invoices "
            . "OFFSET 50 ROWS FETCH NEXT 10 ROWS ONLY";
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);
    }
}
