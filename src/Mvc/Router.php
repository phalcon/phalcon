<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Mvc;

use Phalcon\Di\DiInterface;
use Phalcon\Di\AbstractInjectionAware;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\ManagerInterface;
use Phalcon\Http\RequestInterface;
use Phalcon\Mvc\Router\Exception;
use Phalcon\Mvc\Router\GroupInterface;
use Phalcon\Mvc\Router\Route;
use Phalcon\Mvc\Router\RouteInterface;
//use Phalcon\Helper\Sequence;

/**
 * Phalcon\Mvc\Router
 *
 * Phalcon\Mvc\Router is the standard framework router. Routing is the
 * process of taking a URI endpoint (that part of the URI which comes after the
 * base URL) and decomposing it into parameters to determine which module,
 * controller, and action of that controller should receive the request
 *
 * ```php
 * use Phalcon\Mvc\Router;
 *
 * $router = new Router();
 *
 * $router->add(
 *     "/documentation/{chapter}/{name}\.{type:[a-z]+}",
 *     [
 *         "controller" => "documentation",
 *         "action"     => "show",
 *     ]
 * );
 *
 * $router->handle(
 *     "/documentation/1/examples.html"
 * );
 *
 * echo $router->getControllerName();
 * ```
 */
class Router extends AbstractInjectionAware implements RouterInterface, EventsAwareInterface {

    const POSITION_FIRST = 0;
    const POSITION_LAST = 1;

    protected $action = null;
    protected $controller = null;
    protected $defaultAction;
    protected $defaultController;
    protected $defaultModule;
    protected $defaultNamespace;
    protected $defaultParams = [];
    protected $eventsManager;
    protected $keyRouteNames = []; // { get, set };
    protected $keyRouteIds = []; // { get, set };
    protected $matchedRoute;
    protected $matches;
    protected $module = null;
    protected $namespaceName = null;
    protected $notFoundPaths;
    protected $params = [];
    protected $removeExtraSlashes;
    protected $routes;
    protected $uriSource;
    protected $wasMatched = false;
    protected $generator = null;

    /**
     * @return A generator object
     *   $id = $generator->current();
     *   $generator->next();
     * 
     */

    protected static function genId() : \Generator {
                $i = 0;
                while(true) {
                    yield $i;
                    $i++;
                }
    }
    /**
     * Phalcon\Mvc\Router constructor
     */
    public function __construct(bool $defaultRoutes = true) {
        $routes = [];
        
        $this->generator = self::genId(); // return generator object
        
        $gen = $this->generator;
        
        $gen->rewind();
        
        if ($defaultRoutes) {
            /**
             * Two routes are added by default to match /:controller/:action and
             * /:controller/:action/:params
             */
            
            $routes[] = new Route(
                    $gen->current(),
                    "#^/([\\w0-9\\_\\-]+)[/]{0,1}$#u",
                    [
                "controller" => 1
                    ]
            );
            $this->generator->next();

            $routes[] = new Route(
                    $gen->current(),
                    "#^/([\\w0-9\\_\\-]+)/([\\w0-9\\.\\_]+)(/.*)*$#u",
                    [
                "controller" => 1,
                "action" => 2,
                "params" => 3
                    ]
            );
            $gen->next();
        }

        $this->routes = $routes;
    }

    /**
     * Adds a route to the router without any HTTP constraint
     *
     * ```php
     * use Phalcon\Mvc\Router;
     *
     * $router->add("/about", "About::index");
     *
     * $router->add(
     *     "/about",
     *     "About::index",
     *     ["GET", "POST"]
     * );
     *
     * $router->add(
     *     "/about",
     *     "About::index",
     *     ["GET", "POST"],
     *     Router::POSITION_FIRST
     * );
     * ```
     *
     * @param string|array paths = [
     *     'module => '',
     *     'controller' => '',
     *     'action' => '',
     *     'namespace' => ''
     * ]
     */
    public function add(string $pattern, $paths = null, $httpMethods = null, $position = Router::POSITION_LAST): RouteInterface {
        // var route;

        /**
         * Every route is internally stored as a Phalcon\Mvc\Router\Route
         */
        $route = new Route($this->generator->current(), $pattern, $paths, $httpMethods);
        $this->generator->next();


        $this->attach($route, $position);

        return $route;
    }

