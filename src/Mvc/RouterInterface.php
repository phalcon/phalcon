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
     */
    public function add(
        string $pattern,
        mixed $paths = null,
        mixed $httpMethods = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is CONNECT
     */
    public function addConnect(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is DELETE
     */
    public function addDelete(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is GET
     */
    public function addGet(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is HEAD
     */
    public function addHead(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface;

    /**
     * Add a route to the router that only match if the HTTP method is OPTIONS
     */
    public function addOptions(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is PATCH
     */
    public function addPatch(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is POST
     */
    public function addPost(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is PURGE
     * (Squid and Varnish support)
     */
    public function addPurge(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is PUT
     */
    public function addPut(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is TRACE
     */
    public function addTrace(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface;

    /**
     * Attach Route object to the routes stack.
     */
    public function attach(
        RouteInterface $route,
        int $position = Router::POSITION_LAST
    ): RouterInterface;

    /**
     * Removes all the defined routes
     */
    public function clear(): void;

    /**
     * Returns processed action name
     */
    public function getActionName(): string;

    /**
     * Returns processed controller name
     */
    public function getControllerName(): string;

    /**
     * Returns the route that matches the handled URI
     */
    public function getMatchedRoute(): RouteInterface | null;

    /**
     * Return the sub expressions in the regular expression matched
     *
     * @phpstan-return mvc_router_matches
     */
    public function getMatches(): array;

    /**
     * Returns processed module name
     */
    public function getModuleName(): string;

    /**
     * Returns processed namespace name
     */
    public function getNamespaceName(): string;

    /**
     * Returns processed extra params
     *
     * @phpstan-return mvc_router_params
     */
    public function getParams(): array;

    /**
     * Returns a route object by its id
     */
    public function getRouteById(mixed $routeId): bool | RouteInterface;

    /**
     * Returns a route object by its name
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
     */
    public function handle(string $uri): void;

    /**
     * Loads routes from an array or Phalcon\Config\Config instance.
     */
    public function loadFromConfig(mixed $config): RouterInterface;

    /**
     * Mounts a group of routes in the router
     */
    public function mount(GroupInterface $group): RouterInterface;

    /**
     * Sets the default action name
     */
    public function setDefaultAction(string $actionName): RouterInterface;

    /**
     * Sets the default controller name
     */
    public function setDefaultController(string $controllerName): RouterInterface;

    /**
     * Sets the name of the default module
     */
    public function setDefaultModule(string $moduleName): RouterInterface;

    /**
     * Sets an array of default paths
     *
     * @phpstan-param mvc_router_defaults $defaults
     */
    public function setDefaults(array $defaults): RouterInterface;

    /**
     * Check if the router matches any of the defined routes
     */
    public function wasMatched(): bool;
}
