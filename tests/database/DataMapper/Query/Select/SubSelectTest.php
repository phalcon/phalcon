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

final class SubSelectTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Select :: subSelect()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQuerySelectSubSelect(): void
    {
        $connection = self::getDataMapperConnection();
        $factory    = new QueryFactory();
        $select     = $factory->newSelect($connection);

        $select
            ->from(
                $select
                    ->subSelect()
                    ->columns(["inv_id"])
                    ->from('co_invoices')
                    ->asAlias('inv')
                    ->getStatement()
            )
        ;

        $expected = "SELECT * FROM (SELECT inv_id FROM co_invoices) AS inv";
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);
    }
}
