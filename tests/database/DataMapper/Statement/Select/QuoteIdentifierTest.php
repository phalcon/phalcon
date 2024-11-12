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

namespace Phalcon\Tests\Database\DataMapper\Statement\Select;

use Phalcon\DataMapper\Statement\Select;
use Phalcon\Tests\Database\DataMapper\Statement\AbstractStatementTestCase;

use function env;

final class QuoteIdentifierTest extends AbstractStatementTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: quoteIdentifier()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmStatementSelectQuoteIdentifier(): void
    {
        $select = Select::new('mysql');

        $source   = 'some field';
        $expected = $select->quote('mysql', $source);
        $actual   = $select->quoteIdentifier($source);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: quoteIdentifier()
     * MSSQL
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmStatementSelectQuoteIdentifierSqlsrv(): void
    {
        $select = Select::new('sqlsrv');

        $source   = 'some field';
        $expected = $select->quote('sqlsrv', $source);
        $actual   = $select->quoteIdentifier($source);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: quoteIdentifier()
     * Sqlite
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmStatementSelectQuoteIdentifierSqlite(): void
    {
        $select = Select::new('sqlite');

        $source   = 'some field';
        $expected = $select->quote('sqlite', $source);
        $actual   = $select->quoteIdentifier($source);
        $this->assertEquals($expected, $actual);
    }
}
