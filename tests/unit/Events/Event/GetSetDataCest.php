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
use UnitTester;

class GetSetDataCest
{
    /**
     * Tests Phalcon\Events\Event - getData() when not explicitly set
     *
     * @param UnitTester $I
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2020-09-09
     */
    public function eventsEventGetSetDataNotExplicitlySet(UnitTester $I)
    {
        $I->wantToTest('Events\Event - getData() when not explicitly set');

        $event  = new Event('type-one:beforeSome', $this);
        $actual = $event->getData();

        $I->assertNull($actual);
    }

    /**
     * Tests Phalcon\Events\Event - getData() when explicitly set
     *
     * @param UnitTester $I
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2020-09-09
     */
    public function eventsEventGetDataExplicitlySet(UnitTester $I)
    {
        $I->wantToTest('Events\Event - getData() when explicitly set');

        $data    = [1, 2, 3];
        $event    = new Event('type-two:beforeSome', $this, $data);
        $expected = $data;
        $actual   = $event->getData();
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Events\Event - setData() overwrites previous data
     *
     * @param UnitTester $I
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2020-09-09
     */
    public function eventsEventGetSetDataOverwrite(UnitTester $I)
    {
        $I->wantToTest('Events\Event - setData() overwrites previous data');

        $event = new Event('type-three:beforeSome', $this);

        $data = [1, 2, 3];
        $event->setData($data);

        $expected = $data;
        $actual   = $event->getData();
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Events\Event - setData() with no parameters
     *
     * @param UnitTester $I
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2020-09-09
     */
    public function eventsEventGetSetDataWithNoParameters(UnitTester $I)
    {
        $I->wantToTest('Events\Event - setData() with no parameters');

        $event = new Event('type-four:beforeSome', $this, [1, 2, 3]);

        $event->setData();
        $actual = $event->getData();

        $I->assertNull($actual);
    }
}
