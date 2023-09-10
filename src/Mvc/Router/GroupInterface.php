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

namespace Phalcon\Mvc\Router;

/**
 * Interface for Group Routes
 */
interface GroupInterface
{
    /**
     * Adds a route to the router on any HTTP method
     *
     *```php
     * router->add("/about", "About::index");
     *```
     *
     * @param string       $pattern
     * @param array|string $paths
     * @param array|string $httpMethods
     *
     * @return RouteInterface
     */
    public function add(
        string $pattern,
        array|string $paths = [],
        array|string $httpMethods = []
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is CONNECT
     *
     * @param string       $pattern
     * @param array|string $paths
     *
     * @return RouteInterface
     */
    public function addConnect(
        string $pattern,
        array|string $paths = [],
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is DELETE
     *
     * @param string       $pattern
     * @param array|string $paths
     *
     * @return RouteInterface
     */
    public function addDelete(
        string $pattern,
        array|string $paths = [],
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is GET
     *
     * @param string       $pattern
     * @param array|string $paths
     *
     * @return RouteInterface
     */
    public function addGet(
        string $pattern,
        array|string $paths = [],
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is HEAD
     *
     * @param string       $pattern
     * @param array|string $paths
     *
     * @return RouteInterface
     */
    public function addHead(
        string $pattern,
        array|string $paths = [],
    ): RouteInterface;

    /**
     * Add a route to the router that only match if the HTTP method is OPTIONS
     *
     * @param string       $pattern
     * @param array|string $paths
     *
     * @return RouteInterface
     */
    public function addOptions(
        string $pattern,
        array|string $paths = [],
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is PATCH
     *
     * @param string       $pattern
     * @param array|string $paths
     *
     * @return RouteInterface
     */
    public function addPatch(
        string $pattern,
        array|string $paths = [],
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is POST
     *
     * @param string       $pattern
     * @param array|string $paths
     *
     * @return RouteInterface
     */
    public function addPost(
        string $pattern,
        array|string $paths = [],
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is PURGE
     *
     * @param string       $pattern
     * @param array|string $paths
     *
     * @return RouteInterface
     */
    public function addPurge(
        string $pattern,
        array|string $paths = [],
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is PUT
     *
     * @param string       $pattern
     * @param array|string $paths
     *
     * @return RouteInterface
     */
    public function addPut(
        string $pattern,
        array|string $paths = [],
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is TRACE
     *
     * @param string       $pattern
     * @param array|string $paths
     *
     * @return RouteInterface
     */
    public function addTrace(
        string $pattern,
        array|string $paths = [],
    ): RouteInterface;

    /**
     * Sets a callback that is called if the route is matched.
     * The developer can implement any arbitrary conditions here
     * If the callback returns false the route is treated as not matched
     *
     * @param callable $beforeMatch
     *
     * @return GroupInterface|null
     */
    public function beforeMatch(callable $beforeMatch): ?GroupInterface;

    /**
     * Removes all the pre-defined routes
     *
     * @return void
     */
    public function clear(): void;

    /**
     * Returns the 'before match' callback if any
     *
     * @return callable|null
     */
    public function getBeforeMatch(): callable|null;

    /**
     * Returns the hostname restriction
     *
     * @return string
     */
    public function getHostname(): string;

    /**
     * Returns the common paths defined for this group
     *
     * @return array|string
     */
    public function getPaths(): array|string;

    /**
     * Returns the common prefix for all the routes
     *
     * @return string
     */
    public function getPrefix(): string;

    /**
     * Returns the routes added to the group
     *
     * @return RouteInterface[]
     */
    public function getRoutes(): array;

    /**
     * Set a hostname restriction for all the routes in the group
     *
     * @param string $hostname
     *
     * @return GroupInterface
     */
    public function setHostname(string $hostname): GroupInterface;

    /**
     * Set common paths for all the routes in the group
     *
     * @param array|string $paths
     *
     * @return GroupInterface
     */
    public function setPaths(array|string $paths): GroupInterface;

    /**
     * Set a common uri prefix for all the routes in this group
     *
     * @param string $prefix
     *
     * @return GroupInterface
     */
    public function setPrefix(string $prefix): GroupInterface;
}