    public function getIdGenerator(): Generator {
        return $this->generator;
    }

    /**
     * Adds a route to the router that only match if the HTTP method is CONNECT
     *
     * @param string|array paths = [
     *     'module => '',
     *     'controller' => '',
     *     'action' => '',
     *     'namespace' => ''
     * ]
     */
    public function addConnect(string $pattern, $paths = null, $position = Router::POSITION_LAST): RouteInterface {
        return $this->add($pattern, $paths, "CONNECT", $position);
    }

    /**
     * Adds a route to the router that only match if the HTTP method is DELETE
     *
     * @param string|array paths = [
     *     'module => '',
     *     'controller' => '',
     *     'action' => '',
     *     'namespace' => ''
     * ]
     */
    public function addDelete(string $pattern, $paths = null, $position = Router::POSITION_LAST): RouteInterface {
        return $this->add($pattern, $paths, "DELETE", $position);
    }

    /**
     * Adds a route to the router that only match if the HTTP method is GET
     *
     * @param string|array paths = [
     *     'module => '',
     *     'controller' => '',
     *     'action' => '',
     *     'namespace' => ''
     * ]
     */
    public function addGet(string $pattern, $paths = null, $position = Router::POSITION_LAST): RouteInterface {
        return $this->add($pattern, $paths, "GET", $position);
    }

    /**
     * Adds a route to the router that only match if the HTTP method is HEAD
     *
     * @param string|array paths = [
     *     'module => '',
     *     'controller' => '',
     *     'action' => '',
     *     'namespace' => ''
     * ]
     */
    public function addHead(string $pattern, $paths = null, $position = Router::POSITION_LAST): RouteInterface {
        return $this->add($pattern, $paths, "HEAD", $position);
    }

    /**
     * Add a route to the router that only match if the HTTP method is OPTIONS
     *
     * @param string|array paths = [
     *     'module => '',
     *     'controller' => '',
     *     'action' => '',
     *     'namespace' => ''
     * ]
     */
    public function addOptions(string $pattern, $paths = null, $position = Router::POSITION_LAST): RouteInterface {
        return $this->add($pattern, $paths, "OPTIONS", $position);
    }

    /**
     * Adds a route to the router that only match if the HTTP method is PATCH
     *
     * @param string|array paths = [
     *     'module => '',
     *     'controller' => '',
     *     'action' => '',
     *     'namespace' => ''
     * ]
     */
    public function addPatch(string $pattern, $paths = null, $position = Router::POSITION_LAST): RouteInterface {
        return $this->add($pattern, $paths, "PATCH", $position);
    }

    /**
     * Adds a route to the router that only match if the HTTP method is POST
     *
     * @param string|array paths = [
     *     'module => '',
     *     'controller' => '',
     *     'action' => '',
     *     'namespace' => ''
     * ]
     */
    public function addPost(string $pattern, $paths = null, $position = Router::POSITION_LAST): RouteInterface {
        return $this->add($pattern, $paths, "POST", $position);
    }

    /**
     * Adds a route to the router that only match if the HTTP method is PURGE
     * (Squid and Varnish support)
     *
     * @param string|array paths = [
     *     'module => '',
     *     'controller' => '',
     *     'action' => '',
     *     'namespace' => ''
     * ]
     */
    public function addPurge(string $pattern, $paths = null, $position = Router::POSITION_LAST): RouteInterface {
        return $this->add($pattern, $paths, "PURGE", position);
    }

    /**
     * Adds a route to the router that only match if the HTTP method is PUT
     *
     * @param string|array paths = [
     *     'module => '',
     *     'controller' => '',
     *     'action' => '',
     *     'namespace' => ''
     * ]
     */
    public function addPut(string $pattern, $paths = null, $position = Router::POSITION_LAST): RouteInterface {
        return $this->add($pattern, $paths, "PUT", position);
    }

