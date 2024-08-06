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

final class GetSetFinalTimeTest extends DatabaseTestCase
{
    /**
     * Tests Phalcon\Db\Profiler\Item :: getFinalTime()/setFinalTime()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     *
     * @group  common
     */
    public function dbProfilerItemGetSetFinalTime(): void
    {
        $item = new Item();
        $item->setFinalTime(123.45);
        $this->assertSame(123.45, $item->getFinalTime());
    }
}
