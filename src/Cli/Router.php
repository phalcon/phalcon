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

use Phalcon\Cli\Router\Exceptions\BeforeMatchNotCallable;
use Phalcon\Cli\Router\Exceptions\RouterArgumentsInvalidType;
use Phalcon\Cli\Router\Route;
use Phalcon\Cli\Router\RouteInterface;
use Phalcon\Di\AbstractInjectionAware;

use function array_merge;
use function array_reverse;
use function call_user_func_array;
use function explode;
use function is_array;
use function is_callable;
use function preg_match;
use function substr;

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
 * @phpstan-import-type TDefaults from RouterInterface
 */
class Router extends AbstractInjectionAware implements RouterInterface
{
    protected string $action = "";
    protected string $defaultAction = "";
    protected string $defaultModule = "";
    protected array $defaultParams = [];
    protected string $defaultTask = "";
    protected ?RouteInterface $matchedRoute = null;
    /**
     * @var array<array-key, string>
     */
    protected array $matches = [];
    protected string $module = "";
    protected array $parameters = [];
    protected array $routes = [];
    protected string $task = "";
    protected bool $wasMatched = false;

    /**
     * Phalcon\Cli\Router constructor
     */
    public function __construct(bool $defaultRoutes = true)
    {
        if (true === $defaultRoutes) {
            // Two routes are added by default to match
            // /:task/:action and /:task/:action/:params
            $this->add(
                "#^(?::delimiter)?([a-zA-Z0-9\\_\\-]+)[:delimiter]{0,1}$#",
                [
                    "task" => 1,
                ]
            );

            $this->add(
                "#^(?::delimiter)?([a-zA-Z0-9\\_\\-]+):delimiter([a-zA-Z0-9\\.\\_]+)(:delimiter.*)?$#",
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
     * @phpstan-param array|string|null $paths
     */
    public function add(string $pattern, mixed $paths = null): RouteInterface
    {
        $route   = new Route($pattern, $paths);
        $this->routes[$route->getRouteId()] = $route;

        return $route;
    }

    /**
     * Returns processed action name
     */
    public function getActionName(): string
    {
        return $this->action;
    }

    /**
     * Returns the route that matches the handled URI
     */
    public function getMatchedRoute(): RouteInterface | null
    {
        return $this->matchedRoute;
    }

    /**
     * Returns the sub expressions in the regular expression matched
     *
     * @return array<array-key, string>
     */
    public function getMatches(): array
    {
        return $this->matches;
    }

    /**
     * Returns processed module name
     */
    public function getModuleName(): string
    {
        return $this->module;
    }

    /**
     * Returns processed extra params
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Returns processed extra params
     *
     * @deprecated Use {@see getParameters()} instead.
     */
    public function getParams(): array
    {
        return $this->getParameters();
    }

    /**
     * Returns a route object by its id
     */
    public function getRouteById(mixed $id): bool | RouteInterface
    {
        return $this->routes[$id] ?? false;
    }

    /**
     * Returns a route object by its name
     */
    public function getRouteByName(string $name): bool | RouteInterface
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
     */
    public function getTaskName(): string
    {
        return $this->task;
    }

    /**
     * Handles routing information received from command-line arguments
     *
     * @param array|string|null $arguments
     */
    public function handle(mixed $arguments = null)
    {
        $routeFound         = false;
        $parts              = [];
        $params             = [];
        $matches            = null;
        $this->wasMatched   = false;
        $this->matchedRoute = null;

        if (!is_array($arguments)) {
            if (!is_string($arguments) && $arguments !== null) {
                throw new RouterArgumentsInvalidType(gettype($arguments));
            }

            /**
             * Zephir gives `preg_match()` the null subject as an empty
             * string. PHP rejects it, so make the empty string explicit.
             */
            $arguments = (string) $arguments;

            $reverseRoutes = array_reverse($this->routes);
            foreach ($reverseRoutes as $route) {
                /**
                 * If the route has parentheses use preg_match
                 */
                $pattern = $route->getCompiledPattern();

                if (str_contains($pattern, "^")) {
                    $routeFound = (bool)preg_match($pattern, $arguments, $matches);
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
                        if (!is_callable($beforeMatch)) {
                            throw new BeforeMatchNotCallable($route->getPattern());
                        }

                        /**
                         * Check first if the callback is callable
                         */
                        $routeFound = call_user_func_array(
                            $beforeMatch,
                            [
                                $arguments,
                                $route,
                                $this,
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
                    if (!empty($matches)) {
                        /**
                         * Get the route converters if any
                         */
                        $converters = $route->getConverters();

                        foreach ($paths as $part => $position) {
                            if (isset($matches[$position])) {
                                $matchPosition = $matches[$position];
                                /**
                                 * Check if the part has a converter
                                 */
                                if (isset($converters[$part])) {
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
                                if (isset($converters[$part])) {
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

                return $this;
            }
        } else {
            $parts = $arguments;
        }

        /**
         * Check for a module
         */
        $moduleName = $parts["module"] ?? $this->defaultModule;
        if (isset($parts["module"])) {
            unset($parts["module"]);
        }

        /**
         * Check for a task
         */
        $taskName = $parts["task"] ?? $this->defaultTask;
        if (isset($parts["task"])) {
            unset($parts["task"]);
        }

        /**
         * Check for an action
         */
        $actionName = $parts["action"] ?? $this->defaultAction;
        if (isset($parts["action"])) {
            unset($parts["action"]);
        }

        /**
         * Check for parameters
         */
        if (isset($parts["params"])) {
            $params = $parts["params"];
            if (!is_array($params)) {
                $strParams = substr((string)$params, 1);

                if ($strParams) {
                    $params = explode(Route::getDelimiter(), $strParams);
                } else {
                    $params = [];
                }
            }

            unset($parts["params"]);
        }

        if (!empty($params)) {
            $params = array_merge($params, $parts);
        } else {
            $params = $parts;
        }

        $this->module     = $moduleName;
        $this->task       = $taskName;
        $this->action     = $actionName;
        $this->parameters = $params;

        return $this;
    }

    /**
     * Sets the default action name
     */
    public function setDefaultAction(string $actionName): static
    {
        $this->defaultAction = $actionName;

        return $this;
    }

    /**
     * Sets the name of the default module
     */
    public function setDefaultModule(string $moduleName): static
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
     * @param TDefaults $defaults
     */
    public function setDefaults(array $defaults): static
    {
        $this->defaultModule = $defaults["module"] ?? $this->defaultModule;
        $this->defaultTask   = $defaults["task"] ?? $this->defaultTask;
        $this->defaultAction = $defaults["action"] ?? $this->defaultAction;
        $this->defaultParams = $defaults["params"] ?? $this->defaultParams;

        return $this;
    }

    /**
     * Sets the default controller name
     */
    public function setDefaultTask(string $taskName): static
    {
        $this->defaultTask = $taskName;

        return $this;
    }

    /**
     * Checks if the router matches any of the defined routes
     */
    public function wasMatched(): bool
    {
        return $this->wasMatched;
    }
}