    /**
     * Adds a route to the router that only match if the HTTP method is TRACE
     *
     * @param string|array paths = [
     *     'module => '',
     *     'controller' => '',
     *     'action' => '',
     *     'namespace' => ''
     * ]
     */
    public function addTrace(string $pattern, $paths = null, $position = Router::POSITION_LAST): RouteInterface {
        return $this->add($pattern, $paths, "TRACE", position);
    }

    /**
     * Attach Route object to the routes stack.
     *
     * ```php
     * use Phalcon\Mvc\Router;
     * use Phalcon\Mvc\Router\Route;
     *
     * class CustomRoute extends Route {
     *      // ...
     * }
     *
     * $router = new Router();
     *
     * $router->attach(
     *     new CustomRoute("/about", "About::index", ["GET", "HEAD"]),
     *     Router::POSITION_FIRST
     * );
     * ```
     */
    public function attach(RouteInterface $route, $position = Router::POSITION_LAST): RouterInterface 
    {
        switch ($position) {
            case self::POSITION_LAST:
                $this->routes[] = $route;
                break;
            case self::POSITION_FIRST:
                $this->routes = array_merge([$route], $this->routes);
                break;
            default:
                throw new Exception("Invalid route position");
        }

        return $this;
    }

    /**
     * Removes all the pre-defined routes
     */
    public function clear(): void {
        $this->routes = [];
    }

    /**
     * Returns the internal event manager
     */
    public function getEventsManager(): ManagerInterface {
        return $this->eventsManager;
    }

    /**
     * Returns the processed action name
     */
    public function getActionName(): string {
        return $this->action;
    }

    /**
     * Returns the processed controller name
     */
    public function getControllerName(): string {
        return $this->controller;
    }

    /**
     * Returns the route that matches the handled URI
     */
    public function getMatchedRoute(): RouteInterface {
        return $this->matchedRoute;
    }

    /**
     * Returns the sub expressions in the regular expression matched
     */
    public function getMatches(): array {
        return $this->matches;
    }

    /**
     * Returns the processed module name
     */
    public function getModuleName(): ?string {
        return $this->module;
    }

    /**
     * Returns the processed namespace name
     */
    public function getNamespaceName(): ?string {
        return $this->namespaceName;
    }

    /**
     * Returns the processed parameters
     */
    public function getParams(): ?array {
        return $this->params;
    }

    /**
     * Returns a route object by its id
     * TODO: return RouteInterface | bool
     */
    public function getRouteById(int $id) {
        $key = $this->keyRouteIds[$id] ?? null;

        if ($key !== null) {
            return $this->routes[$key];
        }
        // rekey operation
        foreach ($routes as $key => $route) {
            $routeId = $route->getRouteId();
            $this->keyRouteIds[$routeId] = $key;

            if ($routeId === $id) {
                return $route;
            }
        }
        return false;
    }

    /**
     * Returns a route object by its name
     * TODO: return RouteInterface | bool
     */
    public function getRouteByName(string $name) {
        //var route, routeName, key;
        $key = $this->keyRouteNames[$name] ?? null;

        if ($key !== null) {
            return $this->routes[$key];
        }
        foreach ($this->routes as $key => $route) {
            $routeName = $route->getName();

            if (!empty($routeName)) {
                $this->keyRouteNames[$routeName] = $key;
                if ($routeName === $name) {
                    return $route;
                }
            }
        }
        return false;
    }

    /**
     * Returns all the routes defined in the router
     */
    public function getRoutes(): array {
        return $this->routes;
    }

    /**
     * Code it once
     */
    private function getRequestObj(): RequestInterface {
        $container = $this->container;

        if (!is_object($container)) {
            throw new Exception(
                    Exception::containerServiceNotFound(
                            "the 'request' service"
                    )
            );
        }

        return $container->getShared("request");
    }

