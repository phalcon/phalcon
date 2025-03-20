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
 *```php
 * $router = new \Phalcon\Mvc\Router();
 *
 * // Create a group with a common module and controller
 * $blog = new Group(
 *     [
 *         "module"     => "blog",
 *         "controller" => "index",
 *     ]
 * );
 *
 * // All the routes start with /blog
 * $blog->setPrefix("/blog");
 *
 * // Add a route to the group
 * $blog->add(
 *     "/save",
 *     [
 *         "action" => "save",
 *     ]
 * );
 *
 * // Add another route to the group
 * $blog->add(
 *     "/edit/{id}",
 *     [
 *         "action" => "edit",
 *     ]
 * );
 *
 * // This route maps to a controller different than the default
 * $blog->add(
 *     "/blog",
 *     [
 *         "controller" => "about",
 *         "action"     => "index",
 *     ]
 * );
 *
 * // Add the group to the router
 * $router->mount($blog);
 *```
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
     * @param string            $pattern
     * @param array|string|null $paths
     * @param array|string|null $httpMethods
     *
     * @return RouteInterface
     */
    public function add(
        string $pattern,
        array | string | null $paths = null,
        array | string | null $httpMethods = null
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is CONNECT
     *
     * @param string            $pattern
     * @param array|string|null $paths
     *
     * @return RouteInterface
     */
    public function addConnect(
        string $pattern,
        array | string | null $paths = null
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is DELETE
     *
     * @param string            $pattern
     * @param array|string|null $paths
     *
     * @return RouteInterface
     */
    public function addDelete(
        string $pattern,
        array | string | null $paths = null
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is GET
     *
     * @param string            $pattern
     * @param array|string|null $paths
     *
     * @return RouteInterface
     */
    public function addGet(
        string $pattern,
        array | string | null $paths = null
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is HEAD
     *
     * @param string            $pattern
     * @param array|string|null $paths
     *
     * @return RouteInterface
     */
    public function addHead(
        string $pattern,
        array | string | null $paths = null
    ): RouteInterface;

    /**
     * Add a route to the router that only match if the HTTP method is OPTIONS
     *
     * @param string            $pattern
     * @param array|string|null $paths
     *
     * @return RouteInterface
     */
    public function addOptions(
        string $pattern,
        array | string | null $paths = null
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is PATCH
     *
     * @param string            $pattern
     * @param array|string|null $paths
     *
     * @return RouteInterface
     */
    public function addPatch(
        string $pattern,
        array | string | null $paths = null
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is POST
     *
     * @param string            $pattern
     * @param array|string|null $paths
     *
     * @return RouteInterface
     */
    public function addPost(
        string $pattern,
        array | string | null $paths = null
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is PURGE
     *
     * @param string            $pattern
     * @param array|string|null $paths
     *
     * @return RouteInterface
     */
    public function addPurge(
        string $pattern,
        array | string | null $paths = null
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is PUT
     *
     * @param string     $pattern
     * @param mixed|null $paths
     *
     * @return RouteInterface
     */
    public function addPut(
        string $pattern,
        mixed $paths = null
    ): RouteInterface;

    /**
     * Adds a route to the router that only match if the HTTP method is TRACE
     *
     * @param string            $pattern
     * @param array|string|null $paths
     *
     * @return RouteInterface
     */
    public function addTrace(
        string $pattern,
        array | string | null $paths = null
    ): RouteInterface;

    /**
     * Sets a callback that is called if the route is matched.
     * The developer can implement any arbitrary conditions here
     * If the callback returns false the route is treated as not matched
     *
     * @param callable $beforeMatch
     *
     * @return GroupInterface
     */
    public function beforeMatch(callable $beforeMatch): GroupInterface;

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
    public function getBeforeMatch(): callable | null;

    /**
     * Returns the hostname restriction
     *
     * @return string|null
     */
    public function getHostname(): string | null;

    /**
     * Returns the common paths defined for this group
     *
     * @return array|string|null
     */
    public function getPaths(): array | string | null;

    /**
     * Returns the common prefix for all the routes
     *
     * @return string|null
     */
    public function getPrefix(): string | null;

    /**
     * Returns the routes added to the group
     *
     * @return array
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
    public function setPaths(array | string $paths): GroupInterface;

    /**
     * Set a common uri prefix for all the routes in this group
     *
     * @param string $prefix
     *
     * @return GroupInterface
     */
    public function setPrefix(string $prefix): GroupInterface;
}
