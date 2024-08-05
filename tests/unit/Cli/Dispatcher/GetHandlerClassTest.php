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
use Codeception\Example;
use Phalcon\Cli\Dispatcher;

/**
 * Class GetHandlerClassTest extends UnitTestCase
 */
final class GetHandlerClassTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Cli\Dispatcher :: getHandlerClass()
     *
     * @dataProvider getTestCases
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2018-11-13
     */
    public function testCliDispatcherGetHandlerClass(
        string $namespace,
        string $task,
        string $suffix,
        string $expected
    ): void {
        $dispatcher = new Dispatcher();

        // test the handler name
        $dispatcher->setNamespaceName($namespace);
        $dispatcher->setTaskName($task);
        $dispatcher->setHandlerSuffix($suffix);

        $actual   = $dispatcher->getHandlerClass();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array[]
     */
    public static function getTestCases(): array
    {
        return [
            ['', 'hello', '', 'Hello'],
            ['', 'hello-phalcon', '', 'HelloPhalcon'],
            ['', 'hello_phalcon', '', 'HelloPhalcon'],
            ['', 'HelloPhalcon', '', 'HelloPhalcon'],
            ['', 'Hello\\Phalcon', '', 'Hello\\Phalcon'],
            ['', 'non_std\\Phalcon', '', 'non_std\\Phalcon'],
            // include the suffix
            ['', 'hello', 'Ctrl', 'HelloCtrl'],
            ['', 'hello-phalcon', 'Ctrl', 'HelloPhalconCtrl'],
            ['', 'hello_phalcon', 'Ctrl', 'HelloPhalconCtrl'],
            ['', 'HelloPhalcon', 'Ctrl', 'HelloPhalconCtrl'],
            ['', 'Hello\\Phalcon', 'Ctrl', 'Hello\\PhalconCtrl'],
            // include the namespace
            ['Ola', 'hello', 'Ctrl', 'Ola\\HelloCtrl'],
            ['Ola', 'hello-phalcon', 'Ctrl', 'Ola\\HelloPhalconCtrl'],
            ['ola\\', 'hello_phalcon', 'Ctrl', 'ola\\HelloPhalconCtrl'],
            ['ola_phalcon\\', 'HelloPhalcon', 'Ctrl', 'ola_phalcon\\HelloPhalconCtrl'],
            ['Ola\\', 'Hello\\Phalcon', 'Ctrl', 'Ola\\Hello\\PhalconCtrl'],
        ];
    }
}
