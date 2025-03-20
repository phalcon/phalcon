<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with $this source code.
 */

declare(strict_types=1);

namespace Phalcon\Mvc;

use ArrayAccess;
use Closure;
use Phalcon\Cache\Adapter\AdapterInterface;
use Phalcon\Di\DiInterface;
use Phalcon\Di\FactoryDefault;
use Phalcon\Di\Injectable;
use Phalcon\Di\ServiceInterface;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\Exception as EventsException;
use Phalcon\Events\Traits\EventsAwareTrait;
use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\Micro\CollectionInterface;
use Phalcon\Mvc\Micro\Exception;
use Phalcon\Mvc\Micro\LazyLoader;
use Phalcon\Mvc\Micro\MiddlewareInterface;
use Phalcon\Mvc\Model\BinderInterface;
use Phalcon\Mvc\Router\RouteInterface;
use Throwable;

use function is_array;
use function is_object;
use function is_string;

/**
 * With Phalcon, you can create "Micro-Framework like" applications. By doing
 * this, you only need to write a minimal amount of code to create a PHP
 * application. Micro applications are suitable to small applications, APIs and
 * prototypes in a practical way.
 *
 *```php
 * $app = new \Phalcon\Mvc\Micro();
 *
 * $app->get(
 *     "/say/welcome/{name}",
 *     function ($name) {
 *         echo "<h1>Welcome $name!</h1>";
 *     }
 * );
 *
 * $app->handle("/say/welcome/Phalcon");
 *```
 */
class Micro extends Injectable implements ArrayAccess, EventsAwareInterface
{
    use EventsAwareTrait;

    /**
     * @var callable|null
     */
    protected mixed $activeHandler = null;

    /**
     * @var array
     */
    protected array $afterBindingHandlers = [];

    /**
     * @var array
     */
    protected array $afterHandlers = [];

    /**
     * @var array
     */
    protected array $beforeHandlers = [];

    /**
     * @var callable|null
     */
    protected mixed $errorHandler = null;

    /**
     * @var array
     */
    protected array $finishHandlers = [];

    /**
     * @var array
     */
    protected array $handlers = [];

    /**
     * @var BinderInterface|null
     */
    protected BinderInterface | null $modelBinder = null;

    /**
     * @var callable|null
     */
    protected mixed $notFoundHandler = null;

    /**
     * @var callable|null
     */
    protected mixed $responseHandler = null;

    /**
     * @var mixed|null
     */
    protected mixed $returnedValue = null;

    /**
     * @var RouterInterface|null
     */
    protected RouterInterface | null $router = null;

    /**
     * @var bool
     */
    protected bool $stopped = false;

    /**
     * Phalcon\Mvc\Micro constructor
     */
    public function __construct(DiInterface | null $container = null)
    {
        if (null !== $container) {
            $this->setDi($container);
        }
    }

    /**
     * Appends an 'after' middleware to be called after execute the route
     *
     * @param callable|MiddlewareInterface $handler
     *
     * @return $this
     */
    public function after(callable | MiddlewareInterface $handler): Micro
    {
        $this->afterHandlers[] = $handler;

        return $this;
    }

    /**
     * Appends a afterBinding middleware to be called after model binding
     *
     * @param callable|MiddlewareInterface $handler
     *
     * @return $this
     */
    public function afterBinding(callable | MiddlewareInterface $handler): Micro
    {
        $this->afterBindingHandlers[] = $handler;

        return $this;
    }

