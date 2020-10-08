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

class GetTypeCest
{
    /**
     * Tests Phalcon\Events\Event :: getType()
     *
     * @param UnitTester $I
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2020-09-09
     */
    public function eventsEventGetType(UnitTester $I)
    {
        $I->wantToTest('Events\Event - getType()');

        $type  = 'some-type:beforeSome';
        $event = new Event($type, $this, []);

        $expected = $type;
        $actual   = $event->getType();
        $I->assertSame($expected, $actual);
    }
}
