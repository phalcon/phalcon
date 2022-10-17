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

namespace Phalcon\Tests\Cli\Cli\Task;

use CliTester;
use Phalcon\Cli\Task;
use Phalcon\Di\FactoryDefault\Cli as DiFactoryDefault;
use Phalcon\Support\Registry;
use Phalcon\Tests\Fixtures\Tasks\EchoTask;
use Phalcon\Tests\Fixtures\Tasks\MainTask;
use Phalcon\Tests\Fixtures\Tasks\OnConstructTask;

class ConstructCest
{
    /**
     * Tests Phalcon\Cli\Task :: __construct()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function cliTaskConstruct(CliTester $I)
    {
        $I->wantToTest('Cli\Task - __construct()');
        $task = new Task();

        $class = Task::class;
        $I->assertInstanceOf($class, $task);

        $task = new OnConstructTask();

        $actual = $task->onConstructExecuted;
        $I->assertTrue($actual);
    }

    public function extendTask(CliTester $I)
    {
        $di             = new DiFactoryDefault();
        $di['registry'] = function () {
            $registry = new Registry();

            $registry->data = 'data';

            return $registry;
        };

        $task = new MainTask();
        $task->setDI($di);

        $expected = 'data';
        $actual   = $task->requestRegistryAction();
        $I->assertSame($expected, $actual);

        $expected = 'Hello !';
        $actual   = $task->helloAction();
        $I->assertSame($expected, $actual);

        $expected = 'Hello World!';
        $actual   = $task->helloAction('World');
        $I->assertSame($expected, $actual);
    }

    public function echoTask(CliTester $I)
    {
        $task = new EchoTask();
        $di   = new DiFactoryDefault();

        $task->setDI($di);

        $expected = 'echoMainAction';
        $actual   = $task->mainAction();
        $I->assertSame($expected, $actual);
    }
}
