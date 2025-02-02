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
use Phalcon\Tests\AbstractStatementTestCase;

use function env;

final class SubSelectTest extends AbstractStatementTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: subSelect()
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementSelectSubSelect(): void
    {
        $driver = env('driver');
        $select = Select::new($driver);

        $select
            ->from(
                $select
                    ->subSelect()
                    ->columns(['inv_id'])
                    ->from('co_invoices')
                    ->asAlias('inv')
                    ->getStatement()
            )
        ;

        $expected = 'SELECT * FROM (SELECT inv_id FROM co_invoices) AS inv';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: subSelect() object
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementSelectSubSelectObject(): void
    {
        $driver = env('driver');
        $select = Select::new($driver);

        $select
            ->where('inv_total > ', 100)
            ->from(
                $select
                    ->subSelect()
                    ->columns(['inv_id'])
                    ->from('co_invoices')
                    ->asAlias('inv')
            )
        ;

        $expected = 'SELECT * '
            . 'FROM (SELECT inv_id FROM co_invoices) AS inv '
            . 'WHERE inv_total > :_1_1_';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);
    }
}