    /**
     * Handles routing information received from the rewrite engine
     *
     * ```php
     * // Passing a URL
     * $router->handle("/posts/edit/1");
     * ```
     */
    public function handle(string $uri): void {
        $uri = parse_url($uri, PHP_URL_PATH);

        /**
         * Remove extra slashes in the route
         */
        if (($this->removeExtraSlashes) && ($uri !== "/")) {
            $handledUri = rtrim($uri, "/");
        } else {
            $handledUri = $uri;
        }

        if (empty($handledUri)) {
            $handledUri = "/";
        }

        $request = null;
        $currentHostName = null;
        $routeFound = false;
        $parts = [];
        $params = [];
        $matches = null;
        $this->wasMatched = false;
        $this->matchedRoute = null;

        $eventsManager = is_object($this->eventsManager) ? $this->eventsManager : null;

        if ($eventsManager !== null) {
            $eventsManager->fire("router:beforeCheckRoutes", $this);
        }

        /**
         * Routes are traversed in reversed order
         */
        foreach (array_reverse($this->routes) as $route) {
            $params = [];
            $matches = null;

            /**
             * Look for HTTP method constraints
             */
            $methods = $route->getHttpMethods();

            if ($methods !== null) {
                /**
                 * Retrieve the request service from the container
                 */
                if ($request === null) {
                    $request = $this->getRequestObj();
                }

                /**
                 * Check if the current method is allowed by the route
                 */
                if ($request->isMethod($methods, true) === false) {
                    continue;
                }
            }

            /**
             * Look for hostname constraints
             */
            $hostname = $route->getHostName();

            if ($hostname !== null) {
                /**
                 * Retrieve the request service from the container
                 */
                if ($request === null) {
                    $request = $this->getRequestObj();
                }


                /**
                 * Check if the current hostname is the same as the route
                 */
                if ($currentHostName === null) {
                    $currentHostName = $request->getHttpHost();
                }

                /**
                 * No HTTP_HOST, maybe in CLI mode?
                 */
                if (!$currentHostName) {
                    continue;
                }

                /**
                 * Check if the hostname restriction is the same as the current
                 * in the route
                 */
                if (strpos($hostname, "(") !== false) {
                    if (strpos(hostname, "#") === false) {
                        $regexHostName = "#^" . $hostname;
                        if (strpos(hostname, ":") === false) {
                            $regexHostName .= "(:[[:digit:]]+)?";
                        }
                        $regexHostName .= "$#i";
                    } else {
                        $regexHostName = $hostname;
                    }

                    $matched = preg_match($regexHostName, $currentHostName);
                } else {
                    $matched = ($currentHostName === $hostname);
                }

                if (!$matched) {
                    continue;
                }
            }  // if $hostname

            if ($eventsManager !== null) {
                $eventsManager->fire("router:beforeCheckRoute", $this, $route);
            }

            /**
             * If the route has parentheses use preg_match
             */
            $pattern = $route->getCompiledPattern();

            if (strpos($pattern, "^") !== false) {
                $routeFound = preg_match($pattern, $handledUri, $matches);
            } else {
                $routeFound = ($pattern === $handledUri);
            }

            /**
             * Check for beforeMatch conditions
             */
            if ($routeFound) {
                if ($eventsManager !== null) {
                    $eventsManager->fire("router:matchedRoute", $this, $route);
                }

                $beforeMatch = $route->getBeforeMatch();

                if ($beforeMatch !== null) {
                    /**
                     * Check first if the callback is callable
                     */
                    if (!is_callable($beforeMatch)) {
                        throw new Exception(
                                "Before-Match callback is not callable in matched route"
                        );
                    }

                    /**
                     * Check first if the callback is callable
                     */
                    $routeFound = call_user_func_array($beforeMatch, [$handledUri, $route, $this]);
                }
            } else {
                if ($eventsManager !== null) {
                    $routeFound = $eventsManager->fire("router:notMatchedRoute", $this, $route);
                }
            }

            if ($routeFound) {
                /**
                 * Start from the default paths
                 */
                $paths = $route->getPaths();
                $parts = $paths;

                /**
                 * Check if the matches has variables
                 */
                if (is_array($matches)) {
                    /**
                     * Get the route converters if any
                     */
                    $converters = $route->getConverters();

                    foreach ($paths as $part => $position) {
                        if (!is_string($part)) {
                            throw new Exception("Wrong key in paths: " . $part);
                        }

                        if (!is_string($position) && !is_int($position)) {
                            continue;
                        }
                        $matchPosition = $matches[$position] ?? null;
                        if ($matchPosition !== null) {
                            /**
                             * Check if the part has a converter
                             */
                            if (is_array($converters)) {
                                $converter = $converters[$part] ?? null;
                                if ($converter !== null) {
                                    $parts[$part] = call_user_func_array(
                                            $converter,
                                            [$matchPosition]
                                    );

                                    continue;
                                }
                            }

                            /**
                             * Update the parts if there is no converter
                             */
                            $parts[$part] = $matchPosition;
                        } else {
                            /**
                             * Apply the converters anyway
                             */
                            if (is_array($converters)) {
                                $converter = $converters[$part] ?? null;
                                if ($converter !== null) {
                                    $parts[$part] = call_user_func_array(
                                            $converter,
                                            [$position]
                                    );
                                }
                            } else {
                                /**
                                 * Remove the path if the parameter was not
                                 * matched
                                 */
                                if (is_int($position)) {
                                    unset($parts[$part]);
                                }
                            }
                        }
                    }


                    /**
                     * Update the matches generated by preg_match
                     */
                    $this->matches = $matches;
                }

                $this->matchedRoute = $route;

                break;
            }
        }


        /**
         * Update the wasMatched property indicating if the route was matched
         */
        if ($routeFound) {
            $this->wasMatched = true;
        } else {
            $this->wasMatched = false;
        }

        /**
         * The route wasn't found, try to use the not-found paths
         */
        if (!$routeFound) {
            $notFoundPaths = $this->notFoundPaths;

            if ($notFoundPaths !== null) {
                $parts = Route::getRoutePaths($notFoundPaths);
                $routeFound = true;
            }
        }

        /**
         * Use default values before we overwrite them if the route is matched
         */
        $this->namespaceName = $this->defaultNamespace;
        $this->module = $this->defaultModule;
        $this->controller = $this->defaultController;
        $this->action = $this->defaultAction;
        $this->params = $this->defaultParams;

        if ($routeFound) {
            /**
             * Check for a namespace
             */
            $vnamespace = $parts["namespace"] ?? null;
            if ($vnamespace !== null) {
                if (!is_numeric($vnamespace)) {
                    $this->namespaceName = $vnamespace;
                }

                unset($parts["namespace"]);
            }

            /**
             * Check for a module
             */
            $module = $parts["module"] ?? null;
            if ($module !== null) {
                if (!is_numeric($module)) {
                    $this->module = $module;
                }

                unset($parts["module"]);
            }

            /**
             * Check for a controller
             */
            $controller = $parts["controller"] ?? null;
            if ($controller !== null) {
                if (!is_numeric($controller)) {
                    $this->controller = $controller;
                }

                unset($parts["controller"]);
            }

            /**
             * Check for an action
             */
            $action = $parts["action"] ?? null;
            if ($action !== null) {
                if (!is_numeric($action)) {
                    $this->action = $action;
                }

                unset($parts["action"]);
            }

            /**
             * Check for parameters
             */
            $paramsStr = $parts["params"] ?? null;
            if ($paramsStr !== null) {
                if (is_string($paramsStr)) {
                    $strParams = trim($paramsStr, "/");

                    if (!empty($strParams)) {
                        $params = explode("/", $strParams);
                    }
                }

                unset($parts["params"]);
            }

            if (count($params)) {
                $this->params = array_merge($params, $parts);
            } else {
                $this->params = $parts;
            }
        }

        if ($eventsManager !== null) {
            $eventsManager->fire("router:afterCheckRoutes", $this);
        }
    }

