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

namespace Phalcon\Tests\Unit\Cli\Dispatcher;

use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\UnitTestCase;

final class GetSetEventsManagerTest extends UnitTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Cli\Dispatcher :: getEventsManager()/setEventsManager()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testCliDispatcherGetSetEventsManager(): void
    {
        $console = $this->newService('console');
        $manager = $this->newService('eventsManager');

        $console->setEventsManager($manager);

        $actual = $console->getEventsManager();
        $this->assertSame($manager, $actual);
    }
}
