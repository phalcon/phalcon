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

use Phalcon\Tests\DatabaseTestCase;
use Phalcon\DataMapper\Query\QueryFactory;

final class UnionAllTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Select :: unionAll()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQuerySelectUnionAll(): void
    {
        $connection = $this->getDataMapperConnection();
        $factory    = new QueryFactory();
        $select     = $factory->newSelect($connection);

        $select
            ->from('co_invoices')
            ->where('inv_id = 1')
            ->unionAll()
            ->from('co_invoices')
            ->where('inv_id = 2')
            ->unionAll()
            ->from('co_invoices')
            ->where('inv_id = 3')
        ;

        $expected = "SELECT * FROM co_invoices WHERE inv_id = 1 UNION ALL "
            . "SELECT * FROM co_invoices WHERE inv_id = 2 UNION ALL "
            . "SELECT * FROM co_invoices WHERE inv_id = 3";
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);
    }
}
