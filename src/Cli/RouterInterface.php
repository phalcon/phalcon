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

use Phalcon\Cli\Router\RouteInterface;

/**
 * Interface for Phalcon\Cli\Router
 *
 * @psalm-type TDefaults = array{
 *      module?: string,
 *      task?: string,
 *      action?: string,
 *      params?: string
 * }
 */
interface RouterInterface
{
    /**
     * Adds a route to the router on any HTTP method
     *
     * @param string       $pattern
     * @param array|string $paths
     *
     * @return RouteInterface
     */
    public function add(string $pattern, array | string $paths = []): RouteInterface;

    /**
     * Returns processed action name
     *
     * @return string
     */
    public function getActionName(): string;

    /**
     * Returns the route that matches the handled URI
     *
     * @return RouteInterface|null
     */
    public function getMatchedRoute(): RouteInterface | null;

    /**
     * Return the sub expressions in the regular expression matched
     *
     * @return array<array-key, string>
     */
    public function getMatches(): array;

    /**
     * Returns processed module name
     *
     * @return string
     */
    public function getModuleName(): string;

    /**
     * Returns processed extra params
     *
     * @return array
     */
    public function getParams(): array;

    /**
     * Returns a route object by its id
     *
     * @param string $routeId
     *
     * @return RouteInterface| bool
     */
    public function getRouteById(string $routeId): RouteInterface | bool;

    /**
     * Returns a route object by its name
     *
     * @param string $name
     *
     * @return RouteInterface|bool
     */
    public function getRouteByName(string $name): RouteInterface | bool;

    /**
     * Return all the routes defined in the router
     *
     * @return RouteInterface[]
     */
    public function getRoutes(): array;

    /**
     * Returns processed task name
     *
     * @return string
     */
    public function getTaskName(): string;

    /**
     * Handles routing information received from the rewrite engine
     *
     * @param array|string $arguments
     *
     * @return void
     */
    public function handle(array | string $arguments = []): void;

    /**
     * Sets the default action name
     *
     * @param string $actionName
     *
     * @return RouterInterface
     */
    public function setDefaultAction(string $actionName): RouterInterface;

    /**
     * Sets the name of the default module
     *
     * @param string $moduleName
     *
     * @return RouterInterface
     */
    public function setDefaultModule(string $moduleName): RouterInterface;

    /**
     * Sets the default task name
     *
     * @param string $taskName
     *
     * @return RouterInterface
     */
    public function setDefaultTask(string $taskName): RouterInterface;

    /**
     * Sets an array of default paths
     *
     * @param TDefaults $defaults
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