    /**
     * Returns whether controller name should not be mangled
     */
    public function isExactControllerName(): bool {
        return true;
    }

    /**
     * Mounts a group of routes in the router
     */
    public function mount(GroupInterface $group): RouterInterface 
    {
        //var groupRoutes, beforeMatch, hostname, routes, route, eventsManager;

        $eventsManager = is_object($this->eventsManager) ? $this->eventsManager : null;

        if ($eventsManager !== null) {
            $eventsManager->fire("router:beforeMount", $this, $group);
        }

        $groupRoutes = $group->getRoutes();

        if (empty($groupRoutes)) {
            throw new Exception(
                    "The group of routes does not contain any routes"
            );
        }

        /**
         * Get the before-match condition
         */
        $beforeMatch = $group->getBeforeMatch();

        if ($beforeMatch !== null) {
            foreach ($groupRoutes as $route) {
                $route->beforeMatch($beforeMatch);
            }
        }

        // Get the hostname restriction
        $hostname = $group->getHostName();

        if ($hostname !== null) {
            foreach ($groupRoutes as $route) {
                $route->setHostName($hostname);
            }
        }

        $routes = $this->routes;

        $this->routes = array_merge($routes, $groupRoutes);

        return $this;
    }

    /**
     * Set a group of paths to be returned when none of the defined routes are
     * matched
     */
    public function notFound($paths): RouterInterface {
        if (!is_array($paths) && !is_string($paths)) {
            throw new Exception(
                    "The not-found paths must be an array or string"
            );
        }

        $this->notFoundPaths = $paths;

        return $this;
    }

