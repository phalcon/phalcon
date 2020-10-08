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

class IsCancelableCest
{
    /**
     * Tests Phalcon\Events\Event :: isCancelable()
     *
     * @param UnitTester $I
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2020-09-09
     */
    public function eventsEventIsCancelable(UnitTester $I)
    {
        $I->wantToTest('Events\Event - isCancelable()');

        $event  = new Event('type-one:beforeSome', $this, []);
        $actual = $event->isCancelable();
        $I->assertTrue($actual);

        $event = new Event('type-two:beforeSome', $this, [], false);
        $actual = $event->isCancelable();
        $I->assertFalse($actual);

        $event = new Event('type-three:beforeSome', $this, [], true);
        $actual = $event->isCancelable();
        $I->assertTrue($actual);
    }
}
