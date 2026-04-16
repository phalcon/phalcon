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

final class WithTest extends AbstractStatementTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: with() - bind values
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementSelectOrWhereBind(): void
    {
        $driver = env('driver');
        $select = Select::new($driver);

        $select
            ->with('cte1', 'SELECT cst_id FROM co_customers WHERE cst_status_flag = 1')
            ->withColumns('cte2', ['cst_id'], 'SELECT * FROM co_customers WHERE cst_status_flag = -1')
            ->from('cte1')
            ->union()
            ->from('cte2')
        ;

        $expected = 'WITH `cte1` AS '
            . '(SELECT cst_id '
            . 'FROM co_customers '
            . 'WHERE cst_status_flag = 1), '
            . '`cte2` (`cst_id`) AS '
            . '(SELECT * '
            . 'FROM co_customers '
            . 'WHERE cst_status_flag = -1) '
            . 'SELECT * FROM cte1 '
            . 'UNION '
            . 'SELECT * FROM cte2';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: with() object
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementSelectWithObject(): void
    {
        $driver = env('driver');
        $select = Select::new($driver);

        $cte1 = Select::new($driver);
        $cte1
            ->columns(['cst_id'])
            ->from('co_customers')
            ->where('cst_status_flag = ', 1)
        ;

        $cte2 = Select::new($driver);
        $cte2
            ->from('co_customers')
            ->where('cst_status_flag = ', -1)
        ;

        $select
            ->with('cte1', $cte1)
            ->withColumns('cte2', ['cst_id'], $cte2)
            ->from('cte1')
            ->union()
            ->from('cte2')
        ;

        $expected = 'WITH `cte1` AS '
            . '(SELECT cst_id '
            . 'FROM co_customers '
            . 'WHERE cst_status_flag = :_2_1_), '
            . '`cte2` (`cst_id`) AS '
            . '(SELECT * '
            . 'FROM co_customers '
            . 'WHERE cst_status_flag = :_3_1_) '
            . 'SELECT * '
            . 'FROM cte1 '
            . 'UNION SELECT * '
            . 'FROM cte2';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);

        $expected = [
            '_2_1_' => [1, 1],
            '_3_1_' => [-1, 1],
        ];
        $actual   = $select->getBindValues();
        $this->assertSame($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: with() recursive
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementSelectWithRecursive(): void
    {
        $driver = env('driver');
        $select = Select::new($driver);

        $select
            ->withRecursive()
            ->with('cte1', 'SELECT cst_id FROM co_customers WHERE cst_status_flag = 1')
            ->withColumns('cte2', ['cst_id'], 'SELECT * FROM co_customers WHERE cst_status_flag = -1')
            ->from('cte1')
            ->union()
            ->from('cte2')
        ;

        $expected = 'WITH RECURSIVE `cte1` AS '
            . '(SELECT cst_id '
            . 'FROM co_customers '
            . 'WHERE cst_status_flag = 1), '
            . '`cte2` (`cst_id`) AS '
            . '(SELECT * '
            . 'FROM co_customers '
            . 'WHERE cst_status_flag = -1) '
            . 'SELECT * FROM cte1 '
            . 'UNION '
            . 'SELECT * FROM cte2';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);
    }
}
