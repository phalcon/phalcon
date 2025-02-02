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

final class FromTest extends AbstractStatementTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: from()
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementSelectFrom(): void
    {
        $driver = env('driver');
        $select = Select::new($driver);

        $select
            ->from('co_invoices')
            ->from('co_customers')
        ;

        $expected = 'SELECT * FROM co_invoices, co_customers';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Statement\Select :: from() - empty
     *
     * @since  2020-01-20
     *
     * @group mysql
     */
    public function testDmStatementSelectFromEmpty(): void
    {
        $driver = env('driver');
        $select = Select::new($driver);

        $expected = 'SELECT *';
        $actual   = $select->getStatement();
        $this->assertSame($expected, $actual);
    }
}
