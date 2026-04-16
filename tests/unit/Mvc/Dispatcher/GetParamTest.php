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

namespace Phalcon\Tests\Unit\Mvc\Dispatcher;

use Phalcon\Cli\Dispatcher as CliDispatcher;
use Phalcon\Cli\Dispatcher\Exception as CliDispatcherException;
use Phalcon\Dispatcher\Exception as DispatcherException;
use Phalcon\Tests\AbstractUnitTestCase;

class GetParamTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Mvc\Dispatcher :: getParam()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testMvcDispatcherGetParam(): void
    {
        $this->markTestSkipped('Need implementation');
    }

    /**
     * Tests Phalcon\Dispatcher\AbstractDispatcher :: getParameter() - null container
     *
     * When getParameter() is called with a non-empty $filters array and
     * $this->container is null, throwDispatchException() is invoked (L943-947).
     * The Cli\Dispatcher variant is used because its throwDispatchException()
     * does not require the container (no checkContainer call).
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testDispatcherGetParameterNullContainerThrows(): void
    {
        $dispatcher = new CliDispatcher();
        // No DI container set
        $dispatcher->setParams(['key' => 'value']);

        $this->expectException(CliDispatcherException::class);
        $this->expectExceptionMessage(
            "A dependency injection container is required to access the 'filter' service"
        );
        $this->expectExceptionCode(DispatcherException::EXCEPTION_NO_DI);

        // Non-empty filters + null container → throwDispatchException at L943
        $dispatcher->getParameter('key', 'string');
    }
}
