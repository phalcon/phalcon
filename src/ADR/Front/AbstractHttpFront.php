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

namespace Phalcon\ADR\Front;

use Phalcon\ADR\Application;
use Phalcon\ADR\Container\AdrProvider;
use Phalcon\Container\Container;
use Phalcon\Contracts\ADR\Application as ApplicationInterface;
use Phalcon\Contracts\ADR\Emitter\Emitter;
use Phalcon\Contracts\Front\FrontController;
use Phalcon\Contracts\Http\AttributeRequest;
use Throwable;

/**
 * Boots a container, builds the Application, handles the request and emits the
 * response. Userland front controllers override `loadEnvironment()`,
 * `registerProviders()` and optionally `getApplication()`; bootstrap is
 * `exit((new AppFront(dirname(__DIR__)))->run());`.
 */
abstract class AbstractHttpFront implements FrontController
{
    /**
     * @var Container|null
     */
    protected ?Container $container = null;

    public function __construct(
        protected string $projectRoot
    ) {
    }

    /**
     * Builds the container, loads the environment and registers the providers,
     * returning the container for consumers that need it before (or instead
     * of) `run()`. The container is built once and cached, so calling `boot()`
     * and then `run()` reuses the same instance.
     */
    final public function boot(): Container
    {
        $container = $this->container;

        if (null === $container) {
            $container       = $this->buildContainer();
            $this->container = $container;

            $this->loadEnvironment($container);
            $this->registerProviders($container);
        }

        return $container;
    }

    /**
     * @return int<0,254>
     */
    final public function run(): int
    {
        try {
            $container = $this->boot();

            /** @var AttributeRequest $request */
            $request = $container->get(AttributeRequest::class);

            $application = $this->getApplication($container);
            $response    = $application->handle($request);

            /** @var Emitter $emitter */
            $emitter = $container->get(Emitter::class);

            $emitter->emit($response);

            return 0;
        } catch (Throwable $exception) {
            return $this->handleBootError($exception);
        }
    }

    protected function buildContainer(): Container
    {
        return new Container();
    }

    /**
     * Builds the Application the front will hand the request to. Override to
     * configure it (`setBaseNamespace()`/`secureWith()`) or to wire a different
     * `Phalcon\Contracts\ADR\Application` implementation.
     */
    protected function getApplication(Container $container): ApplicationInterface
    {
        return new Application($container);
    }

    /**
     * @return int<0,254>
     */
    protected function handleBootError(\Throwable $exception): int
    {
        error_log((string) $exception);

        if (!headers_sent()) {
            http_response_code(500);
            header("Content-Type: text/plain; charset=utf-8");
            echo "Internal Server Error\n";
        }

        return 1;
    }

    protected function loadEnvironment(Container $container): void
    {
    }

    protected function registerProviders(Container $container): void
    {
        (new AdrProvider())->provide($container);
    }
}
