<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Tests\Unit\Events;

use Phalcon\Events\Manager;
use Phalcon\Tests\Fixtures\Events\ComponentWithEvents;
use Phalcon\Tests\AbstractUnitTestCase;

use function method_exists;

final class ComponentManagerTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Events\EventsAwareTrait
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEventsComponentManager(): void
    {
        $manager   = new Manager();
        $component = new ComponentWithEvents();

        $actual = method_exists($component, 'getEventsManager');
        $this->assertTrue($actual);
        $actual = method_exists($component, 'setEventsManager');
        $this->assertTrue($actual);

        $component->setEventsManager($manager);
        $actual = $component->getEventsManager();
        $this->assertSame($manager, $actual);
    }
}
