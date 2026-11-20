<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\ADR;

use Exception;
use Phalcon\ADR\Dispatcher;
use Phalcon\ADR\ErrorResponder;
use Phalcon\ADR\Router\AttributeFilter;
use Phalcon\Container\Container;
use Phalcon\Container\Exceptions\ServiceNotFound;
use Phalcon\Contracts\ADR\Action;
use Phalcon\Contracts\ADR\Payload\Payload;
use Phalcon\Contracts\ADR\Responder\Responder;
use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Events\Manager;
use Phalcon\Http\Request;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;
use Phalcon\Logger\Adapter\Noop;
use Phalcon\Logger\Logger;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use RuntimeException;

/**
 * cphalcon uses the PHP weak type rules for an int value that goes to a string
 * target, thus the int becomes a string.
 *
 * @issue https://github.com/phalcon/phalcon/issues/887
 */
final class WeakModeIntValuesTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testAdrDispatcherMiddlewareNameInt(): void
    {
        $action = new class () implements Action {
            public function __invoke(AttributeRequest $request): ResponseInterface
            {
                return new Response();
            }
        };

        $container = new Container();
        $container->set('SomeAction', $action);

        $dispatcher = new Dispatcher($container, new Manager(), [123]);

        $this->expectException(ServiceNotFound::class);
        $this->expectExceptionMessage("Service '123' not found");

        $dispatcher->dispatch('SomeAction', new Request());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testAdrErrorResponderExceptionMapAncestorInt(): void
    {
        $responder = $this->newErrorResponder([Exception::class => 404]);
        $response  = $responder->handle(
            new Request(),
            new Response(),
            new RuntimeException('boom')
        );

        $this->assertSame('404', $response->getContent());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testAdrErrorResponderExceptionMapInt(): void
    {
        $responder = $this->newErrorResponder([RuntimeException::class => 404]);
        $response  = $responder->handle(
            new Request(),
            new Response(),
            new RuntimeException('boom')
        );

        $this->assertSame('404', $response->getContent());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testAdrRouterAttributeFilterParamsTypeInt(): void
    {
        $action = new class () {
            /**
             * @return array<string, array<string, int>>
             */
            public static function params(): array
            {
                return ['id' => ['type' => 1]];
            }
        };

        $this->assertSame(
            ['id' => '5'],
            (new AttributeFilter())->filter($action::class, ['5'])
        );
    }

    /**
     * @param array<class-string, int> $exceptionMap
     */
    private function newErrorResponder(array $exceptionMap): ErrorResponder
    {
        $responder = new class () implements Responder {
            public function __invoke(
                RequestInterface $request,
                ResponseInterface $response,
                Payload $payload
            ): ResponseInterface {
                return $response->setContent((string) $payload->getStatus());
            }
        };

        return new ErrorResponder(
            $responder,
            new Logger('l', ['m' => new Noop()]),
            false,
            $exceptionMap
        );
    }
}
