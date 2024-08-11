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

use Phalcon\Cli\Dispatcher;
use Phalcon\Cli\Dispatcher\Exception;
use Phalcon\Di\FactoryDefault\Cli as DiFactoryDefault;
use Phalcon\Tests\AbstractUnitTestCase;

final class DispatchTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Cli\Dispatcher :: dispatch()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testCliDispatcherDispatch(): void
    {
        // test 1
        $dispatcher = new Dispatcher();
        $dispatcher->setDI(new DiFactoryDefault());
        $dispatcher->setDefaultNamespace('Phalcon\Tests\Fixtures\Tasks');
        $dispatcher->dispatch();

        $expected = 'main';
        $actual   = $dispatcher->getTaskName();
        $this->assertSame($expected, $actual);

        $expected = 'main';
        $actual   = $dispatcher->getActionName();
        $this->assertSame($expected, $actual);

        $expected = [];
        $actual   = $dispatcher->getParams();
        $this->assertSame($expected, $actual);

        $expected = 'mainAction';
        $actual   = $dispatcher->getReturnedValue();
        $this->assertSame($expected, $actual);

        // Test 2
        $dispatcher = new Dispatcher();
        $dispatcher->setDI(new DiFactoryDefault());
        $dispatcher->setDefaultNamespace('Phalcon\Tests\Fixtures\Tasks');
        $dispatcher->setTaskName('echo');
        $dispatcher->dispatch();

        $expected = 'echo';
        $actual   = $dispatcher->getTaskName();
        $this->assertSame($expected, $actual);

        $expected = 'main';
        $actual   = $dispatcher->getActionName();
        $this->assertSame($expected, $actual);

        $expected = [];
        $actual   = $dispatcher->getParams();
        $this->assertSame($expected, $actual);

        $expected = 'echoMainAction';
        $actual   = $dispatcher->getReturnedValue();
        $this->assertSame($expected, $actual);

        // Test 3
        $dispatcher = new Dispatcher();
        $dispatcher->setDI(new DiFactoryDefault());
        $dispatcher->setDefaultNamespace('Phalcon\Tests\Fixtures\Tasks');
        $dispatcher->setTaskName('main');
        $dispatcher->setActionName('hello');
        $dispatcher->dispatch();

        $expected = 'main';
        $actual   = $dispatcher->getTaskName();
        $this->assertSame($expected, $actual);

        $expected = 'hello';
        $actual   = $dispatcher->getActionName();
        $this->assertSame($expected, $actual);

        $expected = [];
        $actual   = $dispatcher->getParams();
        $this->assertSame($expected, $actual);

        $expected = 'Hello !';
        $actual   = $dispatcher->getReturnedValue();
        $this->assertSame($expected, $actual);

        // Test 4
        $dispatcher = new Dispatcher();
        $dispatcher->setDI(new DiFactoryDefault());
        $dispatcher->setDefaultNamespace('Phalcon\Tests\Fixtures\Tasks');
        $dispatcher->setActionName('hello');
        $dispatcher->setParams(
            [
                'World',
                '#####',
            ]
        );
        $dispatcher->dispatch();

        $expected = 'main';
        $actual   = $dispatcher->getTaskName();
        $this->assertSame($expected, $actual);

        $expected = 'hello';
        $actual   = $dispatcher->getActionName();
        $this->assertSame($expected, $actual);

        $expected = [
            'World',
            '#####',
        ];
        $actual   = $dispatcher->getParams();
        $this->assertSame($expected, $actual);

        $expected = 'Hello World#####';
        $actual   = $dispatcher->getReturnedValue();
        $this->assertSame($expected, $actual);

        //Test 5
        $dispatcher = new Dispatcher();
        $dispatcher->setDI(new DiFactoryDefault());
        $dispatcher->setDefaultNamespace('Phalcon\Tests\Fixtures\Tasks');
        $dispatcher->setActionName('hello');
        $dispatcher->setParams(
            [
                'hello'   => 'World',
                'goodbye' => 'Everybody',
            ]
        );
        $dispatcher->dispatch();


        $actual = $dispatcher->hasParam('hello');
        $this->assertTrue($actual);
        $actual = $dispatcher->hasParam('goodbye');
        $this->assertTrue($actual);
        $actual = $dispatcher->hasParam('salutations');
        $this->assertFalse($actual);
    }

    public function testFakeNamespace(): void
    {
        $dispatcher = new Dispatcher();

        $dispatcher->setDI(new DiFactoryDefault());

        $dispatcher->setDefaultNamespace('Dummy\\');
        $dispatcher->setTaskName('main');
        $dispatcher->setActionName('hello');

        $dispatcher->setParams(
            ['World']
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Dummy\MainTask handler class cannot be loaded',
        );
        $this->expectExceptionCode(Exception::EXCEPTION_HANDLER_NOT_FOUND);

        $dispatcher->dispatch();
    }
}
