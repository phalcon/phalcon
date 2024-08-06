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

final class AsAliasTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Select :: asAlias()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQuerySelectAsAlias(): void
    {
        $connection = $this->getDataMapperConnection();
        $factory    = new QueryFactory();
        $select     = $factory->newSelect($connection);

        $select
            ->from('co_invoices')
            ->asAlias('inv')
        ;

        $expected = "(SELECT * FROM co_invoices) AS inv";
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);
    }
}
