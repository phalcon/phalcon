<?php
/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Cli;

use Phalcon\Di\DiInterface;
use Phalcon\Di\AbstractInjectionAware;
use Phalcon\Cli\Router\Route;
use Phalcon\Cli\Router\Exception;
use Phalcon\Cli\Router\RouteInterface;

/**
 * Phalcon\Cli\Router is the standard framework router. Routing is the process
 * of taking a command-line arguments and decomposing it into parameters to
 * determine which module, task, and action of that task should receive the
 * request.
 *
 *```php
 * $router = new \Phalcon\Cli\Router();
 *
 * $router->handle(
 *     [
 *         "module" => "main",
 *         "task"   => "videos",
 *         "action" => "process",
 *     ]
 * );
 *
 * echo $router->getTaskName();
 *```
 */
class Router extends AbstractInjectionAware
{
    protected $action;

    protected $defaultAction = null;

    protected $defaultModule = null;

    /**
     * @var array
     */
    protected $defaultParams = [];

    protected $defaultTask = null;

    protected $matchedRoute;

    protected $matches;

    protected $odule;

    /**
     * @var array
     */
    protected $params = [];

    protected $routes;

    protected $task;

    protected $wasMatched = false;

    /**
     * Phalcon\Cli\Router constructor
     */
    public function __construct(bool $defaultRoutes = true)
    {
        $routes = [];

        if ($defaultRoutes) {
            // Two routes are added by default to match
            // /:task/:action and /:task/:action/:params

            $routes[] = new Route(
                "#^(?::delimiter)?([a-zA-Z0-9\\_\\-]+)[:delimiter]{0,1}$#",
                [
                    "task" => 1
                ]
            );

            $routes[] = new Route(
                "#^(?::delimiter)?([a-zA-Z0-9\\_\\-]+):delimiter([a-zA-Z0-9\\.\\_]+)(:delimiter.*)*$#",
                [
                    "task" =>   1,
                    "action" => 2,
                    "params" => 3
                ]
            );
        }

        $this->routes = $routes;
    }

    /**
     * Adds a route to the router
     *
     *```php
     * $router->add("/about", "About::main");
     *```
     *
     * @param string|array paths
     */
    public function add(string $pattern, $paths = null) : RouteInterface
    {
        $route = new Route($pattern, $paths);
        $this->routes[] = $route;

        return $route;
    }

    /**
     * Returns processed action name
     */
    public function getActionName() : string
    {
        return $this->action;
    }

    /**
     * Returns the route that matches the handled URI
     */
    public function getMatchedRoute() : RouteInterface
    {
        return $this->matchedRoute;
    }

    /**
     * Returns the sub expressions in the regular expression matched
     */
    public function getMatches() : array
    {
        return $this->matches;
    }

    /**
     * Returns processed module name
     */
    public function getModuleName(): ?string
    {
        return $this->module;
    }

    /**
     * Returns processed extra params
     */
    public function getParams() : array
    {
        return $this->params;
    }

