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

final class ForUpdateTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Select :: forUpdate()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQuerySelectForUpdate(): void
    {
        $connection = self::getDataMapperConnection();
        $factory    = new QueryFactory();
        $select     = $factory->newSelect($connection);

        $select
            ->from('co_invoices')
            ->forUpdate()
        ;

        $expected = "SELECT * FROM co_invoices FOR UPDATE";
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Query\Select :: forUpdate() - unset
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQuerySelectForUpdateUnset(): void
    {
        $connection = self::getDataMapperConnection();
        $factory    = new QueryFactory();
        $select     = $factory->newSelect($connection);

        $select
            ->from('co_invoices')
            ->forUpdate()
            ->forUpdate(false)
        ;

        $expected = "SELECT * FROM co_invoices";
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);
    }
}
