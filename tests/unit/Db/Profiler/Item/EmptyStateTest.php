<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Db\Profiler\Item;

use Phalcon\Db\Profiler\Item;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use TypeError;

/**
 * Reads before the times are set, as in cphalcon.
 */
final class EmptyStateTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/838
     */
    public function testDbProfilerItemGetFinalTimeBeforeSet(): void
    {
        $item = new Item();

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage(
            'Phalcon\Db\Profiler\Item::getFinalTime(): '
            . 'Return value must be of type float, null returned'
        );

        $item->getFinalTime();
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/838
     */
    public function testDbProfilerItemGetInitialTimeBeforeSet(): void
    {
        $item = new Item();

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage(
            'Phalcon\Db\Profiler\Item::getInitialTime(): '
            . 'Return value must be of type float, null returned'
        );

        $item->getInitialTime();
    }

    /**
     * The elapsed time is 0 before the times are set.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/838
     */
    public function testDbProfilerItemGetTotalElapsedBeforeSet(): void
    {
        $item = new Item();

        $this->assertEquals(0.0, $item->getTotalElapsedNanoseconds());
        $this->assertEquals(0.0, $item->getTotalElapsedMilliseconds());
        $this->assertEquals(0.0, $item->getTotalElapsedSeconds());
    }
}