    /**
     * Set whether router must remove the extra slashes in the handled routes
     */
    public function removeExtraSlashes(bool $remove): RouterInterface {
        $this->removeExtraSlashes = $remove;

        return $this;
    }

    /**
     * Sets the default action name
     */
    public function setDefaultAction(string $actionName): RouterInterface {
        $this->defaultAction = $actionName;

        return $this;
    }

    /**
     * Sets the default controller name
     */
    public function setDefaultController(string $controllerName): RouterInterface {
        $this->defaultController = $controllerName;

        return $this;
    }

    /**
     * Sets the name of the default module
     */
    public function setDefaultModule(string $moduleName): RouterInterface {
        $this->defaultModule = $moduleName;

        return $this;
    }

    /**
     * Sets the name of the default namespace
     */
    public function setDefaultNamespace(string $namespaceName): RouterInterface {
        $this->defaultNamespace = $namespaceName;

        return $this;
    }

    /**
     * Sets an array of default paths. If a route is missing a path the router
     * will use the defined here. This method must not be used to set a 404
     * route
     *
     * ```php
     * $router->setDefaults(
     *     [
     *         "module" => "common",
     *         "action" => "index",
     *     ]
     * );
     * ```
     */
    public function setDefaults(array $defaults): RouterInterface {
        // var namespaceName, module, controller, action, params;
        // Set a default namespace
        $namespaceName = $defaults["namespace"] ?? null;
        if ($namespaceName !== null) {
            $this->defaultNamespace = namespaceName;
        }

        // Set a default module
        $module = $defaults["module"] ?? null;
        if ($module !== null) {
            $this->defaultModule = $module;
        }

        // Set a default controller
        $controller = $defaults["controller"] ?? null;
        if ($controller !== null) {
            $this->defaultController = $controller;
        }

        // Set a default action
        $action = $defaults["action"] ?? null;
        if ($action !== null) {
            $this->defaultAction = $action;
        }

        // Set default parameters
        $params = $defaults["params"] ?? null;
        if ($params !== null) {
            $this->defaultParams = $params;
        }

        return $this;
    }

    /**
     * Returns an array of default parameters
     */
    public function getDefaults(): array {
        return [
            "namespace" => $this->defaultNamespace,
            "module" => $this->defaultModule,
            "controller" => $this->defaultController,
            "action" => $this->defaultAction,
            "params" => $this->defaultParams
        ];
    }

    /**
     * Sets the events manager
     */
    public function setEventsManager(ManagerInterface $eventsManager): void {
        $this->eventsManager = $eventsManager;
    }

    /**
     * Checks if the router matches any of the defined routes
     */
    public function wasMatched(): bool {
        return $this->wasMatched;
    }

}
