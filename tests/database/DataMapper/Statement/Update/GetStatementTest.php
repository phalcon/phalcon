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

namespace Phalcon\Tests\Database\DataMapper\Statement\Update;

use PDO;
use Phalcon\DataMapper\Statement\Update;
use Phalcon\Tests\AbstractDatabaseTestCase;

use function env;

final class GetStatementTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Update :: getStatement()
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementUpdateGetStatement(): void
    {
        $driver = env('driver');
        $update = Update::new($driver);

        $update
            ->table('co_invoices')
            ->columns(['inv_id', 'inv_cst_id', 'inv_total' => 'total'])
            ->set('inv_status_flag', null)
            ->set('inv_created_date', 'NOW()')
            ->where('inv_total > :totalMax')
            ->where('inv_cst_id = :cstId')
            ->orWhere('inv_status_flag = :flag')
            ->bindValues(
                [
                    'totalMax' => 100,
                    'cstId'    => 4,
                    'flag'     => 'active',
                ]
            )
        ;

        $expected = 'UPDATE co_invoices '
            . 'SET '
            . $update->quote($driver, 'inv_id') . ' = :inv_id, '
            . $update->quote($driver, 'inv_cst_id') . ' = :inv_cst_id, '
            . $update->quote($driver, 'inv_total') . ' = :inv_total, '
            . $update->quote($driver, 'inv_status_flag') . ' = NULL, '
            . $update->quote($driver, 'inv_created_date') . ' = NOW() '
            . 'WHERE inv_total > :totalMax '
            . 'AND inv_cst_id = :cstId '
            . 'OR inv_status_flag = :flag';

        $actual = $update->getStatement();
        $this->assertSame($expected, $actual);

        $expected = [
            'inv_total' => ['total', PDO::PARAM_STR],
            'totalMax'  => [100, PDO::PARAM_INT],
            'cstId'     => [4, PDO::PARAM_INT],
            'flag'      => ['active', PDO::PARAM_STR],
        ];
        $actual   = $update->getBindValues();
        $this->assertSame($expected, $actual);

        $update
            ->returning(['inv_id', 'inv_cst_id'])
            ->returning(['inv_total'])
        ;

        $expected = 'UPDATE co_invoices '
            . 'SET '
            . $update->quote($driver, 'inv_id') . ' = :inv_id, '
            . $update->quote($driver, 'inv_cst_id') . ' = :inv_cst_id, '
            . $update->quote($driver, 'inv_total') . ' = :inv_total, '
            . $update->quote($driver, 'inv_status_flag') . ' = NULL, '
            . $update->quote($driver, 'inv_created_date') . ' = NOW() '
            . 'WHERE inv_total > :totalMax '
            . 'AND inv_cst_id = :cstId '
            . 'OR inv_status_flag = :flag '
            . 'RETURNING inv_id, inv_cst_id, inv_total';
        $actual   = $update->getStatement();
        $this->assertSame($expected, $actual);
    }
}
