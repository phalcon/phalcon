<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Tests\Unit\Cli\Console;

use Phalcon\Tests\UnitTestCase;
use Phalcon\Cli\Console as CliConsole;
use Phalcon\Di\FactoryDefault\Cli as DiFactoryDefault;
use Phalcon\Events\Manager as EventsManager;

final class GetSetEventsManagerTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Cli\Console :: getEventsManager()
     * Tests Phalcon\Cli\Console :: setEventsManager()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     *
     * @author Nathan Edwards <https://github.com/npfedwards>
     * @since  2018-12-26
     */
    public function testCliConsoleGetSetEventsManager(): void
    {
        $console = new CliConsole(new DiFactoryDefault());

        $eventsManager = new EventsManager();

        $console->setEventsManager($eventsManager);

        $expected = $eventsManager;
        $actual   = $console->getEventsManager();
        $this->assertSame($expected, $actual);
    }
}
