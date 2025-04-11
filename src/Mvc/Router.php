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

namespace Phalcon\Mvc;

use Phalcon\Di\AbstractInjectionAware;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\Exception as EventsException;
use Phalcon\Events\Traits\EventsAwareTrait;
use Phalcon\Http\RequestInterface;
use Phalcon\Mvc\Router\Exception;
use Phalcon\Mvc\Router\GroupInterface;
use Phalcon\Mvc\Router\Route;
use Phalcon\Mvc\Router\RouteInterface;

use function array_merge;
use function array_reverse;
use function call_user_func_array;
use function explode;
use function is_array;
use function is_callable;
use function is_int;
use function is_string;
use function preg_match;
use function rtrim;
use function trim;

/**
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
class Router extends AbstractInjectionAware implements RouterInterface, EventsAwareInterface
{
    use EventsAwareTrait;

    public const POSITION_FIRST = 0;
    public const POSITION_LAST  = 1;

    public const URI_SOURCE_GET_URL            = 0;
    public const URI_SOURCE_SERVER_REQUEST_URI = 1;

    /**
     * @var string
     */
    protected string $action = "";

    /**
     * @var string
     */
    protected string $controller = "";

    /**
     * @var string
     */
    protected string $defaultAction = "";

    /**
     * @var string
     */
    protected string $defaultController = "";

    /**
     * @var string
     */
    protected string $defaultModule = "";

    /**
     * @var string
     */
    protected string $defaultNamespace = "";

    /**
     * @var array
     */
    protected array $defaultParams = [];

    /**
     * @var array
     */
    protected array $keyRouteIds = [];

    /**
     * @var array
     */
    protected array $keyRouteNames = [];

    /**
     * @var RouteInterface|null
     */
    protected RouteInterface | null $matchedRoute = null;

    /**
     * @var array
     */
    protected array $matches = [];

    /**
     * @var string
     */
    protected string $module = "";

    /**
     * @var string
     */
    protected string $namespaceName = "";

    /**
     * @var array|string|null
     */
    protected array | string | null $notFoundPaths = null;

    /**
     * @var array
     */
    protected array $params = [];

    /**
     * @var bool
     */
    protected bool $removeExtraSlashes = false;

    /**
     * @var array
     */
    protected array $routes = [];

    /**
     * @var int
     */
    protected int $uriSource = self::URI_SOURCE_GET_URL;

    /**
     * @var bool
     */
    protected bool $wasMatched = false;

    /**
     * Phalcon\Mvc\Router constructor
     *
     * @param bool $defaultRoutes
     *
     * @throws Exception
     */
    public function __construct(bool $defaultRoutes = true)
    {
        if ($defaultRoutes) {
            /**
             * Two routes are added by default to match /:controller/:action and
             * /:controller/:action/:params
             */
            $this->routes[] = new Route(
                "#^/([\\w0-9\\_\\-]+)[/]{0,1}$#u",
                [
                    "controller" => 1,
                ]
            );

            $this->routes[] = new Route(
                "#^/([\\w0-9\\_\\-]+)/([\\w0-9\\.\\_]+)(/.*)*$#u",
                [
                    "controller" => 1,
                    "action"     => 2,
                    "params"     => 3,
                ]
            );
        }
    }

    /**
     * Adds a route to the router without any HTTP constraint
     *
     *```php
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
     *```
     *
     * @param string            $pattern
     * @param array|string|null $paths = [
     *                                 'module => '',
     *                                 'controller' => '',
     *                                 'action' => '',
     *                                 'namespace' => ''
     *                                 ]
     * @param array|string|null $httpMethods
     * @param int               $position
     *
     * @return RouteInterface
     * @throws Exception
     */
    public function add(
        string $pattern,
        array | string | null $paths = null,
        array | string | null $httpMethods = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface {
        /**
         * Every route is internally stored as a Phalcon\Mvc\Router\Route
         */
        $route = new Route($pattern, $paths, $httpMethods);

        $this->attach($route, $position);

        return $route;
    }

    /**
     * Adds a route to the router that only match if the HTTP method is CONNECT
     *
     * @param string            $pattern
     * @param array|string|null $paths  = [
     *                                  'module => '',
     *                                  'controller' => '',
     *                                  'action' => '',
     *                                  'namespace' => ''
     *                                  ]
     * @param int               $position
     *
     * @return RouteInterface
     * @throws Exception
     */
    public function addConnect(
        string $pattern,
        array | string | null $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface {
        return $this->add($pattern, $paths, "CONNECT", $position);
    }

    /**
     * Adds a route to the router that only match if the HTTP method is DELETE
     *
     * @param string            $pattern
     * @param array|string|null $paths  = [
     *                                  'module => '',
     *                                  'controller' => '',
     *                                  'action' => '',
     *                                  'namespace' => ''
     *                                  ]
     * @param int               $position
     *
     * @return RouteInterface
     * @throws Exception
     */
    public function addDelete(
        string $pattern,
        array | string | null $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface {
        return $this->add($pattern, $paths, "DELETE", $position);
    }

    /**
     * Adds a route to the router that only match if the HTTP method is GET
     *
     * @param string            $pattern
     * @param array|string|null $paths  = [
     *                                  'module => '',
     *                                  'controller' => '',
     *                                  'action' => '',
     *                                  'namespace' => ''
     *                                  ]
     * @param int               $position
     *
     * @return RouteInterface
     * @throws Exception
     */
    public function addGet(
        string $pattern,
        array | string | null $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface {
        return $this->add($pattern, $paths, "GET", $position);
    }

    /**
     * Adds a route to the router that only match if the HTTP method is HEAD
     *
     * @param string            $pattern
     * @param array|string|null $paths  = [
     *                                  'module => '',
     *                                  'controller' => '',
     *                                  'action' => '',
     *                                  'namespace' => ''
     *                                  ]
     * @param int               $position
     *
     * @return RouteInterface
     * @throws Exception
     */
    public function addHead(
        string $pattern,
        array | string | null $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface {
        return $this->add($pattern, $paths, "HEAD", $position);
    }

    /**
     * Add a route to the router that only match if the HTTP method is OPTIONS
     *
     * @param string            $pattern
     * @param array|string|null $paths  = [
     *                                  'module => '',
     *                                  'controller' => '',
     *                                  'action' => '',
     *                                  'namespace' => ''
     *                                  ]
     * @param int               $position
     *
     * @return RouteInterface
     * @throws Exception
     */
    public function addOptions(
        string $pattern,
        array | string | null $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface {
        return $this->add($pattern, $paths, "OPTIONS", $position);
    }

    /**
     * Adds a route to the router that only match if the HTTP method is PATCH
     *
     * @param string            $pattern
     * @param array|string|null $paths  = [
     *                                  'module => '',
     *                                  'controller' => '',
     *                                  'action' => '',
     *                                  'namespace' => ''
     *                                  ]
     * @param int               $position
     *
     * @return RouteInterface
     * @throws Exception
     */
    public function addPatch(
        string $pattern,
        array | string | null $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface {
        return $this->add($pattern, $paths, "PATCH", $position);
    }

    /**
     * Adds a route to the router that only match if the HTTP method is POST
     *
     * @param string            $pattern
     * @param array|string|null $paths  = [
     *                                  'module => '',
     *                                  'controller' => '',
     *                                  'action' => '',
     *                                  'namespace' => ''
     *                                  ]
     * @param int               $position
     *
     * @return RouteInterface
     * @throws Exception
     */
    public function addPost(
        string $pattern,
        array | string | null $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface {
        return $this->add($pattern, $paths, "POST", $position);
    }

    /**
     * Adds a route to the router that only match if the HTTP method is PURGE
     * (Squid and Varnish support)
     *
     * @param string            $pattern
     * @param array|string|null $paths  = [
     *                                  'module => '',
     *                                  'controller' => '',
     *                                  'action' => '',
     *                                  'namespace' => ''
     *                                  ]
     * @param int               $position
     *
     * @return RouteInterface
     * @throws Exception
     */
    public function addPurge(
        string $pattern,
        array | string | null $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface {
        return $this->add($pattern, $paths, "PURGE", $position);
    }

    /**
     * Adds a route to the router that only match if the HTTP method is PUT
     *
     * @param string            $pattern
     * @param array|string|null $paths  = [
     *                                  'module => '',
     *                                  'controller' => '',
     *                                  'action' => '',
     *                                  'namespace' => ''
     *                                  ]
     * @param int               $position
     *
     * @return RouteInterface
     * @throws Exception
     */
    public function addPut(
        string $pattern,
        array | string | null $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface {
        return $this->add($pattern, $paths, "PUT", $position);
    }

    /**
     * Adds a route to the router that only match if the HTTP method is TRACE
     *
     * @param string            $pattern
     * @param array|string|null $paths  = [
     *                                  'module => '',
     *                                  'controller' => '',
     *                                  'action' => '',
     *                                  'namespace' => ''
     *                                  ]
     * @param int               $position
     *
     * @return RouteInterface
     * @throws Exception
     */
    public function addTrace(
        string $pattern,
        array | string | null $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface {
        return $this->add($pattern, $paths, "TRACE", $position);
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
     *
     * @param RouteInterface $route
     * @param int            $position
     *
     * @return RouterInterface
     * @throws Exception
     */
    public function attach(
        RouteInterface $route,
        int $position = Router::POSITION_LAST
    ): RouterInterface {
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
     *
     * @return void
     */
    public function clear(): void
    {
        $this->routes = [];
    }

    /**
     * Returns the processed action name
     *
     * @return string
     */
    public function getActionName(): string
    {
        return $this->action;
    }

    /**
     * Returns the processed controller name
     *
     * @return string
     */
    public function getControllerName(): string
    {
        return $this->controller;
    }

    /**
     * Returns an array of default parameters
     *
     * @return array<string, array|string>
     */
    public function getDefaults(): array
    {
        return [
            'namespace'  => $this->defaultNamespace,
            'module'     => $this->defaultModule,
            'controller' => $this->defaultController,
            'action'     => $this->defaultAction,
            'params'     => $this->defaultParams,
        ];
    }

    /**
     * @return array
     */
    public function getKeyRouteIds(): array
    {
        return $this->keyRouteIds;
    }

    /**
     * @return array
     */
    public function getKeyRouteNames(): array
    {
        return $this->keyRouteNames;
    }

    /**
     * Returns the route that matches the handled URI
     *
     * @return RouteInterface|null
     */
    public function getMatchedRoute(): RouteInterface | null
    {
        return $this->matchedRoute;
    }

    /**
     * Returns the sub expressions in the regular expression matched
     *
     * @return array
     */
    public function getMatches(): array
    {
        return $this->matches;
    }

    /**
     * Returns the processed module name
     *
     * @return string
     */
    public function getModuleName(): string
    {
        return $this->module;
    }

    /**
     * Returns the processed namespace name
     *
     * @return string
     */
    public function getNamespaceName(): string
    {
        return $this->namespaceName;
    }

    /**
     * Returns the processed parameters
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Get rewrite info. This info is read from $_GET["_url"].
     * This returns '/' if the rewrite information cannot be read
     */
    public function getRewriteUri(): string
    {
        /**
         * By default we use $_GET["url"] to obtain the rewrite information
         */
        if (empty($this->uriSource)) {
            $url = $_GET['_url'] ?? '';
            if (true !== empty($url)) {
                return $this->extractRealUri($url);
            }
        } else {
            /**
             * Otherwise use the standard $_SERVER["REQUEST_URI"]
             */
            $url = $_SERVER['REQUEST_URI'] ?? '';
            if (true !== empty($url)) {
                return $this->extractRealUri($url);
            }
        }

        return "/";
    }

    /**
     * Returns a route object by its id
     *
     * @param int|string $routeId
     *
     * @return RouteInterface|bool
     */
    public function getRouteById(int | string $routeId): RouteInterface | bool
    {
        if (isset($this->keyRouteIds[$routeId])) {
            return $this->routes[$this->keyRouteIds[$routeId]];
        }

        /**
         * @var int            $key
         * @var RouteInterface $route
         */
        foreach ($this->routes as $key => $route) {
            $id                     = $route->getRouteId();
            $this->keyRouteIds[$id] = $key;

            if ($id == $routeId) {
                return $route;
            }
        }

        return false;
    }

    /**
     * Returns a route object by its name
     *
     * @param string $name
     *
     * @return RouteInterface|bool
     */
    public function getRouteByName(string $name): RouteInterface | bool
    {
        if (isset($this->keyRouteNames[$name])) {
            return $this->routes[$this->keyRouteNames[$name]];
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
     *
     * @return RouteInterface[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Handles routing information received from the rewrite engine
     *
     *```php
     * // Passing a URL
     * $router->handle("/posts/edit/1");
     *```
     *
     * @param string $uri
     *
     * @return void
     * @throws Exception
     * @throws EventsException
     */
    public function handle(string $uri): void
    {
        if (empty($uri)) {
            /**
             * If 'uri' isn't passed as parameter it reads _GET["_url"]
             */
            $uri = $this->getRewriteUri();
        } else {
            $uri = $this->extractRealUri($uri);
        }

        /**
         * Remove extra slashes in the route
         */
        if ($this->removeExtraSlashes && $uri !== "/") {
            $handledUri = rtrim($uri, "/");
        } else {
            $handledUri = $uri;
        }

        if (empty($handledUri)) {
            $handledUri = "/";
        }

        $currentHostName    = null;
        $routeFound         = false;
        $parts              = [];
        $params             = [];
        $this->wasMatched   = false;
        $this->matchedRoute = null;

        $this->fireManagerEvent('router:beforeCheckRoutes');

        /**
         * Retrieve the request service from the container
         */
        $this->checkContainer(
            Exception::class,
            "the 'request' service"
        );

        /** @var RequestInterface $request */
        $request = $this->container->get("request");

        /**
         * Routes are traversed in reversed order
         */
        $reverseRoutes = array_reverse($this->routes);

        foreach ($reverseRoutes as $route) {
            $params  = [];
            $matches = null;

            /**
             * Look for HTTP method constraints
             */
            $methods = $route->getHttpMethods();
            if (
                null !== $methods &&
                false === $request->isMethod($methods, true)
            ) {
                /**
                 * Check if the current method is allowed by the route
                 */
                continue;
            }

            /**
             * Look for hostname constraints
             */
            $hostname = $route->getHostName();
            if (null !== $hostname) {
                /**
                 * Check if the current hostname is the same as the route
                 */
                if (null === $currentHostName) {
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
                if (str_contains($hostname, "(")) {
                    if (!str_contains($hostname, "#")) {
                        $regexHostName = "#^" . $hostname;

                        if (!str_contains($hostname, ":")) {
                            $regexHostName .= "(:[[:digit:]]+)?";
                        }

                        $regexHostName .= "$#i";
                    } else {
                        $regexHostName = $hostname;
                    }

                    $matched = preg_match($regexHostName, $currentHostName);
                } else {
                    $matched = $currentHostName == $hostname;
                }

                if (!$matched) {
                    continue;
                }
            }

            $this->fireManagerEvent('router:beforeCheckRoute', $route);

            /**
             * If the route has parentheses use preg_match
             */
            $pattern = $route->getCompiledPattern();

            if (str_contains($pattern, "^")) {
                $routeFound = preg_match($pattern, $handledUri, $matches);
            } else {
                $routeFound = $pattern === $handledUri;
            }

            /**
             * Check for beforeMatch conditions
             */
            if ($routeFound) {
                $this->fireManagerEvent('router:matchedRoute', $route);

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
                    $routeFound = call_user_func_array(
                        $beforeMatch,
                        [
                            $handledUri,
                            $route,
                            $this,
                        ]
                    );
                }
            } else {
                $this->fireManagerEvent('router:notMatchedRoute', $route);
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

                        if (isset($matches[$position])) {
                            $matchPosition = $matches[$position];
                            /**
                             * Check if the part has a converter
                             */
                            if (is_array($converters) && isset($converters[$part])) {
                                $converter    = $converters[$part];
                                $parts[$part] = call_user_func_array(
                                    $converter,
                                    [$matchPosition]
                                );

                                continue;
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
                                if (isset($converters[$part])) {
                                    $converter    = $converters[$part];
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
        $this->wasMatched = (bool)$routeFound;

        /**
         * The route wasn't found, try to use the not-found paths
         */
        if (!$routeFound) {
            $notFoundPaths = $this->notFoundPaths;

            if ($notFoundPaths !== null) {
                $parts      = Route::getRoutePaths($notFoundPaths);
                $routeFound = true;
            }
        }

        /**
         * Use default values before we overwrite them if the route is matched
         */
        $this->namespaceName = $this->defaultNamespace;
        $this->module        = $this->defaultModule;
        $this->controller    = $this->defaultController;
        $this->action        = $this->defaultAction;
        $this->params        = $this->defaultParams;

        if ($routeFound) {
            /**
             * Check for a namespace
             */
            if (isset($parts['namespace'])) {
                $this->namespaceName = $parts['namespace'];
                unset($parts['namespace']);
            }

            /**
             * Check for a module
             */
            if (isset($parts['module'])) {
                $this->module = $parts['module'];
                unset($parts['module']);
            }

            /**
             * Check for a controller
             */
            if (isset($parts['controller'])) {
                $this->controller = $parts['controller'];
                unset($parts['controller']);
            }

            /**
             * Check for an action
             */
            if (isset($parts['action'])) {
                $this->action = $parts['action'];
                unset($parts['action']);
            }

            /**
             * Check for parameters
             */
            if (isset($parts["params"])) {
                $paramsStr = $parts["params"];
                if (is_string($paramsStr)) {
                    $strParams = trim($paramsStr, "/");

                    if ('' !== $strParams) {
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

        $this->fireManagerEvent('router:afterCheckRoutes');
    }

    /**
     * Returns whether controller name should not be mangled
     *
     * @return bool
     */
    public function isExactControllerName(): bool
    {
        return true;
    }

    /**
     * Mounts a group of routes in the router
     *
     * @param GroupInterface $group
     *
     * @return RouterInterface
     * @throws EventsException
     * @throws Exception
     */
    public function mount(GroupInterface $group): RouterInterface
    {
        $this->fireManagerEvent('router:beforeMount', $group);

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
            foreach ($groupRoutes as $groupRoute) {
                $groupRoute->beforeMatch($beforeMatch);
            }
        }

        // Get the hostname restriction
        $hostname = $group->getHostName();

        if (null !== $hostname) {
            foreach ($groupRoutes as $groupRoute) {
                $groupRoute->setHostName($hostname);
            }
        }

        $this->routes = array_merge($this->routes, $groupRoutes);

        return $this;
    }

    /**
     * Set a group of paths to be returned when none of the defined routes are
     * matched
     *
     * @param array|string $paths
     *
     * @return RouterInterface
     */
    public function notFound(array | string $paths): RouterInterface
    {
        $this->notFoundPaths = $paths;

        return $this;
    }

    /**
     * Set whether router must remove the extra slashes in the handled routes
     *
     * @param bool $remove
     *
     * @return RouterInterface
     */
    public function removeExtraSlashes(bool $remove): RouterInterface
    {
        $this->removeExtraSlashes = $remove;

        return $this;
    }

    /**
     * Sets the default action name
     *
     * @param string $actionName
     *
     * @return RouterInterface
     */
    public function setDefaultAction(string $actionName): RouterInterface
    {
        $this->defaultAction = $actionName;

        return $this;
    }

    /**
     * Sets the default controller name
     *
     * @param string $controllerName
     *
     * @return RouterInterface
     */
    public function setDefaultController(string $controllerName): RouterInterface
    {
        $this->defaultController = $controllerName;

        return $this;
    }

    /**
     * Sets the name of the default module
     *
     * @param string $moduleName
     *
     * @return RouterInterface
     */
    public function setDefaultModule(string $moduleName): RouterInterface
    {
        $this->defaultModule = $moduleName;

        return $this;
    }

    /**
     * Sets the name of the default namespace
     *
     * @param string $namespaceName
     *
     * @return RouterInterface
     */
    public function setDefaultNamespace(string $namespaceName): RouterInterface
    {
        $this->defaultNamespace = $namespaceName;

        return $this;
    }

    /**
     * Sets an array of default paths. If a route is missing a path the router
     * will use the defined here. This method must not be used to set a 404
     * route
     *
     *```php
     * $router->setDefaults(
     *     [
     *         "module" => "common",
     *         "action" => "index",
     *     ]
     * );
     *```
     *
     * @param array $defaults
     *
     * @return RouterInterface
     */
    public function setDefaults(array $defaults): RouterInterface
    {
        // Set a default namespace
        if (isset($defaults['namespace'])) {
            $this->defaultNamespace = (string)$defaults['namespace'];
        }

        // Set a default module
        if (isset($defaults['module'])) {
            $this->defaultModule = (string)$defaults['module'];
        }

        // Set a default controller
        if (isset($defaults['controller'])) {
            $this->defaultController = (string)$defaults['controller'];
        }

        // Set a default action
        if (isset($defaults['action'])) {
            $this->defaultAction = (string)$defaults['action'];
        }

        // Set default parameters
        if (isset($defaults['params'])) {
            $this->defaultParams = $defaults['params'];
        }

        return $this;
    }

    /**
     * @param array $routeIds
     *
     * @return Router
     */
    public function setKeyRouteIds(array $routeIds): Router
    {
        $this->keyRouteIds = $routeIds;

        return $this;
    }

    /**
     * @param array $routeNames
     *
     * @return Router
     */
    public function setKeyRouteNames(array $routeNames): Router
    {
        $this->keyRouteNames = $routeNames;

        return $this;
    }

    /**
     * Sets the URI source. One of the URI_SOURCE_* constants
     *
     * ```php
     * $router->setUriSource(
     *     Router::URI_SOURCE_SERVER_REQUEST_URI
     * );
     * ```
     */
    public function setUriSource(int $uriSource): Router
    {
        $this->uriSource = $uriSource;

        return $this;
    }

    /**
     * Checks if the router matches any of the defined routes
     *
     * @return bool
     */
    public function wasMatched(): bool
    {
        return $this->wasMatched;
    }

    /**
     * @param string $uri
     *
     * @return string
     */
    protected function extractRealUri(string $uri): string
    {
        $urlParts = explode("?", $uri, 2);

        return $urlParts[0];
    }
}
