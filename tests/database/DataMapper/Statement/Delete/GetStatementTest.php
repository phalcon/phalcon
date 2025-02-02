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

namespace Phalcon\Tests\Database\DataMapper\Statement\Delete;

use Phalcon\DataMapper\Statement\Delete;
use Phalcon\Tests\AbstractDatabaseTestCase;

use function env;

final class GetStatementTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Statement\Delete :: getStatement()
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementDeleteGetStatement(): void
    {
        $driver = env('driver');
        $delete = Delete::new($driver);

        $delete
            ->table('co_invoices')
            ->where('inv_total > :total')
            ->where('inv_cst_id = :cstId')
            ->orWhere('inv_status_flag = :flag')
            ->returning(['inv_total', 'inv_cst_id', 'inv_status_flag'])
            ->bindValues(
                [
                    'total' => 100,
                    'cstId' => 4,
                    'flag'  => 'active',
                ]
            )
        ;

        $expected = 'DELETE FROM co_invoices '
            . 'WHERE inv_total > :total '
            . 'AND inv_cst_id = :cstId '
            . 'OR inv_status_flag = :flag '
            . 'RETURNING inv_total, inv_cst_id, inv_status_flag';
        $actual   = $delete->getStatement();
        $this->assertSame($expected, $actual);

        $delete->resetReturning();

        $expected = 'DELETE FROM co_invoices '
            . 'WHERE inv_total > :total '
            . 'AND inv_cst_id = :cstId '
            . 'OR inv_status_flag = :flag';
        $actual   = $delete->getStatement();
        $this->assertSame($expected, $actual);
    }
}
