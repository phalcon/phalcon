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

use BadMethodCallException;
use Phalcon\Tests\DatabaseTestCase;
use PDOStatement;
use Phalcon\DataMapper\Query\QueryFactory;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;

final class UnderscoreCallTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Select :: __call()
     *
     * @since  2020-01-20
     *
     * @group  pgsql
     * @group  mysql
     * @group  sqlite
     */
    public function testDmQuerySelectUnderscoreCall(): void
    {
        $connection = $this->getDataMapperConnection();
        $factory    = new QueryFactory();
        $select     = $factory->newSelect($connection);
        (new InvoicesMigration($connection));

        $select->from('co_invoices');
        $expected = [];
        $actual   = $select->fetchAll();
        $this->assertEquals($expected, $actual);
        $this->assertInstanceOf(PDOStatement::class, $select->perform());
    }

    /**
     * Database Tests Phalcon\DataMapper\Query\Select :: __call() - exception
     *
     * @since  2020-01-20
     *
     * @group  pgsql
     * @group  mysql
     * @group  sqlite
     */
    public function testDmQuerySelectUnderscoreCallException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Unknown method: [unknown]');

        $connection = $this->getDataMapperConnection();
        $factory    = new QueryFactory();
        $select     = $factory->newSelect($connection);

        $select->from('co_invoices');
        $select->unknown();
    }
}
