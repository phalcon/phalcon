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

namespace Phalcon\Tests\Unit\Mvc\Dispatcher\Refactor;

use Exception;
use Phalcon\Tests\Unit\Mvc\Dispatcher\Helper\BaseDispatcher;

/**
 * @todo    : refactor
 */
class DispatcherAfterDispatchLoopTest extends BaseDispatcher
{
    /**
     * Tests the forwarding in the afterDispatchLoop event
     *
     * @author Mark Johnson <https://github.com/virgofx>
     * @since  2017-10-07
     */
    public function testAfterDispatchLoopForward(): void
    {
        $forwarded  = false;
        $dispatcher = $this->getDispatcher();

        $dispatcher->getEventsManager()->attach(
            'dispatch:afterDispatchLoop',
            function ($event, $dispatcher) use (&$forwarded) {
                if ($forwarded === false) {
                    $dispatcher->forward(
                        [
                            'action' => 'index2',
                        ]
                    );

                    $forwarded = true;
                }
            }
        );

        $dispatcher->dispatch();

        $expected = [
            'beforeDispatchLoop',
            'beforeDispatch',
            'beforeExecuteRoute',
            'beforeExecuteRoute-method',
            'initialize-method',
            'afterInitialize',
            'indexAction',
            'afterExecuteRoute',
            'afterExecuteRoute-method',
            'afterDispatch',
            'afterDispatchLoop',
        ];

        $this->assertEquals(
            $expected,
            $this->getDispatcherListener()->getTrace()
        );
    }

    /**
     * Tests returning <tt>false</tt> inside an afterDispatchLoop event.
     *
     * @author Mark Johnson <https://github.com/virgofx>
     * @since  2017-10-07
     */
    public function testAfterDispatchLoopReturnFalse(): void
    {
        $dispatcher         = $this->getDispatcher();
        $dispatcherListener = $this->getDispatcherListener();

        $dispatcher->getEventsManager()->attach(
            'dispatch:afterDispatchLoop',
            function () use ($dispatcherListener) {
                $dispatcherListener->trace('afterDispatchLoop: custom return false');

                return false;
            }
        );

        $dispatcher->dispatch();

        $expected = [
            'beforeDispatchLoop',
            'beforeDispatch',
            'beforeExecuteRoute',
            'beforeExecuteRoute-method',
            'initialize-method',
            'afterInitialize',
            'indexAction',
            'afterExecuteRoute',
            'afterExecuteRoute-method',
            'afterDispatch',
            'afterDispatchLoop',
            'afterDispatchLoop: custom return false',
        ];

        $this->assertEquals(
            $expected,
            $this->getDispatcherListener()->getTrace()
        );
    }

    /**
     * Tests exception handling to ensure exceptions can be properly handled
     * via beforeException event and then will properly bubble up the stack if
     * anything other than <tt>false</tt> is returned.
     *
     * @author Mark Johnson <https://github.com/virgofx>
     * @since  2017-10-07
     */
    public function testAfterDispatchLoopWithBeforeExceptionBubble(): void
    {
        $dispatcher         = $this->getDispatcher();
        $dispatcherListener = $this->getDispatcherListener();

        $dispatcher->getEventsManager()->attach(
            'dispatch:afterDispatchLoop',
            function () {
                throw new Exception('afterDispatchLoop exception occurred');
            }
        );
        $dispatcher->getEventsManager()->attach(
            'dispatch:beforeException',
            function () use ($dispatcherListener) {
                $dispatcherListener->trace(
                    'beforeException: custom before exception bubble'
                );

                return null;
            }
        );

        $this->expectException(Exception::class);

        $dispatcher->dispatch();

        $expected = [
            'beforeDispatchLoop',
            'beforeDispatch',
            'beforeExecuteRoute',
            'beforeExecuteRoute-method',
            'initialize-method',
            'afterInitialize',
            'indexAction',
            'afterExecuteRoute',
            'afterExecuteRoute-method',
            'afterDispatch',
            'afterDispatchLoop',
            'beforeException: afterDispatchLoop exception occurred',
            'beforeException: custom before exception bubble',
        ];

        $this->assertEquals(
            $expected,
            $this->getDispatcherListener()->getTrace()
        );
    }

    /**
     * Tests dispatch forward handling inside the beforeException when an
     * exception occurs in an "afterDispatchLoop" event.
     *
     * @author Mark Johnson <https://github.com/virgofx>
     * @since  2017-10-07
     */
    public function testAfterDispatchLoopWithBeforeExceptionForwardOnce(): void
    {
        $forwarded          = false;
        $dispatcher         = $this->getDispatcher();
        $dispatcherListener = $this->getDispatcherListener();

        $dispatcher->getEventsManager()->attach(
            'dispatch:afterDispatchLoop',
            function () use (&$forwarded) {
                if ($forwarded === false) {
                    $forwarded = true;

                    throw new Exception('afterDispatchLoop exception occurred');
                }
            }
        );

        $dispatcher->getEventsManager()->attach(
            'dispatch:beforeException',
            function ($event, $dispatcher) use ($dispatcherListener) {
                $dispatcherListener->trace('beforeException: custom before exception forward');

                $dispatcher->forward(
                    [
                        'action' => 'index2',
                    ]
                );
            }
        );

        $this->expectException(Exception::class);

        $dispatcher->dispatch();

        $expected = [
            'beforeDispatchLoop',
            'beforeDispatch',
            'beforeExecuteRoute',
            'beforeExecuteRoute-method',
            'initialize-method',
            'afterInitialize',
            'indexAction',
            'afterExecuteRoute',
            'afterExecuteRoute-method',
            'afterDispatch',
            'afterDispatchLoop',
            'beforeException: afterDispatchLoop exception occurred',
            'beforeException: custom before exception forward',
        ];

        $this->assertEquals(
            $expected,
            $this->getDispatcherListener()->getTrace()
        );
    }

    /**
     * Tests exception handling to ensure exceptions can be properly handled
     * when thrown from inside an "afterDispatchLoop" event and then ensure the
     * exception is not bubbled when returning with <tt>false</tt>.
     *
     * @author Mark Johnson <https://github.com/virgofx>
     * @since  2017-10-07
     */
    public function testAfterDispatchLoopWithBeforeExceptionReturningFalse(): void
    {
        $dispatcher = $this->getDispatcher();

        $dispatcher->getEventsManager()->attach(
            'dispatch:afterDispatchLoop',
            function () {
                throw new Exception('afterDispatch exception occurred');
            }
        );

        $dispatcher->getEventsManager()->attach(
            'dispatch:beforeException',
            function () {
                return false;
            }
        );

        $dispatcher->dispatch();

        $expected = [
            'beforeDispatchLoop',
            'beforeDispatch',
            'beforeExecuteRoute',
            'beforeExecuteRoute-method',
            'initialize-method',
            'afterInitialize',
            'indexAction',
            'afterExecuteRoute',
            'afterExecuteRoute-method',
            'afterDispatch',
            'afterDispatchLoop',
            'beforeException: afterDispatch exception occurred',
        ];

        $this->assertEquals(
            $expected,
            $this->getDispatcherListener()->getTrace()
        );
    }
}
