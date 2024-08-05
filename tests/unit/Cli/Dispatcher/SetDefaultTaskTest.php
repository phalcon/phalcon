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

use Phalcon\Tests\UnitTestCase;
use Phalcon\Cli\Dispatcher;
use Phalcon\Di\FactoryDefault\Cli as DiFactoryDefault;

final class SetDefaultTaskTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Cli\Dispatcher :: setDefaultTask()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testCliDispatcherSetDefaultTask(): void
    {
        $dispatcher = new Dispatcher();
        $dispatcher->setDefaultNamespace('Phalcon\Tests\Fixtures\Tasks');
        $dispatcher->setDI(
            new DiFactoryDefault()
        );
        $defaultTask = "echo";
        $dispatcher->setDefaultTask($defaultTask);

        $expected = '';
        $actual   = $dispatcher->getTaskName();
        $this->assertSame($expected, $actual);

        $dispatcher->dispatch();

        $expected = $defaultTask;
        $actual   = $dispatcher->getTaskName();
        $this->assertSame($expected, $actual);
    }
}