    /**
     * Appends a before middleware to be called before execute the route
     *
     * @param callable|MiddlewareInterface $handler
     *
     * @return $this
     */
    public function before(callable | MiddlewareInterface $handler): Micro
    {
        $this->beforeHandlers[] = $handler;

        return $this;
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is DELETE
     *
     * @param string         $routePattern
     * @param array|callable $handler
     *
     * @return RouteInterface
     */
    public function delete(string $routePattern, array | callable $handler): RouteInterface
    {
        return $this->addRoute('addDelete', $routePattern, $handler);
    }

    /**
     * Sets a handler that will be called when an exception is thrown handling
     * the route
     *
     * @param callable $handler
     *
     * @return $this
     */
    public function error(callable $handler): Micro
    {
        $this->errorHandler = $handler;

        return $this;
    }

    /**
     * Appends a 'finish' middleware to be called when the request is finished
     *
     * @param callable $handler
     *
     * @return $this
     */
    public function finish(callable $handler): Micro
    {
        $this->finishHandlers[] = $handler;

        return $this;
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is GET
     *
     * @param string         $routePattern
     * @param array|callable $handler
     *
     * @return RouteInterface
     */
    public function get(string $routePattern, array | callable $handler): RouteInterface
    {
        return $this->addRoute('addGet', $routePattern, $handler);
    }

    /**
     * Return the handler that will be called for the matched route
     *
     * @return callable|null
     */
    public function getActiveHandler(): mixed
    {
        return $this->activeHandler;
    }

    /**
     * Returns bound models from binder instance
     *
     * @return array
     */
    public function getBoundModels(): array
    {
        if (null === $this->modelBinder) {
            return [];
        }

        return $this->modelBinder->getBoundModels();
    }

    /**
     * Returns the internal handlers attached to the application
     *
     * @return array
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }

    /**
     * Gets model binder
     *
     * @return BinderInterface|null
     */
    public function getModelBinder(): BinderInterface | null
    {
        return $this->modelBinder;
    }

    /**
     * Returns the value returned by the executed handler
     *
     * @return mixed
     */
    public function getReturnedValue(): mixed
    {
        return $this->returnedValue;
    }

    /**
     * Returns the internal router used by the application
     *
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface
    {
        if (null === $this->router) {
            $this->router = $this->getSharedService("router");

            /**
             * Clear the set routes if any
             */
            $this->router->clear();

            /**
             * Automatically remove extra slashes
             */
            $this->router->removeExtraSlashes(true);
        }

        return $this->router;
    }

    /**
     * Obtains a service from the DI
     *
     * @return object
     */
    public function getService(string $serviceName)
    {
        $this->checkDiContainer();

        return $this->container->get($serviceName);
    }

    /**
     * Obtains a shared service from the DI
     *
     * @return mixed
     */
    public function getSharedService(string $serviceName)
    {
        $this->checkDiContainer();

        return $this->container->getShared($serviceName);
    }

