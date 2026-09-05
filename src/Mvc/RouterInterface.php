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

use Phalcon\Contracts\Mvc\MvcTypes;
use Phalcon\Mvc\Router\GroupInterface;
use Phalcon\Mvc\Router\RouteInterface;

/**
 * Interface for Phalcon\Mvc\Router
 *
 * @phpstan-import-type mvc_router_defaults from MvcTypes
 * @phpstan-import-type mvc_router_http_methods from MvcTypes
 * @phpstan-import-type mvc_router_matches from MvcTypes
 * @phpstan-import-type mvc_router_params from MvcTypes
 * @phpstan-import-type mvc_router_paths from MvcTypes
 *
 * The router class carries this member and the framework calls it on the
 * interface. It joins the interface in the next major; until then the tag
 * below records the contract that all implementations meet.
 *
 * @method RouterInterface removeExtraSlashes(bool $remove)
 */
interface RouterInterface
{
    /**
     * Adds a route to the router on any HTTP method
     *
     * @param string $pattern
     * @param mixed  $paths
     * @param mixed  $httpMethods
     * @param int    $position
     *
     *
     * @return RouteInterface
     */
    public function add(
        string $pattern,
        mixed $paths = null,
        mixed $httpMethods = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is CONNECT
     *
     * @param string $pattern
     * @param mixed  $paths
     * @param int    $position
     *
     *
     * @return RouteInterface
     */
    public function addConnect(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is DELETE
     *
     * @param string $pattern
     * @param mixed  $paths
     * @param int    $position
     *
     *
     * @return RouteInterface
     */
    public function addDelete(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is GET
     *
     * @param string $pattern
     * @param mixed  $paths
     * @param int    $position
     *
     *
     * @return RouteInterface
     */
    public function addGet(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is HEAD
     *
     * @param string $pattern
     * @param mixed  $paths
     * @param int    $position
     *
     *
     * @return RouteInterface
     */
    public function addHead(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface;

    /**
     * Add a route to the router that only match if the HTTP method is OPTIONS
     *
     * @param string $pattern
     * @param mixed  $paths
     * @param int    $position
     *
     *
     * @return RouteInterface
     */
    public function addOptions(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is PATCH
     *
     * @param string $pattern
     * @param mixed  $paths
     * @param int    $position
     *
     *
     * @return RouteInterface
     */
    public function addPatch(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is POST
     *
     * @param string $pattern
     * @param mixed  $paths
     * @param int    $position
     *
     *
     * @return RouteInterface
     */
    public function addPost(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is PURGE
     * (Squid and Varnish support)
     *
     * @param string $pattern
     * @param mixed  $paths
     * @param int    $position
     *
     *
     * @return RouteInterface
     */
    public function addPurge(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is PUT
     *
     * @param string $pattern
     * @param mixed  $paths
     * @param int    $position
     *
     *
     * @return RouteInterface
     */
    public function addPut(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is TRACE
     *
     * @param string $pattern
     * @param mixed  $paths
     * @param int    $position
     *
     *
     * @return RouteInterface
     */
    public function addTrace(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface;

    /**
     * Attach Route object to the routes stack.
     *
     * @param RouteInterface $route
     * @param int            $position
     *
     * @return RouterInterface
     */
    public function attach(
        RouteInterface $route,
        int $position = Router::POSITION_LAST
    ): RouterInterface;

    /**
     * Removes all the defined routes
     *
     * @return void
     */
    public function clear(): void;

    /**
     * Returns processed action name
     *
     * @return string
     */
    public function getActionName(): string;

    /**
     * Returns processed controller name
     *
     * @return string
     */
    public function getControllerName(): string;

    /**
     * Returns the route that matches the handled URI
     *
     * @return RouteInterface|null
     */
    public function getMatchedRoute(): RouteInterface | null;

    /**
     * Return the sub expressions in the regular expression matched
     *
     * @return array
     *
     * @phpstan-return mvc_router_matches
     */
    public function getMatches(): array;

    /**
     * Returns processed module name
     *
     * @return string
     */
    public function getModuleName(): string;

    /**
     * Returns processed namespace name
     *
     * @return string
     */
    public function getNamespaceName(): string;

    /**
     * Returns processed extra params
     *
     * @return array
     *
     * @phpstan-return mvc_router_params
     */
    public function getParams(): array;

    /**
     * Returns a route object by its id
     *
     * @param mixed $routeId
     *
     * @return bool|RouteInterface
     */
    public function getRouteById(mixed $routeId): bool | RouteInterface;

    /**
     * Returns a route object by its name
     *
     * @param string $name
     *
     * @return bool|RouteInterface
     */
    public function getRouteByName(string $name): bool | RouteInterface;

    /**
     * Return all the routes defined in the router
     *
     * @return RouteInterface[]
     */
    public function getRoutes(): array;

    /**
     * Handles routing information received from the rewrite engine
     *
     * @param string $uri
     *
     * @return void
     */
    public function handle(string $uri): void;

    /**
     * Loads routes from an array or Phalcon\Config\Config instance.
     *
     * @param mixed $config
     *
     *
     * @return RouterInterface
     */
    public function loadFromConfig(mixed $config): RouterInterface;

    /**
     * Mounts a group of routes in the router
     *
     * @param GroupInterface $group
     *
     * @return RouterInterface
     */
    public function mount(GroupInterface $group): RouterInterface;

    /**
     * Sets the default action name
     *
     * @param string $actionName
     *
     * @return RouterInterface
     */
    public function setDefaultAction(string $actionName): RouterInterface;

    /**
     * Sets the default controller name
     *
     * @param string $controllerName
     *
     * @return RouterInterface
     */
    public function setDefaultController(string $controllerName): RouterInterface;

    /**
     * Sets the name of the default module
     *
     * @param string $moduleName
     *
     * @return RouterInterface
     */
    public function setDefaultModule(string $moduleName): RouterInterface;

    /**
     * Sets an array of default paths
     *
     * @param array $defaults
     *
     * @phpstan-param mvc_router_defaults $defaults
     *
     * @return RouterInterface
     */
    public function setDefaults(array $defaults): RouterInterface;

    /**
     * Check if the router matches any of the defined routes
     *
     * @return bool
     */
    public function wasMatched(): bool;
}
