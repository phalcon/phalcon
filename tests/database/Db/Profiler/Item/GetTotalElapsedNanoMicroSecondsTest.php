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

final class GetTotalElapsedNanoMicroSecondsTest extends AbstractDatabaseTestCase
{
    /**
     * Tests Phalcon\Db\Profiler\Item :: getTotalElapsedNanoseconds() /
     * getTotalElapsedMicroseconds() / getTotalElapsedSeconds()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     *
     * @group mysql
     */
    public function testDbProfilerItemGetTotalElapsedNanoMicroSeconds(): void
    {
        $item  = new Item();
        $start = 444445555566666;
        $end   = 999999999999999;

        $item->setInitialTime($start);
        $item->setFinalTime($end);

        $expected = (float)($end - $start);
        $actual   = $item->getTotalElapsedNanoseconds();
        $this->assertSame($expected, $actual);

        $expected = ($end - $start) / 1000000;
        $actual   = $item->getTotalElapsedMilliseconds();
        $this->assertSame($expected, $actual);

        $expected = ($end - $start) / 1000000000;
        $actual   = $item->getTotalElapsedSeconds();
        $this->assertSame($expected, $actual);
    }
}
