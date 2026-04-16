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

final class DistinctTest extends AbstractStatementTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: distinct()
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementSelectDistinct(): void
    {
        $driver = env('driver');
        $select = Select::new($driver);

        $select
            ->distinct()
            ->from('co_invoices')
            ->columns(['inv_id', 'inc_cst_id'])
        ;

        $expected = 'SELECT DISTINCT inv_id, inc_cst_id FROM co_invoices';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: distinct() - twice
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementSelectDistinctTwice(): void
    {
        $driver = env('driver');
        $select = Select::new($driver);

        $select
            ->distinct()
            ->distinct()
            ->from('co_invoices')
            ->columns(['inv_id', 'inc_cst_id'])
        ;

        $expected = 'SELECT DISTINCT inv_id, inc_cst_id FROM co_invoices';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: distinct() - unset
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementSelectDistinctUnset(): void
    {
        $driver = env('driver');
        $select = Select::new($driver);

        $select
            ->distinct()
            ->distinct(false)
            ->from('co_invoices')
            ->columns(['inv_id', 'inc_cst_id'])
        ;

        $expected = 'SELECT inv_id, inc_cst_id FROM co_invoices';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);
    }
}