    /**
     * Returns a route object by its id
     *
     * @param int id
     */
    public function getRouteById(int $id) : ?RouteInterface
    {
        foreach($this->routes as $route)  {
            if ($route->getRouteId() == $id) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Returns a route object by its name
     */
    public function getRouteByName(string $name) :  ?RouteInterface
    {
        foreach($this->routes as $route) {
            if ($route->getName() === $name) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Returns all the routes defined in the router
     */
    public function getRoutes() : array
    {
        return $this->routes;
    }

    /**
     * Returns processed task name
     */
    public function getTaskName(): string
    {
        return $this->task;
    }

    private function handleString(?string $arg) : array {
        $parts = [];
        foreach( array_reverse($this->routes) as  $route) {
                /**
                 * If the route has parentheses use preg_match
                 */
                $pattern = $route->getCompiledPattern();
                $matches = null;
                if (strpos(pattern, "^")!==false) {
                    $routeFound = preg_match($pattern, $arg, $matches);
                } else {
                    $routeFound = ($pattern === $arg);
                }

                /**
                 * Check for beforeMatch conditions
                 */
                if ($routeFound) {
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
                                $arg,
                                $route,
                                $this
                            ]
                        );
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

                        foreach($paths as $part => $position) {
                            $matchPosition = $matches[$position] ?? null;
                            if ($matchPosition  !== null) {
                                $converter = $converters[$part] ?? null;
                                if ($converter !== null) {
                                        $parts[$part] = call_user_func_array(
                                            $converter,
                                            [$matchPosition]
                                        );
                                }
                                else {
                                    $parts[$part] = $matchPosition;
                                }
                            }
                            else {
                                $converter = $converters[$part] ?? null;
                                if ($converter !== null) {
                                    $parts[$part] = call_user_func_array(
                                        $converter,
                                        [$position]
                                    );
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
        } // foreach
        return [$routeFound,$parts];
    }
    /**
     * Handles routing information received from command-line arguments
     *
     * @param array arguments
     */
    public function handle($arguments = null)
    {
        $this->wasMatched = false;
        $this->matchedRoute = null;
        switch(gettype($arguments)) {
            case "array": 
                $parts = $arguments;
                break;
            case "string":
            case "null":
                list($routeFound,$parts) = $this->handleString($arguments);
                if ($routeFound) {
                    $this->wasMatched = true;
                }
                else {
                    $this->module = $this->defaultModule;
                    $this->task = $this->defaultTask;
                    $this->action = $this->defaultAction;
                    $this->params = $this->defaultParams;
                    return $this;
                }
                break;
            default:
                throw new Exception("Arguments must be an array or string");
                break;
        }
        $moduleName = null;
        $taskName = null;
        $actionName = null;

        /**
         * Check for a module
         */
        $moduleName = $parts["module"] ?? null;
        if ($moduleName !== null) {
            unset($parts["module"]);
        }
        else {
            $moduleName = $this->defaultModule;
        }

        /**
         * Check for a task
         */
        $taskName = $parts["task"] ?? null;
        if ($taskName !== null) {
            unset ($parts["task"]);
        } else {
            $taskName = $this->defaultTask;
        }

        /**
         * Check for an action
         */
        $actionName = $parts["action"] ?? null;
        if ($actionName !== null) {
            unset ($parts["action"]);
        } else {
            $actionName = $this->defaultAction;
        }

        /**
         * Check for an parameters
         */

        $params = $parts["params"] ?? null;
        if ($params !== null) {
            if (!is_array($params)) {
                $strParams = substr( strval($params),1);

                if (!empty($strParams)) {
                    $params = explode(Route::getDelimiter(), $strParams);
                } else {
                    $params = [];
                }
            }

            unset($parts["params"]);
        }

        if(!empty($params)) {
            $params = array_merge($params, $parts);
        } else {
            $params = $parts;
        }

        $this->module = $moduleName;
            $this->task = $taskName;
            $this->action = $actionName;
            $this->params = $params;
    }

    /**
     * Sets the default action name
     */
    public function setDefaultAction(string $actionName)
    {
        $this->defaultAction = $actionName;
    }

    /**
     * Sets the name of the default module
     */
    public function setDefaultModule(string $moduleName)
    {
        $this->defaultModule = $moduleName;
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
     */
    public function setDefaults(array $defaults) : Router
    {
        $module = $defaults["module"] ?? null;
        if ($module !== null) {
            $this->defaultModule = $module;
        }

        // Set a default task
        $task = $defaults["task"] ?? null;
        if ($task !== null)  {
            $this->defaultTask = $task;
        }

        // Set a default action
        $action = $defaults["action"] ?? null;
        if ($action !== null) {
            $this->defaultAction = $action;
        }

        // Set default parameters
        $params = $default["params"] ?? null;
        
        if ($params !== null) {
            $this->defaultParams = $params;
        }

        return $this;
    }

    /**
     * Sets the default controller name
     */
    public function setDefaultTask(string $taskName)
    {
        $this->defaultTask = $taskName;
    }

    /**
     * Checks if the router matches any of the defined routes
     */
    public function wasMatched() : bool
    {
        return $this->wasMatched;
    }
}
