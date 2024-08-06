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
use Phalcon\Tests\DatabaseTestCase;

final class ColumnsTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Select :: columns()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQuerySelectColumns(): void
    {
        $connection = $this->getDataMapperConnection();
        $factory    = new QueryFactory();
        $select     = $factory->newSelect($connection);

        $actual = $select->hasColumns();
        $this->assertFalse($actual);

        $select
            ->columns(['inv_id', 'inv_cst_id', 'COUNT(inv_total)'])
            ->from('co_invoices')
        ;

        $expected = "SELECT inv_id, inv_cst_id, COUNT(inv_total) "
            . "FROM co_invoices";
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);

        $select->reset();

        $select
            ->columns(
                [
                    'id'         => 'inv_id',
                    'customerId' => 'inv_cst_id',
                    'total'      => 'COUNT(inv_total)',
                ]
            )
            ->from('co_invoices')
        ;

        $expected = 'SELECT '
            . 'inv_id AS id, '
            . 'inv_cst_id AS customerId, '
            . 'COUNT(inv_total) AS total '
            . 'FROM co_invoices';
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);
    }
}
