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

final class FromTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Select :: from()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQuerySelectFrom(): void
    {
        $connection = $this->getDataMapperConnection();
        $factory    = new QueryFactory();
        $select     = $factory->newSelect($connection);

        $select
            ->from('co_invoices')
            ->from('co_customers')
        ;

        $expected = "SELECT * FROM co_invoices, co_customers";
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Query\Select :: from() - empty
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQuerySelectFromEmpty(): void
    {
        $connection = $this->getDataMapperConnection();
        $factory    = new QueryFactory();
        $select     = $factory->newSelect($connection);


        $expected = "SELECT *";
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);
    }
}
