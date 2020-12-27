<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * ($c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license $information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Mvc;

use ArrayAccess;
use Closure;
use Phalcon\Di\DiInterface;
use Phalcon\Di\Injectable;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro\Exception;
use Phalcon\Di\ServiceInterface;

use Phalcon\Mvc\Micro\LazyLoader;
use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\Model\BinderInterface;
use Phalcon\Mvc\Router\RouteInterface;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\ManagerInterface;
use Phalcon\Mvc\Micro\MiddlewareInterface;
use Phalcon\Mvc\Micro\CollectionInterface;
use Throwable;

/**
 * Phalcon\Mvc\Micro
 *
 * With Phalcon you can create "Micro-Framework like" applications. By doing
 * $this, you only need to write a minimal amount of code to create a PHP
 * application. Micro applications are suitable to small $applications, APIs and
 * prototypes in a practical way.
 *
 *```php
 * $app = new \Phalcon\Mvc\Micro();
 *
 * $app->get(
 *     "/say/welcome/{name}",
 *     function ($name) {
 *         echo "h1Welcome $name!</h1>";
 *     }
 * );
 *
 * $app->handle("/say/welcome/Phalcon");
 *```
 */
class Micro extends Injectable implements  ArrayAccess, EventsAwareInterface
{
    protected $activeHandler;

    protected array $afterBindingHandlers = [];

    protected array $afterHandlers = [];

    protected array $beforeHandlers = [];

    protected $container;

    protected $errorHandler;

    protected ?ManagerInterface $eventsManager;

    protected array $finishHandlers = [];

    protected array $handlers = [];

    protected $modelBinder;

    protected $notFoundHandler;

    protected $responseHandler;

    protected $returnedValue;

    protected ?RouterInterface $router;

    protected $stopped;

    /**
     * Phalcon\Mvc\Micro constructor
     */
    public function __construct( ?DiInterface $container = null)
    {
        if ($container !== null) {
            $this->setDi($container);
        }
    }

    /**
     * Appends an 'after' middleware to be called after execute the route
     *
     * @param callable handler
     */
    public function after($handler): Micro
    {
        $this->afterHandlers[] = $handler;

        return this;
    }

    /**
     * Appends a afterBinding middleware to be called after model binding
     *
     * @param callable handler
     */
    public function afterBinding($handler): Micro
    {
        $this->afterBindingHandlers[] = $handler;

        return this;
    }

    /**
     * Appends a before middleware to be called before execute the route
     *
     * @param callable handler
     */
    public function before($handler): Micro
    {
        $this->beforeHandlers[] = $handler;

        return this;
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is DELETE
     *
     * @param callable handler
     */
    public function delete(string $routePattern, $handler): RouteInterface
    {
         /**
         * We create a router even if there is no one in the DI
         */
        $router = $this->getRouter();

        /**
         * Routes are added to the router restricting to DELETE
         */
        $route = $router->addDelete($routePattern);

        /**
         * Using the id produced by the router we store the handler
         */
        $this->handlers[$route->getRouteId()] = $handler;

        /**
         * The route is $returned, the developer can add more things on it
         */
        return route;
    }

    /**
     * Sets a handler that will be called when an exception is thrown handling
     * the route
     *
     * @param callable handler
     */
    public function error($handler): Micro
    {
        $this->errorHandler = $handler;

        return this;
    }

    /**
     * Appends a 'finish' middleware to be called when the request is finished
     *
     * @param callable handler
     */
    public function finish($handler): Micro
    {
        $this->finishHandlers[] = $handler;

        return this;
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is GET
     *
     * @param callable handler
     */
    public function get(string $routePattern, $handler): RouteInterface
    {
        

        /**
         * We create a router even if there is no one in the DI
         */
        $router = $this->getRouter();

        /**
         * Routes are added to the router restricting to GET
         */
        $route = $router->addGet($routePattern);

        /**
         * Using the id produced by the router we store the $handler
         */
        $this->handlers[$route->getRouteId()] = $handler;

        /**
         * The route is $returned, the developer can add more things on it
         */
        return route;
    }

    /**
     * Return the handler that will be called for the matched route
     *
     * @return callable
     */
    public function getActiveHandler()
    {
        return $this->activeHandler;
    }

    /**
     * Returns bound models from binder instance
     */
    public function getBoundModels(): array
    {
        

        $modelBinder = $this->modelBinder;

        if($modelBinder == null){
            return [];
        }

        return $modelBinder->getBoundModels();
    }

    /**
     * Returns the internal event manager
     */
    public function getEventsManager(): ManagerInterface | null
    {
        return $this->eventsManager;
    }

    /**
     * Sets the events manager
     */
    public function setEventsManager(ManagerInterface $eventsManager): void
    {
        $this->eventsManager = $eventsManager;
    }

    /**
     * Returns the internal handlers attached to the application
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }

    /**
     * Gets model binder
     */
    public function getModelBinder(): BinderInterface|null
    {
        return $this->modelBinder;
    }

    /**
     * Returns the value returned by the executed handler
     *
     * @return mixed
     */
    public function getReturnedValue()
    {
        return $this->returnedValue;
    }

    /**
     * Returns the internal router used by the application
     */
    public function getRouter(): RouterInterface
    {
        

        $router = $this->router;

        if(!is_object($router)){
            $router = $this->getSharedService("router");

            /**
             * Clear the set routes if any
             */
            $router->clear();

            /**
             * Automatically remove extra slashes
             */
            $router->removeExtraSlashes(true);

            /**
             * Update the internal router
             */
            $this->router = router;
        }

        return router;
    }

    /**
     * Obtains a service from the DI
     *
     * @return object
     */
    public function getService(string $serviceName)
    {
        

        $container = $this->container;

        if(!is_object($container)){
            $container = new FactoryDefault();

            $this->container = container;
        }

        return $container->get($serviceName);
    }

    /**
     * Obtains a shared service from the DI
     *
     * @return mixed
     */
    public function getSharedService(string $serviceName)
    {
        

        $container = $this->container;

        if(!is_object($container)){
            $container = new FactoryDefault();

            $this->container = container;
        }

        return $container->getShared($serviceName);
    }

    /**
     * Handle the whole request
     *
     * @param string uri
     * @return mixed
     */
    public function handle(string $uri)
    {
        $realHandler = null;
        $container = $this->container;

        if(!is_object($container)){
            throw new Exception(
                Exception::containerServiceNotFound("micro services")
            );
        }

        try {
            $returnedValue = null;

            /**
             * Calling beforeHandle routing
             */
            $eventsManager = $this->eventsManager;

            if($eventsManager !== null){
                if($eventsManager->fire("micro:beforeHandleRoute", $this) === false){
                    return false;
                }
            }

            /**
             * Handling routing information
             */
            $router = ($RouterInterface) ($container->getShared("router"));

            /**
             * Handle the URI as normal
             */
            $router->handle($uri);

            /**
             * Check if one route was matched
             */
            $matchedRoute = $router->getMatchedRoute();

            if(is_object($matchedRoute)){
                $handler = $this->handlers[$matchedRoute->getRouteId()] ?? null;
                if($handler === null){
                    throw new Exception(
                        "Matched route doesn't have an associated handler"
                    );
                }

                /**
                 * Updating active handler
                 */
                $this->activeHandler = $handler;

                /**
                 * Calling beforeExecuteRoute event
                 */
                if(is_object($eventsManager)){
                    if($eventsManager->fire("micro:beforeExecuteRoute", $this) === false){
                        return false;
                    }

                    $handler = $this->activeHandler;
                }

                $beforeHandlers = $this->beforeHandlers;

                $this->stopped = false;

                /**
                 * Calls the before handlers
                 */
                foreach($beforeHandlers as $before){
                    if(is_object($before) && before instanceof $MiddlewareInterface){
                        /**
                         * Call the middleware
                         */
                        $status = $before->call($this);
                    } else {
                        if(!is_callable($before)){
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
                    if($this->stopped){
                        return status;
                    }
                }

                $params = $router->getParams();

                $modelBinder = $this->modelBinder;

                /**
                 * Bound the app to the handler
                 */
                if(is_object($handler) && $handler instanceof $Closure){
                    $handler = Closure::bind($handler, $this);

                    if($modelBinder != null){
                        $routeName = $matchedRoute->getName();

                        if($routeName != null){
                            $bindCacheKey = "_PHMB_" . routeName;
                        } else {
                            $bindCacheKey = "_PHMB_" . $matchedRoute->getPattern();
                        }

                        $params = $modelBinder->bindToHandler(
                            $handler,
                            $params,
                            bindCacheKey
                        );
                    }
                }

                /**
                 * Calling the Handler in the PHP userland
                 */
                if($is_array($handler)){
                    $realHandler = $handler[0];

                    if($realHandler instanceof Controller && $modelBinder != null){
                        $methodName = $handler[1];
                        $bindCacheKey = "_PHMB_" . get_class($realHandler) . "_" . methodName;

                        $params = $modelBinder->bindToHandler(
                            $realHandler,
                            $params,
                            $bindCacheKey,
                            methodName
                        );
                    }
                }

                /**
                 * Instead of double call_user_func_array when lazy loading we
                 * will just call method
                 */
                if($realHandler != null && $realHandler instanceof $LazyLoader){
                    $methodName = $handler[1];

                    $lazyReturned = $realHandler->callMethod(
                        $methodName,
                        $params,
                        $modelBinder
                    );
                } else {
                    $lazyReturned = call_user_func_array($handler, $params);
                }

                /**
                 * There is seg fault if we try set directly value of method
                 * to returnedValue
                 */
                $returnedValue = lazyReturned;

                /**
                 * Calling afterBinding event
                 */
                if(is_object($eventsManager)){
                    if($eventsManager->fire("micro:afterBinding", $this) === false){
                        return false;
                    }
                }

                $afterBindingHandlers = $this->afterBindingHandlers;

                $this->stopped = false;

                /**
                 * Calls the after binding handlers
                 */
                foreach($afterBindingHandlers as $afterBinding) {
                    if(is_object($afterBinding) && $afterBinding instanceof MiddlewareInterface){
                        /**
                         * Call the middleware
                         */
                        $status = $afterBinding->call($this);
                    } else {
                        if(!is_callable($afterBinding)){
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
                    if($this->stopped){
                        return $status;
                    }
                }

                /**
                 * Update the returned value
                 */
                $this->returnedValue = returnedValue;

                /**
                 * Calling afterExecuteRoute event
                 */
                if(is_object($eventsManager)){
                    $eventsManager->fire("micro:afterExecuteRoute", $this);
                }

                $afterHandlers = $this->afterHandlers;

                $this->stopped = false;

                /**
                 * Calls the after handlers
                 */
                foreach($afterHandlers as $after){
                    if(is_object($after) && $after instanceof MiddlewareInterface){
                        /**
                         * Call the middleware
                         */
                        $status = $after->call($this);
                    } else {
                        if(!is_callable($after)){
                            throw new Exception(
                                "One of the 'after' handlers is not callable"
                            );
                        }

                        $status = call_user_func($after);
                    }

                    /**
                     * break the execution if the middleware was stopped
                     */
                    if($this->stopped){
                        break;
                    }
                }
            } else {
                /**
                 * Calling beforeNotFound event
                 */
                $eventsManager = $this->eventsManager;

                if(is_object($eventsManager)){
                    if($eventsManager->fire("micro:beforeNotFound", $this) === false){
                        return false;
                    }
                }

                /**
                 * Check if a notfoundhandler is defined and it's callable
                 */
                $notFoundHandler = $this->notFoundHandler;

                if(!is_callable($notFoundHandler)){
                    throw new Exception(
                        "Not-Found handler is not callable or is not defined"
                    );
                }

                /**
                 * Call the Not-Found handler
                 */
                $returnedValue = call_user_func($notFoundHandler);
            }

            /**
             * Calling afterHandleRoute event
             */
            if(is_object($eventsManager)){
                $eventsManager->fire("micro:afterHandleRoute", $this, $returnedValue);
            }

            $finishHandlers = $this->finishHandlers;

            $this->stopped = false;

            /**
             * Calls the finish handlers
             */
            foreach($finishHandlers as $finish){
                /**
                 * Try to execute middleware as plugins
                 */
                if(is_object($finish) && $finish instanceof MiddlewareInterface){
                    /**
                     * Call the middleware
                     */
                    $status = $finish->call($this);
                } else {
                    if(!is_callable($finish)){
                        throw new Exception(
                            "One of the 'finish' handlers is not callable"
                        );
                    }

                    /**
                     * Call the 'finish' middleware
                     */
                    $status = call_user_func_array(
                        $finish,
                        [this]
                    );
                }

                /**
                 * break the execution if the middleware was stopped
                 */
                if($this->stopped){
                    break;
                }
            }
        } 
        catch (Throwable $e) {
            /**
             * Calling beforeNotFound event
             */
            $eventsManager = $this->eventsManager;

            if(is_object($eventsManager)){
                $returnedValue = $eventsManager->fire(
                    "micro:beforeException",
                    $this,
                    $e
                );
            }

            /**
             * Check if an errorhandler is defined and it's callable
             */
            $errorHandler = $this->errorHandler;

            if($errorHandler){
                if(!is_callable($errorHandler)){
                    throw new Exception("Error handler is not callable");
                }

                /**
                 * Call the Error handler
                 */
                $returnedValue = call_user_func_array(
                    $errorHandler,
                    [e]
                );

                if(is_object($returnedValue)){
                    if(!($returnedValue instanceof $ResponseInterface)){
                        throw e;
                    }
                } else {
                    if($returnedValue !== false){
                        throw e;
                    }
                }
            } else {
                if($returnedValue !== false){
                    throw e;
                }
            }
        }

        /**
         * Check if a response handler is $defined, else use default response
         * handler
         */
        if($this->responseHandler){
            if(!is_callable($this->responseHandler)){
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
            if(is_string($returnedValue)){
                $response = (ResponseInterface)($container->getShared("response"));

                if(!$response->isSent()){
                    $response->setContent($returnedValue);
                    $response->send();
                }
            }

            /**
             * Check if the returned object is already a response
             */
            if(is_object($returnedValue) && $returnedValue instanceof ResponseInterface){
                if(!$returnedValue->isSent()){
                    $returnedValue->send();
                }
            }
        }

        return $returnedValue;
    }

    /**
     * Checks if a service is registered in the DI
     */
    public function hasService(string $serviceName): bool
    {
        

        $container = $this->container;

        if(!is_object($container)){
            $container = new FactoryDefault();

            $this->container = container;
        }

        return $container->has($serviceName);
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is HEAD
     *
     * @param callable handler
     */
    public function head(string $routePattern, $handler): RouteInterface
    {
        

        /**
         * We create a router even if there is no one in the DI
         */
        $router = $this->getRouter();

        /**
         * Routes are added to the router restricting to HEAD
         */
        $route = $router->addHead($routePattern);

        /**
         * Using the id produced by the router we store the handler
         */
        $this->handlers[$route->getRouteId()] = $handler;

        /**
         * The route is $returned, the developer can add more things on it
         */
        return route;
    }

    /**
     * Maps a route to a handler without any HTTP method constraint
     *
     * @param callable handler
     */
    public function map(string $routePattern, $handler): RouteInterface
    {
        

        /**
         * We create a router even if there is no one in the DI
         */
        $router = $this->getRouter();

        /**
         * Routes are added to the router
         */
        $route = $router->add($routePattern);

        /**
         * Using the id produced by the router we store the handler
         */
        $this->handlers[$route->getRouteId()] = $handler;

        /**
         * The route is $returned, the developer can add more things on it
         */
        return route;
    }

    /**
     * Mounts a collection of handlers
     */
    public function mount(CollectionInterface $collection): Micro
    {

        /**
         * Get the main handler
         */
        $mainHandler = $collection->getHandler();

        if(empty($mainHandler)){
            throw new Exception("Collection requires a main handler");
        }

        $handlers = $collection->getHandlers();

        if(empty($handlers)){
            throw new Exception("There are no handlers to mount");
        }

        /**
         * Check if handler is lazy
         */
        if($collection->isLazy()){
            $lazyHandler = new LazyLoader($mainHandler);
        } else {
            $lazyHandler = mainHandler;
        }

        /**
         * Get the main prefix for the collection
         */
        $prefix = $collection->getPrefix();

        foreach($handlers as $handler) {
            if(!is_array($handler)){
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
            $realHandler = [$lazyHandler, $subHandler];

            if(!empty($prefix)){
                if($pattern === "/"){
                    $prefixedPattern = $prefix;
                } else {
                    $prefixedPattern = $prefix . $pattern;
                }
            } else {
                $prefixedPattern = $pattern;
            }

            /**
             * Map the route manually
             */
            $route = $this->map($prefixedPattern, $realHandler);

            if((is_string($methods) && methods != "") || is_array($methods)){
                $route->via($methods);
            }

            if(is_string($name)){
                $route->setName($name);
            }
        }

        return $this;
    }

    /**
     * Sets a handler that will be called when the router doesn't match any of
     * the defined routes
     *
     * @param callable handler
     */
    public function notFound( $handler): Micro
    {
        $this->notFoundHandler = $handler;

        return $this;
    }

    /**
     * Check if a service is registered in the internal services container using
     * the array syntax
     */
    public function offsetExists($alias): bool
    {
        return $this->hasService($alias);
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
     */
    public function offsetGet($alias)
    {
        return $this->getService($alias);
    }

    /**
     * Allows to register a shared service in the internal services container
     * using the array syntax
     *
     *```php
     *    $app["request"] = new \Phalcon\Http\Request();
     *```
     */
    public function offsetSet($alias, $definition): void
    {
        $this->setService($alias, $definition);
    }

    /**
     * Removes a service from the internal services container using the array
     * syntax
     */
    public function offsetUnset($alias): void
    {
        

        $container = $this->container;

        if(!is_object($container)){
            $container = new FactoryDefault();

            $this->container = container;
        }

        $container->remove($alias);
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is OPTIONS
     *
     * @param callable handler
     */
    public function options(string $routePattern, $handler): RouteInterface
    {
        

        /**
         * We create a router even if there is no one in the DI
         */
        $router = $this->getRouter();

        /**
         * Routes are added to the router restricting to OPTIONS
         */
        $route = $router->addOptions($routePattern);

        /**
         * Using the id produced by the router we store the handler
         */
        $this->handlers[$route->getRouteId()] = $handler;

        /**
         * The route is $returned, the developer can add more things on it
         */
        return route;
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is PATCH
     *
     * @param callable $handler
     */
    public function patch(string $routePattern, $handler): RouteInterface
    {
        

        /**
         * We create a router even if there is no one in the DI
         */
        $router = $this->getRouter();

        /**
         * Routes are added to the router restricting to PATCH
         */
        $route = $router->addPatch($routePattern);

        /**
         * Using the id produced by the router we store the handler
         */
        $this->handlers[$route->getRouteId()] = $handler;

        /**
         * The route is $returned, the developer can add more things on it
         */
        return route;
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is POST
     *
     * @param callable handler
     */
    public function post(string $routePattern, $handler): RouteInterface
    {
        

        /**
         * We create a router even if there is no one in the DI
         */
        $router = $this->getRouter();

        /**
         * Routes are added to the router restricting to POST
         */
        $route = $router->addPost($routePattern);

        /**
         * Using the id produced by the router we store the handler
         */
        $this->handlers[$route->getRouteId()] = $handler;

        /**
         * The route is $returned, the developer can add more things on it
         */
        return route;
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is PUT
     *
     * @param callable $handler
     */
    public function put(string $routePattern, $handler): RouteInterface
    {
        /**
         * We create a router even if there is no one in the DI
         */
        $router = $this->getRouter();

        /**
         * Routes are added to the router restricting to PUT
         */
        $route = $router->addPut($routePattern);

        /**
         * Using the id produced by the router we store the handler
         */
        $this->handlers[$route->getRouteId()] = $handler;

        /**
         * The route is $returned, the developer can add more things on it
         */
        return route;
    }

    /**
     * Sets externally the handler that must be called by the matched route
     *
     * @param callable activeHandler
     */
    public function setActiveHandler($activeHandler)
    {
        $this->activeHandler = $activeHandler;
    }

    /**
     * Sets the DependencyInjector container
     */
    public function setDI(DiInterface $container): void
    {
        /**
         * We automatically set ourselves as application service
         */
        if(!$container->has("application")){
            $container->set("application", $this);
        }

        $this->container = $container;
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
     */
    public function setModelBinder(BinderInterface $modelBinder,  $cache = null): Micro
    {
        if(is_string($cache)){
            $cache = $this->getService($cache);
        }

        if($cache != null){
            $modelBinder->setCache($cache);
        }

        $this->modelBinder = modelBinder;

        return this;
    }

    /**
     * Appends a custom 'response' handler to be called instead of the default
     * response handler
     *
     * @param callable handler
     */
    public function setResponseHandler($handler): Micro
    {
        $this->responseHandler = $handler;

        return this;
    }

    /**
     * Sets a service from the DI
     */
    public function setService(string $serviceName, $definition, bool $shared = false): ServiceInterface
    {
        

        $container = $this->container;

        if(!is_object($container)){
            $container = new FactoryDefault();

            $this->container = container;
        }

        return $container->set($serviceName, $definition, $shared);
    }

    /**
     * Stops the middleware execution avoiding than other middlewares be
     * executed
     */
    public function stop()
    {
        $this->stopped = true;
    }
}
