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
use Phalcon\Tests\Fixtures\Tasks\EchoTask;
use Phalcon\Tests\Fixtures\Tasks\MainTask;

final class GetLastTaskTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Cli\Dispatcher :: getLastTask()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testCliDispatcherGetLastTask(): void
    {
        $dispatcher = new Dispatcher();
        $dispatcher->setDI(new DiFactoryDefault());
        $dispatcher->setDefaultNamespace('Phalcon\Tests\Fixtures\Tasks');
        $dispatcher->setTaskName("main");
        $dispatcher->dispatch();

        $class  = MainTask::class;
        $actual = $dispatcher->getLastTask();
        $this->assertInstanceOf($class, $actual);

        $dispatcher->setTaskName("echo");
        $dispatcher->dispatch();

        $class  = EchoTask::class;
        $actual = $dispatcher->getLastTask();
        $this->assertInstanceOf($class, $actual);
    }
}
