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

namespace Phalcon\Tests\Database\Db\Profiler\Item;

use Phalcon\Db\Profiler\Item;
use Phalcon\Tests\AbstractDatabaseTestCase;

final class GetSetSqlStatementTest extends AbstractDatabaseTestCase
{
    /**
     * Tests Phalcon\Db\Profiler\Item :: getSqlStatement()/setSqlStatement()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     *
     * @group mysql
     */
    public function testDbProfilerItemGetSetSqlStatement(): void
    {
        $item = new Item();
        $item->setSqlStatement('select * from co_invoices');
        $this->assertSame(
            'select * from co_invoices',
            $item->getSqlStatement()
        );
    }
}
