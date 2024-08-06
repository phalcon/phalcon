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

namespace Phalcon\Tests\Unit\Db\Profiler\Item;

use Phalcon\Tests\DatabaseTestCase;
use Phalcon\Db\Profiler\Item;

final class GetSetSqlStatementTest extends DatabaseTestCase
{
    /**
     * Tests Phalcon\Db\Profiler\Item :: getSqlStatement()/setSqlStatement()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     *
     * @group  common
     */
    public function dbProfilerItemGetSetSqlStatement(): void
    {
        $item = new Item();
        $item->setSqlStatement('select * from co_invoices');
        $this->assertSame(
            'select * from co_invoices',
            $item->getSqlStatement()
        );
    }
}
