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

use PDO;
use Phalcon\DataMapper\Statement\Select;
use Phalcon\Tests\AbstractStatementTestCase;

use function env;

final class JoinTest extends AbstractStatementTestCase
{
    /**
     * @return array[]
     */
    public static function getJoinNames(): array
    {
        return [
            ['INNER'],
            ['LEFT'],
            ['NATURAL'],
            ['RIGHT'],
        ];
    }

    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: join() - inner
     *
     * @since        2020-01-20
     *
     * @dataProvider getJoinNames
     * @group mysql
     */
    public function testDmStatementSelectJoin(string $join): void
    {
        $driver = env('driver');
        $select = Select::new($driver);

        $select
            ->from('co_invoices')
            ->join($join, 'co_customers', 'inv_cst_id = cst_id')
        ;


        $expected = 'SELECT * FROM co_invoices '
            . $join . ' JOIN co_customers ON inv_cst_id = cst_id';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: join() - subselect
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementSelectJoinSubSelect(): void
    {
        $driver = env('driver');
        $select = Select::new($driver);

        $sub = $select
            ->subSelect()
            ->from('co_customers')
            ->where('cst_status_flag = ', 1)
            ->asAlias('cst')
        ;

        $select
            ->from('co_invoices')
            ->join(
                $select::JOIN_LEFT,
                $sub,
                'inv_cst_id = cst_id'
            )
            ->appendJoin(' AND cst_name LIKE ', '%john%')
        ;

        $expected = 'SELECT * FROM co_invoices '
            . 'LEFT JOIN (SELECT * FROM co_customers '
            . 'WHERE cst_status_flag = :_2_1_) AS cst '
            . 'ON inv_cst_id = cst_id AND cst_name LIKE :_1_1_';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);

        $expected = [
            '_2_1_' => [1, PDO::PARAM_INT],
            '_1_1_' => ['%john%', PDO::PARAM_STR],
        ];
        $actual   = $select->getBindValues();
        $this->assertSame($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: join() - with bind
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementSelectJoinWithBind(): void
    {
        $driver = env('driver');
        $select = Select::new($driver);

        $select
            ->from('co_invoices')
            ->join(
                $select::JOIN_LEFT,
                'co_customers',
                'inv_cst_id = cst_id AND cst_status_flag = ',
                1
            )
            ->appendJoin(' AND cst_name LIKE ', '%john%')
        ;

        $expected = 'SELECT * FROM co_invoices '
            . 'LEFT JOIN co_customers ON inv_cst_id = cst_id '
            . 'AND cst_status_flag = :_1_1_ AND cst_name LIKE :_1_2_';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);

        $expected = [
            '_1_1_' => [1, PDO::PARAM_INT],
            '_1_2_' => ['%john%', PDO::PARAM_STR],
        ];
        $actual   = $select->getBindValues();
        $this->assertSame($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: join() - with using
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementSelectJoinWithUsing(): void
    {
        $driver = env('driver');
        $select = Select::new($driver);

        $select
            ->from('co_invoices')
            ->join(
                $select::JOIN_LEFT,
                'co_customers',
                'USING (inv_id)'
            )
        ;

        $expected = 'SELECT * FROM co_invoices '
            . 'LEFT JOIN co_customers USING (inv_id)';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);
    }
}
