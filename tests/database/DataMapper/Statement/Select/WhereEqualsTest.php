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

final class WhereEqualsTest extends AbstractStatementTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: whereEquals()
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementSelectWhereEquals(): void
    {
        $driver = env('driver');
        $select = Select::new($driver);

        $select
            ->from('co_invoices')
            ->whereEquals(
                [
                    'inv_id'     => [1, 2, 3],
                    'inv_cst_id' => null,
                    'inv_title'  => 'ACME',
                    'inv_created_at = NOW()',
                ]
            )
        ;

        $expected = 'SELECT * '
            . 'FROM co_invoices '
            . 'WHERE inv_id IN (:_1_1_, :_1_2_, :_1_3_) '
            . 'AND inv_cst_id IS NULL '
            . 'AND inv_title = :_1_4_ '
            . 'AND inv_created_at = NOW()';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);
    }
}
