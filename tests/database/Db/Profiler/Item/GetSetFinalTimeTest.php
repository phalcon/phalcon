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

final class GetSetFinalTimeTest extends AbstractDatabaseTestCase
{
    /**
     * Tests Phalcon\Db\Profiler\Item :: getFinalTime()/setFinalTime()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     *
     * @group mysql
     */
    public function testDbProfilerItemGetSetFinalTime(): void
    {
        $item = new Item();
        $item->setFinalTime(123.45);
        $this->assertSame(123.45, $item->getFinalTime());
    }
}
