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

final class DistinctTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Select :: distinct()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQuerySelectDistinct(): void
    {
        $connection = self::getDataMapperConnection();
        $factory    = new QueryFactory();
        $select     = $factory->newSelect($connection);

        $select
            ->distinct()
            ->from('co_invoices')
            ->columns(['inv_id', 'inc_cst_id'])
        ;

        $expected = "SELECT DISTINCT inv_id, inc_cst_id FROM co_invoices";
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Query\Select :: distinct() - twice
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQuerySelectDistinctTwice(): void
    {
        $connection = self::getDataMapperConnection();
        $factory    = new QueryFactory();
        $select     = $factory->newSelect($connection);

        $select
            ->distinct()
            ->distinct()
            ->from('co_invoices')
            ->columns(['inv_id', 'inc_cst_id'])
        ;

        $expected = "SELECT DISTINCT inv_id, inc_cst_id FROM co_invoices";
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Query\Select :: distinct() - unset
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQuerySelectDistinctUnset(): void
    {
        $connection = self::getDataMapperConnection();
        $factory    = new QueryFactory();
        $select     = $factory->newSelect($connection);

        $select
            ->distinct()
            ->distinct(false)
            ->from('co_invoices')
            ->columns(['inv_id', 'inc_cst_id'])
        ;

        $expected = "SELECT inv_id, inc_cst_id FROM co_invoices";
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);
    }
}
