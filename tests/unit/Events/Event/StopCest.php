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
use Phalcon\Events\Manager;
use UnitTester;

class StopCest
{
    /**
     * Tests using events propagation
     *
     * @param UnitTester $I
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2020-09-09
     */
    public function stopEventsInEventsManager(UnitTester $I)
    {
        $number        = 0;
        $eventsManager = new Manager();

        $propagationListener = function (Event $event, $component, $data) use (&$number) {
            $number++;

            $event->stop();
        };

        $eventsManager->attach('some-type', $propagationListener);
        $eventsManager->attach('some-type', $propagationListener);

        $eventsManager->fire('some-type:beforeSome', $this);

        $I->assertEquals(1, $number);
    }
}
