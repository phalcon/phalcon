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
use Phalcon\Tests\Fixtures\Tasks\MainTask;

/**
 * Class GetActiveTaskTest extends UnitTestCase
 */
final class GetActiveTaskTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Cli\Dispatcher :: getActiveTask()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testCliDispatcherGetActiveTask(): void
    {
        $dispatcher = new Dispatcher();
        $dispatcher->setDI(new DiFactoryDefault());
        $dispatcher->setDefaultNamespace('Phalcon\Tests\Fixtures\Tasks');
        $dispatcher->setTaskName("main");
        $dispatcher->dispatch();

        $class  = MainTask::class;
        $actual = $dispatcher->getActiveTask();
        $this->assertInstanceOf($class, $actual);
    }
}
