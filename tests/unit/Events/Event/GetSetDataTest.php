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

namespace Phalcon\Tests\Unit\Events\Event;

use Phalcon\Events\Event;
use Phalcon\Tests\UnitTestCase;

final class GetSetDataTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Events\Event - setData() - default
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-10-06
     */
    public function testEventsEventGetSetData(): void
    {
        $event = new Event('some-type:beforeSome', $this);

        $actual = $event->getData();
        $this->assertNull($actual);
    }

    /**
     * Tests Phalcon\Events\Event - setData() - constructor
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-10-06
     */
    public function testEventsEventGetSetDataConstructor(): void
    {
        $data     = [1, 2, 3];
        $event    = new Event('some-type:beforeSome', $this, $data);
        $expected = $data;
        $actual   = $event->getData();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Events\Event - setData() - empty
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-10-06
     */
    public function testEventsEventGetSetDataEmpty(): void
    {
        $data     = [1, 2, 3];
        $event    = new Event('some-type:beforeSome', $this, $data);
        $expected = $data;
        $actual   = $event->getData();
        $this->assertSame($expected, $actual);

        $event->setData();
        $actual = $event->getData();
        $this->assertNull($actual);
    }

    /**
     * Tests Phalcon\Events\Event - setData() - overwrite
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-10-06
     */
    public function testEventsEventGetSetDataOverwrite(): void
    {
        $data     = [1, 2, 3];
        $event    = new Event('some-type:beforeSome', $this, $data);
        $expected = $data;
        $actual   = $event->getData();
        $this->assertSame($expected, $actual);

        $newData = [4, 5, 6];
        $event->setData($newData);
        $expected = $newData;
        $actual   = $event->getData();
        $this->assertSame($expected, $actual);
    }
}
