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
use Phalcon\Events\Exception;
use Phalcon\Events\Manager;
use UnitTester;

/**
 * Class StopCest
 *
 * @package Phalcon\Tests\Unit\Events\Event
 */
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
    public function eventsEventStop(UnitTester $I)
    {
        $I->wantToTest('Events\Event - stop()');

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

    /**
     * Tests using events propagation - non cancelable event
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function eventsEventStopNonCancelable(UnitTester $I)
    {
        $I->wantToTest('Events\Event - stop() - exception');

        $I->expectThrowable(
            new Exception('Trying to cancel a non-cancelable event'),
            function () {
                $event = new Event(
                    'some-type:beforeSome',
                    $this,
                    [],
                    false
                );
                $event->stop();
            }
        );
    }
}
