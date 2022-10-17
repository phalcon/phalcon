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

namespace Phalcon\Tests\Cli\Cli\Dispatcher;

use CliTester;
use Phalcon\Cli\Dispatcher;
use Phalcon\Di\FactoryDefault\Cli as DiFactoryDefault;
use Phalcon\Tests\Fixtures\Tasks\EchoTask;
use Phalcon\Tests\Fixtures\Tasks\MainTask;

class GetLastTaskCest
{
    /**
     * Tests Phalcon\Cli\Dispatcher :: getLastTask()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function cliDispatcherGetLastTask(CliTester $I)
    {
        $I->wantToTest('Cli\Dispatcher - getLastTask()');
        $dispatcher = new Dispatcher();
        $dispatcher->setDI(new DiFactoryDefault());
        $dispatcher->setDefaultNamespace('Phalcon\Tests\Fixtures\Tasks');
        $dispatcher->setTaskName("main");
        $dispatcher->dispatch();

        $class  = MainTask::class;
        $actual = $dispatcher->getLastTask();
        $I->assertInstanceOf($class, $actual);

        $dispatcher->setTaskName("echo");
        $dispatcher->dispatch();

        $class  = EchoTask::class;
        $actual = $dispatcher->getLastTask();
        $I->assertInstanceOf($class, $actual);
    }
}
