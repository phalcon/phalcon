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

final class GetSetSqlBindTypesTest extends AbstractDatabaseTestCase
{
    /**
     * Tests Phalcon\Db\Profiler\Item :: getSqlBindTypes()/setSqlBindTypes()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     *
     * @group mysql
     */
    public function testDbProfilerItemGetSetSqlBindTypes(): void
    {
        $item = new Item();
        $item->setSqlBindTypes(['one' => 1, 'two' => 2]);
        $this->assertSame(['one' => 1, 'two' => 2], $item->getSqlBindTypes());
    }
}
