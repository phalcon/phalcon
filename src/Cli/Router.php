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

namespace Phalcon\Cli;

use Phalcon\Cli\Router\Exception;
use Phalcon\Cli\Router\Route;
use Phalcon\Cli\Router\RouteInterface;
use Phalcon\Di\AbstractInjectionAware;

use function array_reverse;
use function is_array;
use function str_contains;

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
class Router extends AbstractInjectionAware implements RouterInterface
{
    /**
     * @var string
     */
    protected string $action = "";

    /**
     * @var string
     */
    protected string $defaultAction = "";

    /**
     * @var string
     */
    protected string $defaultModule = "";

    /**
     * @var array
     */
    protected array $defaultParams = [];

    /**
     * @var string
     */
    protected string $defaultTask = "";

    /**
     * @var RouteInterface|null
     */
    protected ?RouteInterface $matchedRoute = null;

    /**
     * @var array
     */
    protected array $matches = [];

    /**
     * @var string
     */
    protected string $module = "";

    /**
     * @var array
     */
    protected array $parameters = [];

    /**
     * @var array
     */
    protected array $routes = [];

    /**
     * @var string
     */
    protected string $task = "";

    /**
     * @var bool
     */
    protected bool $wasMatched = false;

    /**
     * Phalcon\Cli\Router constructor
     *
     * @param bool $defaultRoutes
     */
    public function __construct(bool $defaultRoutes = true)
    {
        if (true === $defaultRoutes) {
            // Two routes are added by default to match
            // /:task/:action and /:task/:action/:params
            $this->add(
                "#^(?::delimiter)?([a-zA-Z0-9\\_\\-]+)[:delimiter]{0,1}$#",
                [
                    "task" => 1
                ]
            );

            $this->add(
                "#^(?::delimiter)?([a-zA-Z0-9\\_\\-]+):delimiter([a-zA-Z0-9\\.\\_]+)(:delimiter.*)*$#",
                [
                    "task"   => 1,
                    "action" => 2,
                    "params" => 3,
                ]
            );
        }
    }

    /**
     * Adds a route to the router
     *
     *```php
     * $router->add("/about", "About::main");
     *```
     *
     * @param string       $pattern
     * @param array|string $paths
     *
     * @return RouteInterface
     */
    public function add(string $pattern, array|string $paths = []): RouteInterface
    {
        $route   = new Route($pattern, $paths);
        $routeId = $route->getRouteId();

        $this->routes[$routeId] = $route;

        return $route;
    }

    /**
     * Returns processed action name
     *
     * @return string
     */
    public function getActionName(): string
    {
        return $this->action;
    }

    /**
     * Returns the route that matches the handled URI
     *
     * @return RouteInterface|null
     */
    public function getMatchedRoute(): RouteInterface|null
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
     * Returns processed module name
     *
     * @return string
     */
    public function getModuleName(): string
    {
        return $this->module;
    }

    /**
     * Returns processed extra params
     *
     * @return array
     * @todo deprecate this in future versions
     */
    public function getParams(): array
    {
        return $this->getParameters();
    }

    /**
     * Returns processed extra params
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Returns a route object by its id
     *
     * @param string $routeId
     *
     * @return RouteInterface|bool
     */
    public function getRouteById(string $routeId): RouteInterface|bool
    {
        return $this->routes[$routeId] ?? false;
    }

    /**
     * Returns a route object by its name
     *
     * @param string $name
     *
     * @return RouteInterface|bool
     */
    public function getRouteByName(string $name): RouteInterface|bool
    {
        /** @var RouteInterface $route */
        foreach ($this->routes as $route) {
            if ($name === $route->getName()) {
                return $route;
            }
        }

        return false;
    }

    /**
     * Returns all the routes defined in the router
     *
     * @return Route[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Returns processed task name
     *
     * @return string
     */
    public function getTaskName(): string
    {
        return $this->task;
    }

