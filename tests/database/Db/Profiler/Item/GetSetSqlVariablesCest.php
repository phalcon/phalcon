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

use DatabaseTester;
use Phalcon\Db\Profiler\Item;

class GetSetSqlVariablesCest
{
    /**
     * Tests Phalcon\Db\Profiler\Item :: getSqlVariables()/setSqlVariables()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     *
     * @group  common
     */
    public function dbProfilerItemGetSetSqlVariables(DatabaseTester $I)
    {
        $I->wantToTest('Db\Profiler\Item - getSqlVariables()/setSqlVariables()');

        $item = new Item();
        $item->setSqlVariables(['one' => 1, 'two' => 2]);
        $I->assertSame(['one' => 1, 'two' => 2], $item->getSqlVariables());
    }
}
