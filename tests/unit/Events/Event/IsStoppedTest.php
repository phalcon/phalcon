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

final class IsStoppedTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Events\Event :: isStopped()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-05-20
     */
    public function testEventsEventIsStopped(): void
    {
        $type  = 'some-type:beforeSome';
        $event = new Event($type, $this);

        $actual = $event->isStopped();
        $this->assertFalse($actual);

        $event->stop();

        $actual = $event->isStopped();
        $this->assertTrue($actual);
    }
}
