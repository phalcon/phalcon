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

use PDO;
use Phalcon\DataMapper\Query\QueryFactory;
use Phalcon\Tests\AbstractDatabaseTestCase;

final class HavingTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Select :: having()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQuerySelectHaving(): void
    {
        $connection = self::getDataMapperConnection();
        $factory    = new QueryFactory();
        $select     = $factory->newSelect($connection);

        $select
            ->from('co_invoices')
            ->having('inv_total = :total')
            ->bindValue('total', 100)
        ;


        $expected = "SELECT * FROM co_invoices HAVING inv_total = :total";
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);

        $expected = [
            'total' => [100, PDO::PARAM_INT],
        ];
        $actual   = $select->getBindValues();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Query\Select :: having() - complex
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQuerySelectHavingComplex(): void
    {
        $connection = self::getDataMapperConnection();
        $factory    = new QueryFactory();
        $select     = $factory->newSelect($connection);

        $select
            ->from('co_invoices')
            ->having('inv_total = :total')
            ->andHaving('inv_cst_id = 1')
            ->orHaving('(inv_status_flag = 0 ')
            ->appendHaving('OR inv_status_flag = 1)')
            ->bindValue('total', 100)
        ;


        $expected = "SELECT * FROM co_invoices "
            . "HAVING inv_total = :total AND "
            . "inv_cst_id = 1 OR "
            . "(inv_status_flag = 0 OR inv_status_flag = 1)";
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);

        $expected = [
            'total' => [100, PDO::PARAM_INT],
        ];
        $actual   = $select->getBindValues();
        $this->assertEquals($expected, $actual);
    }
}
