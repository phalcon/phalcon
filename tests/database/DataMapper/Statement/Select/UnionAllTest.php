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

final class UnionAllTest extends AbstractStatementTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: unionAll()
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementSelectUnionAll(): void
    {
        $driver = env('driver');
        $select = Select::new($driver);

        $select
            ->from('co_invoices')
            ->where('inv_id = 1')
            ->unionAll()
            ->from('co_invoices')
            ->where('inv_id = 2')
            ->unionAll()
            ->from('co_invoices')
            ->where('inv_id = 3')
        ;

        $expected = 'SELECT * FROM co_invoices WHERE inv_id = 1 UNION ALL '
            . 'SELECT * FROM co_invoices WHERE inv_id = 2 UNION ALL '
            . 'SELECT * FROM co_invoices WHERE inv_id = 3';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);
    }
}
