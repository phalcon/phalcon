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

use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\DataMapper\Query\QueryFactory;
use Phalcon\Tests\AbstractDatabaseTestCase;

final class LimitOffsetTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Select :: limit()/offset()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQuerySelectLimitOffset(): void
    {
        $connection = self::getDataMapperConnection();
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
     * @group  common
     */
    public function testDmQuerySelectLimitOffsetMssql(): void
    {
        $mock = $this
            ->getMockBuilder(Connection::class)
            ->setConstructorArgs(
                [
                    self::getDatabaseDsn(),
                    self::getDatabaseUsername(),
                    self::getDatabasePassword()
                ]
            )
            ->getMock();

        $mock->method('getDriverName')->willReturn('sqlsrv');
        $factory        = new QueryFactory();
        $select = $factory->newSelect($mock);

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