    /**
     * Handle the whole request
     *
     * @param string $uri
     *
     * @return mixed
     * @throws Exception
     * @throws Throwable
     * @throws EventsException
     */
    public function handle(string $uri): mixed
    {
        $realHandler = null;

        $this->checkContainer(
            Exception::class,
            'micro services'
        );

        try {
            $returnedValue = null;

            /**
             * Calling beforeHandle routing
             */
            if (false === $this->fireManagerEvent("micro:beforeHandleRoute")) {
                return false;
            }

            /**
             * Handling routing information
             */
            /** @var Router $router */
            $router = $this->container->getShared("router");

            /**
             * Handle the URI as normal
             */
            $router->handle($uri);

            /**
             * Check if one route was matched
             */
            $matchedRoute = $router->getMatchedRoute();

            if (null !== $matchedRoute) {
                if (!isset($this->handlers[$matchedRoute->getRouteId()])) {
                    throw new Exception(
                        "Matched route does not have an associated handler"
                    );
                }

                /**
                 * Updating active handler
                 */
                $handler = $this->handlers[$matchedRoute->getRouteId()];
                if (null !== $this->eventsManager) {
                    /**
                     * Calling beforeExecuteRoute event
                     */
                    if (true !== $this->fireManagerEvent("micro:beforeExecuteRoute")) {
                        return false;
                    }

                    $handler = $this->activeHandler;
                }

                $this->activeHandler = $handler;
                $this->stopped       = false;

                /**
                 * Calls the before handlers
                 */
                foreach ($this->beforeHandlers as $before) {
                    if ($before instanceof MiddlewareInterface) {
                        /**
                         * Call the middleware
                         */
                        $status = $before->call($this);
                    } else {
                        if (!is_callable($before)) {
                            throw new Exception(
                                "'before' handler is not callable"
                            );
                        }

                        /**
                         * Call the before handler
                         */
                        $status = call_user_func($before);
                    }

                    /**
                     * Return the status if the middleware was stopped
                     */
                    if (true === $this->stopped) {
                        return $status;
                    }
                }

                $params = $router->getParams();

                /**
                 * Bound the app to the handler
                 */
                if ($handler instanceof Closure) {
                    $handler = Closure::bind($handler, $this);

                    if (null !== $this->modelBinder) {
                        $routeName = $matchedRoute->getName();

                        if (null !== $routeName) {
                            $bindCacheKey = "_PHMB_" . $routeName;
                        } else {
                            $bindCacheKey = "_PHMB_" . $matchedRoute->getPattern();
                        }

                        $params = $this->modelBinder->bindToHandler(
                            $handler,
                            $params,
                            $bindCacheKey
                        );
                    }
                }

                /**
                 * Calling the Handler in the PHP userland
                 */
                if (is_array($handler)) {
                    $realHandler = $handler[0];

                    if (
                        $realHandler instanceof Controller &&
                        null !== $this->modelBinder
                    ) {
                        $methodName   = $handler[1];
                        $bindCacheKey = "_PHMB_" . get_class($realHandler) . "_" . $methodName;

                        $params = $this->modelBinder->bindToHandler(
                            $realHandler,
                            $params,
                            $bindCacheKey,
                            $methodName
                        );
                    }
                }

                /**
                 * Instead of double call_user_func_array when lazy loading we
                 * will just call method
                 */
                if ($realHandler instanceof LazyLoader) {
                    $methodName = $handler[1];

                    $lazyReturned = $realHandler->callMethod(
                        $methodName,
                        $params,
                        $this->modelBinder
                    );
                } else {
                    $lazyReturned = call_user_func_array($handler, $params);
                }

                /**
                 * There is seg fault if we try set directly value of method
                 * to returnedValue
                 */
                $returnedValue = $lazyReturned;

                /**
                 * Calling afterBinding event
                 */
                if (false === $this->fireManagerEvent("micro:afterBinding")) {
                    return false;
                }

                $this->stopped = false;

                /**
                 * Calls the after binding handlers
                 */
                foreach ($this->afterBindingHandlers as $afterBinding) {
                    if ($afterBinding instanceof MiddlewareInterface) {
                        /**
                         * Call the middleware
                         */
                        $status = $afterBinding->call($this);
                    } else {
                        if (!is_callable($afterBinding)) {
                            throw new Exception(
                                "'afterBinding' handler is not callable"
                            );
                        }

                        /**
                         * Call the afterBinding handler
                         */
                        $status = call_user_func($afterBinding);
                    }

                    /**
                     * Return the status if the middleware was stopped
                     */
                    if (true === $this->stopped) {
                        return $status;
                    }
                }

                /**
                 * Update the returned value
                 */
                $this->returnedValue = $returnedValue;

                /**
                 * Calling afterExecuteRoute event
                 */
                $this->fireManagerEvent("micro:afterExecuteRoute");

                $this->stopped = false;

                /**
                 * Calls the after handlers
                 */
                foreach ($this->afterHandlers as $after) {
                    if ($after instanceof MiddlewareInterface) {
                        /**
                         * Call the middleware
                         */
                        $status = $after->call($this);
                    } else {
                        if (!is_callable($after)) {
                            throw new Exception(
                                "One of the 'after' handlers is not callable"
                            );
                        }

                        $status = call_user_func($after);
                    }

                    /**
                     * break the execution if the middleware was stopped
                     */
                    if (true === $this->stopped) {
                        break;
                    }
                }
            } else {
                /**
                 * Calling beforeNotFound event
                 */
                if (false === $this->fireManagerEvent("micro:beforeNotFound")) {
                    return false;
                }

                /**
                 * Check if a notfoundhandler is defined and it's callable
                 */
                if (!is_callable($this->notFoundHandler)) {
                    throw new Exception(
                        "Not-Found handler is not callable or is not defined"
                    );
                }

                /**
                 * Call the Not-Found handler
                 */
                $returnedValue = call_user_func($this->notFoundHandler);
            }

            /**
             * Calling afterHandleRoute event
             */
            $this->fireManagerEvent("micro:afterHandleRoute", $returnedValue);

            $this->stopped = false;

            /**
             * Calls the finish handlers
             */
            foreach ($this->finishHandlers as $finish) {
                /**
                 * Try to execute middleware as plugins
                 */
                if ($finish instanceof MiddlewareInterface) {
                    /**
                     * Call the middleware
                     */
                    $status = $finish->call($this);
                } else {
                    if (!is_callable($finish)) {
                        throw new Exception(
                            "One of the 'finish' handlers is not callable"
                        );
                    }

                    /**
                     * Call the 'finish' middleware
                     */
                    $status = call_user_func_array(
                        $finish,
                        [$this]
                    );
                }

                /**
                 * break the execution if the middleware was stopped
                 */
                if (true === $this->stopped) {
                    break;
                }
            }
        } catch (Throwable $ex) {
            /**
             * Calling beforeException event
             */
            $this->fireManagerEvent("micro:beforeException", $ex);

            /**
             * Check if an errorhandler is defined and it's callable
             */
            if (null !== $this->errorHandler) {
                /**
                 * Call the Error handler
                 */
                $returnedValue = call_user_func_array(
                    $this->errorHandler,
                    [$ex]
                );

                if (is_object($returnedValue) && !($returnedValue instanceof ResponseInterface)) {
                    throw $ex;
                }

                if (false !== $returnedValue) {
                    throw $ex;
                }
            } elseif (false !== $returnedValue) {
                throw $ex;
            }
        }

        /**
         * Check if a response handler is defined, else use default response
         * handler
         */
        if (null !== $this->responseHandler) {
            if (!is_callable($this->responseHandler)) {
                throw new Exception(
                    "Response handler is not callable or is not defined"
                );
            }

            $returnedValue = call_user_func($this->responseHandler);
        } else {
            /**
             * Check if the returned value is a string and take it as response
             * body
             */
            if (is_string($returnedValue)) {
                $response = $this->container->getShared("response");

                if (true !== $response->isSent()) {
                    $response->setContent($returnedValue);
                    $response->send();
                }
            }

            /**
             * Check if the returned object is already a response
             */
            if (
                $returnedValue instanceof ResponseInterface &&
                true !== $returnedValue->isSent()
            ) {
                $returnedValue->send();
            }
        }

        return $returnedValue;
    }

