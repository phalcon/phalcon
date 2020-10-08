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
use Phalcon\Events\EventInterface;
use UnitTester;

class ConstructCest
{
    /**
     * Tests Phalcon\Events\Event :: __construct()
     *
     * @param UnitTester $I
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2020-09-09
     */
    public function eventsEventConstruct(UnitTester $I)
    {
        $I->wantToTest('Events\Event - __construct()');

        $actual = new Event('some-type:beforeSome', $this);
        $class  = EventInterface::class;
        $I->assertInstanceOf($class, $actual);
    }
}
