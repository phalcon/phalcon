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

final class GroupByTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Select :: groupBy()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQuerySelectGroupBy(): void
    {
        $connection = self::getDataMapperConnection();
        $factory    = new QueryFactory();
        $select     = $factory->newSelect($connection);

        $select
            ->from('co_invoices')
            ->groupBy('inv_cst_id')
            ->groupBy('inv_status_flag')
        ;

        $expected = "SELECT * FROM co_invoices "
            . "GROUP BY inv_cst_id, inv_status_flag";
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);
    }
}