    /**
     * Checks if a service is registered in the DI
     *
     * @param string $serviceName
     *
     * @return bool
     */
    public function hasService(string $serviceName): bool
    {
        $this->checkDiContainer();

        return $this->container->has($serviceName);
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is HEAD
     *
     * @param string         $routePattern
     * @param array|callable $handler
     *
     * @return RouteInterface
     */
    public function head(string $routePattern, array | callable $handler): RouteInterface
    {
        return $this->addRoute('addHead', $routePattern, $handler);
    }

    /**
     * Maps a route to a handler without any HTTP method constraint
     *
     * @param string         $routePattern
     * @param array|callable $handler
     *
     * @return RouteInterface
     */
    public function map(string $routePattern, array | callable $handler): RouteInterface
    {
        return $this->addRoute('add', $routePattern, $handler);
    }

    /**
     * Mounts a collection of handlers
     *
     * @param CollectionInterface $collection
     *
     * @return $this
     * @throws Exception
     */
    public function mount(CollectionInterface $collection): Micro
    {
        /**
         * Get the main handler
         */
        $mainHandler = $collection->getHandler();

        if (empty($mainHandler)) {
            throw new Exception("Collection requires a main handler");
        }

        $handlers = $collection->getHandlers();

        if (empty($handlers)) {
            throw new Exception("There are no handlers to mount");
        }

        /**
         * Check if handler is lazy
         */
        if (true === $collection->isLazy()) {
            $lazyHandler = new LazyLoader($mainHandler);
        } else {
            $lazyHandler = $mainHandler;
        }

        /**
         * Get the main prefix for the collection
         */
        $prefix = $collection->getPrefix();

        foreach ($handlers as $handler) {
            if (!is_array($handler)) {
                throw new Exception(
                    "One of the registered handlers is invalid"
                );
            }

            $methods    = $handler[0];
            $pattern    = $handler[1];
            $subHandler = $handler[2];
            $name       = $handler[3];

            /**
             * Create a real handler
             */
            $realHandler     = [$lazyHandler, $subHandler];
            $prefixedPattern = $pattern;

            if (!empty($prefix)) {
                $prefixedPattern = $prefix . $pattern;
                if ($pattern === "/") {
                    $prefixedPattern = $prefix;
                }
            }

            /**
             * Map the route manually
             */
            $route = $this->map($prefixedPattern, $realHandler);

            if (
                (is_string($methods) && !empty($methods)) ||
                is_array($methods)
            ) {
                $route->via($methods);
            }

            if (is_string($name)) {
                $route->setName($name);
            }
        }

        return $this;
    }

    /**
     * Sets a handler that will be called when the router doesn't match any of
     * the defined routes
     *
     * @param callable $handler
     *
     * @return $this
     */
    public function notFound(callable $handler): Micro
    {
        $this->notFoundHandler = $handler;

        return $this;
    }

    /**
     * Check if a service is registered in the internal services container using
     * the array syntax
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->hasService($offset);
    }

    /**
     * Allows to obtain a shared service in the internal services container
     * using the array syntax
     *
     *```php
     * var_dump(
     *     $app["request"]
     * );
     *```
     *
     * @param mixed $offset
     *
     * @return object
     */
    public function offsetGet(mixed $offset): object
    {
        return $this->getService($offset);
    }

    /**
     * Allows to register a shared service in the internal services container
     * using the array syntax
     *
     *```php
     *    $app["request"] = new \Phalcon\Http\Request();
     *```
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->setService($offset, $value);
    }

    /**
     * Removes a service from the internal services container using the array
     * syntax
     *
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->checkDiContainer();

        $this->container->remove($offset);
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is OPTIONS
     *
     * @param string         $routePattern
     * @param array|callable $handler
     *
     * @return RouteInterface
     */
    public function options(string $routePattern, array | callable $handler): RouteInterface
    {
        return $this->addRoute('addOptions', $routePattern, $handler);
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is PATCH
     *
     * @param string         $routePattern
     * @param array|callable $handler
     *
     * @return RouteInterface
     */
    public function patch(string $routePattern, array | callable $handler): RouteInterface
    {
        return $this->addRoute('addPatch', $routePattern, $handler);
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is POST
     *
     * @param string         $routePattern
     * @param array|callable $handler
     *
     * @return RouteInterface
     */
    public function post(string $routePattern, array | callable $handler): RouteInterface
    {
        return $this->addRoute('addPost', $routePattern, $handler);
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is PUT
     *
     * @param string         $routePattern
     * @param array|callable $handler
     *
     * @return RouteInterface
     */
    public function put(string $routePattern, array | callable $handler): RouteInterface
    {
        return $this->addRoute('addPut', $routePattern, $handler);
    }

    /**
     * Sets externally the handler that must be called by the matched route
     *
     * @param callable $activeHandler
     *
     * @return Micro
     */
    public function setActiveHandler(callable $activeHandler): Micro
    {
        $this->activeHandler = $activeHandler;

        return $this;
    }

    /**
     * Sets model binder
     *
     * ```php
     * $micro = new Micro($di);
     *
     * $micro->setModelBinder(
     *     new Binder(),
     *     'cache'
     * );
     * ```
     *
     * @param BinderInterface              $modelBinder
     * @param AdapterInterface|string|null $cache
     *
     * @return $this
     */
    public function setModelBinder(
        BinderInterface $modelBinder,
        AdapterInterface | string | null $cache = null
    ): Micro {
        if (is_string($cache)) {
            $cache = $this->getService($cache);
        }

        if ($cache instanceof AdapterInterface) {
            $modelBinder->setCache($cache);
        }

        $this->modelBinder = $modelBinder;

        return $this;
    }

    /**
     * Appends a custom 'response' handler to be called instead of the default
     * response handler
     *
     * @param callable $handler
     *
     * @return $this
     */
    public function setResponseHandler(callable $handler): Micro
    {
        $this->responseHandler = $handler;

        return $this;
    }

    /**
     * Sets a service from the DI
     *
     * @param string $serviceName
     * @param mixed  $definition
     * @param bool   $isShared
     *
     * @return ServiceInterface
     */
    public function setService(
        string $serviceName,
        mixed $definition,
        bool $isShared = false
    ): ServiceInterface {
        $this->checkDiContainer();

        return $this->container->set($serviceName, $definition, $isShared);
    }

    /**
     * Stops the middleware execution avoiding than other middlewares be
     * executed
     */
    public function stop(): void
    {
        $this->stopped = true;
    }

    /**
     * Helper method to route an action
     *
     * @param string         $method
     * @param string         $routePattern
     * @param array|callable $handler
     *
     * @return RouteInterface
     */
    private function addRoute(
        string $method,
        string $routePattern,
        array | callable $handler
    ): RouteInterface {
        /**
         * We create a router even if there is no one in the DI
         */
        $router = $this->getRouter();

        /**
         * Routes are added to the router
         */
        $route = $router->$method($routePattern);

        /**
         * Using the id produced by the router we store the handler
         */
        $this->handlers[$route->getRouteId()] = $handler;

        /**
         * The route is returned, the developer can add more things on it
         */
        return $route;
    }

    /**
     * @return void
     */
    private function checkDiContainer(): void
    {
        if (null === $this->container) {
            $this->container = new FactoryDefault();
        }
    }
}
