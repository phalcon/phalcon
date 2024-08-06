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

final class UnionTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Select :: union()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQuerySelectUnion(): void
    {
        $connection = self::getDataMapperConnection();
        $factory    = new QueryFactory();
        $select     = $factory->newSelect($connection);

        $select
            ->from('co_invoices')
            ->where('inv_id = 1')
            ->union()
            ->from('co_invoices')
            ->where('inv_id = 2')
            ->union()
            ->from('co_invoices')
            ->where('inv_id = 3')
        ;

        $expected = "SELECT * FROM co_invoices WHERE inv_id = 1 UNION "
            . "SELECT * FROM co_invoices WHERE inv_id = 2 UNION "
            . "SELECT * FROM co_invoices WHERE inv_id = 3";
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);
    }
}