    /**
     * Handles routing information received from command-line arguments
     *
     * @param array|string $arguments
     *
     * @return void
     * @throws Exception
     */
    public function handle(array|string $arguments = []): void
    {
        $routeFound         = false;
        $parts              = [];
        $matches            = [];
        $this->wasMatched   = false;
        $this->matchedRoute = null;

        if (true !== is_array($arguments)) {
            $reverseRoutes = array_reverse($this->routes);
            foreach ($reverseRoutes as $route) {
                /**
                 * If the route has parentheses use preg_match
                 */
                $pattern = $route->getCompiledPattern();

                if (str_contains($pattern, "^")) {
                    $routeFound = (bool) preg_match($pattern, $arguments, $matches);
                } else {
                    $routeFound = $pattern === $arguments;
                }

                /**
                 * Check for beforeMatch conditions
                 */
                if (true === $routeFound) {
                    $beforeMatch = $route->getBeforeMatch();

                    if (null !== $beforeMatch) {
                        /**
                         * Check first if the callback is callable
                         */
                        if (true !== is_callable($beforeMatch)) {
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
                                $arguments,
                                $route,
                                $this
                            ]
                        );
                    }
                }

                if (true === $routeFound) {
                    /**
                     * Start from the default paths
                     */
                    $paths = $route->getPaths();
                    $parts = $paths;

                    /**
                     * Check if the matches has variables
                     */
                    if (true !== empty($matches)) {
                        /**
                         * Get the route converters if any
                         */
                        $converters = $route->getConverters();

                        foreach ($paths as $part => $position) {
                            if (true === isset($matches[$position])) {
                                $matchPosition = $matches[$position];
                                /**
                                 * Check if the part has a converter
                                 */
                                if (true === isset($converters[$part])) {
                                    $parts[$part] = call_user_func_array(
                                        $converters[$part],
                                        [$matchPosition]
                                    );
                                } else {
                                    /**
                                     * Update the parts if there is no converter
                                     */
                                    $parts[$part] = $matchPosition;
                                }
                            } else {
                                /**
                                 * Apply the converters anyway
                                 */
                                if (true === isset($converters[$part])) {
                                    $parts[$part] = call_user_func_array(
                                        $converters[$part],
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
            }

            /**
             * Update the wasMatched property indicating if the route was
             * matched
             */
            if (true === $routeFound) {
                $this->wasMatched = true;
            } else {
                $this->wasMatched = false;

                /**
                 * The route wasn't found, try to use the not-found paths
                 */
                $this->module     = $this->defaultModule;
                $this->task       = $this->defaultTask;
                $this->action     = $this->defaultAction;
                $this->parameters = $this->defaultParams;
            }
        } else {
            $parts = $arguments;
        }

        /**
         * Check for a module
         */
        $moduleName = $parts["module"] ?? $this->defaultModule;
        if (true === isset($parts["module"])) {
            unset($parts["module"]);
        }

        /**
         * Check for a task
         */
        $taskName = $parts["task"] ?? $this->defaultTask;
        if (true === isset($parts["task"])) {
            unset($parts["task"]);
        }

        /**
         * Check for an action
         */
        $actionName = $parts["action"] ?? $this->defaultAction;
        if (true === isset($parts["action"])) {
            unset($parts["action"]);
        }

        /**
         * Check for parameters
         */
        if (true === isset($parts["params"])) {
            $params = $parts["params"];
            if (true !== is_array($params)) {
                $strParams = substr((string) $params, 1);

                if ($strParams) {
                    $params = explode(Route::getDelimiter(), $strParams);
                } else {
                    $params = [];
                }
            }

            unset($parts["params"]);
        }

        if (true !== empty($params)) {
            $params = array_merge($params, $parts);
        } else {
            $params = $parts;
        }

        $this->module     = $moduleName;
        $this->task       = $taskName;
        $this->action     = $actionName;
        $this->parameters = $params;
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
        $this->defaultModule = $defaults["module"] ?? $this->defaultModule;
        $this->defaultTask   = $defaults["task"] ?? $this->defaultTask;
        $this->defaultAction = $defaults["action"] ?? $this->defaultAction;
        $this->defaultParams = $defaults["params"] ?? $this->defaultParams;

        return $this;
    }

    /**
     * Sets the default controller name
     *
     * @param string $taskName
     *
     * @return RouterInterface
     */
    public function setDefaultTask(string $taskName): RouterInterface
    {
        $this->defaultTask = $taskName;

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
}
