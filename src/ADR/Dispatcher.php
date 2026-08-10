<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Based on the Action Domain Responder pattern
 * @link    https://pmjones.io/adr/
 */

declare(strict_types=1);

namespace Phalcon\ADR;

use Phalcon\ADR\Events\Event;
use Phalcon\ADR\Exceptions\NotAnAction;
use Phalcon\Contracts\ADR\Action;
use Phalcon\Contracts\ADR\ADRTypes;
use Phalcon\Contracts\ADR\Dispatcher as DispatcherInterface;
use Phalcon\Contracts\ADR\Middleware;
use Phalcon\Contracts\Container\Ioc\IocContainer;
use Phalcon\Contracts\Events\Manager;
use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Http\ResponseInterface;

/**
 * Resolves the Action (and middleware) through the container, wraps it in the
 * pipeline and runs it, firing the `pipeline:*` events. Global middleware is
 * resolved once and cached; only route middleware resolves per request.
 *
 * The container resolution is the one deliberate Service Locator: it uses the
 * resolve-only `IocContainer` contract, so a container swap is a two-method
 * adapter. Everything else is constructor-injected.
 *
 * @phpstan-import-type adr_middleware_names from ADRTypes
 */
final class Dispatcher implements DispatcherInterface
{
    /**
     * @var list<Middleware>|null
     */
    protected array | null $resolvedGlobal = null;

    /**
     * @phpstan-param adr_middleware_names $globalMiddleware
     */
    public function __construct(
        protected IocContainer $container,
        protected Manager $events,
        protected array $globalMiddleware = []
    ) {
    }

    /**
     * @phpstan-param class-string          $actionClass
     * @phpstan-param adr_middleware_names  $routeMiddleware
     */
    public function dispatch(
        string $actionClass,
        AttributeRequest $request,
        array $routeMiddleware = []
    ): ResponseInterface {
        $action = $this->container->getService($actionClass);
        if (!($action instanceof Action)) {
            throw new NotAnAction($actionClass);
        }

        $middleware = array_merge($this->resolveGlobal(), $this->resolveAll($routeMiddleware));
        $terminal   = new EventfulHandler($action, $this->events);
        $pipeline   = new Pipeline($middleware, $terminal);

        $this->events->fire(Event::PIPELINE_BEFORE_DISPATCH, $this, $request);

        $response = $pipeline->__invoke($request);

        $this->events->fire(Event::PIPELINE_AFTER_DISPATCH, $this, $response);

        return $response;
    }

    /**
     * @phpstan-param adr_middleware_names $classes
     *
     * @return list<Middleware>
     */
    protected function resolveAll(array $classes): array
    {
        $result = [];
        foreach ($classes as $className) {
            /** @var Middleware $middleware */
            $middleware = $this->container->getService($className);

            $result[] = $middleware;
        }

        return $result;
    }

    /**
     * @return list<Middleware>
     */
    protected function resolveGlobal(): array
    {
        if (null === $this->resolvedGlobal) {
            $this->resolvedGlobal = $this->resolveAll($this->globalMiddleware);
        }

        return $this->resolvedGlobal;
    }
}
