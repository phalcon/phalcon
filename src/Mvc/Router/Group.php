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

use function is_array;
use function is_string;

/**
 * Helper class to create a group of routes with common attributes
 *
 *```php
 * $router = new \Phalcon\Mvc\Router();
 *
 * //Create a group with a common module and controller
 * $blog = new Group(
 *     [
 *         "module"     => "blog",
 *         "controller" => "index",
 *     ]
 * );
 *
 * //All the routes start with /blog
 * $blog->setPrefix("/blog");
 *
 * //Add a route to the group
 * $blog->add(
 *     "/save",
 *     [
 *         "action" => "save",
 *     ]
 * );
 *
 * //Add another route to the group
 * $blog->add(
 *     "/edit/{id}",
 *     [
 *         "action" => "edit",
 *     ]
 * );
 *
 * //This route maps to a controller different from the default
 * $blog->add(
 *     "/blog",
 *     [
 *         "controller" => "about",
 *         "action"     => "index",
 *     ]
 * );
 *
 * //Add the group to the router
 * $router->mount($blog);
 *```
 */
class Group implements GroupInterface
{
    /**
     * @var callable|null
     */
    protected mixed $beforeMatch = null;

    /**
     * @var string
     */
    protected string $hostname = '';

    /**
     * @var array|string
     */
    protected array|string $paths = [];

    /**
     * @var string
     */
    protected string $prefix = '';

    /**
     * @var array
     */
    protected array $routes = [];

    /**
     * Phalcon\Mvc\Router\Group constructor
     *
     * @param array|string $paths
     */
    public function __construct(array|string $paths = [])
    {
        $this->paths = $paths;

        if (method_exists($this, "initialize")) {
            $this->initialize($paths);
        }
    }

    /**
     * Adds a route to the router on any HTTP method
     *
     *```php
     * $router->add("/about", "About::index");
     *```
     *
     * @param string       $pattern
     * @param array|string $paths = [
     *                            'module => '',
     *                            'controller' => '',
     *                            'action' => '',
     *                            'namespace' => ''
     *                            ]
     * @param array|string $httpMethods
     *
     * @return RouteInterface
     * @throws Exception
     */
    public function add(
        string $pattern,
        array|string $paths = [],
        array|string $httpMethods = []
    ): RouteInterface {
        return $this->addRoute($pattern, $paths, $httpMethods);
    }

    /**
     * Adds a route to the router that only match if the HTTP method is CONNECT
     *
     * @param string       $pattern
     * @param array|string $paths = [
     *                            'module      => '',
     *                            'controller' => '',
     *                            'action'     => '',
     *                            'namespace'  => ''
     *                            ]
     *
     * @return RouteInterface
     * @throws Exception
     */
    public function addConnect(
        string $pattern,
        array|string $paths = []
    ): RouteInterface {
        return $this->addRoute($pattern, $paths, "CONNECT");
    }

    /**
     * Adds a route to the router that only match if the HTTP method is DELETE
     *
     * @param string       $pattern
     * @param array|string $paths = [
     *                            'module      => '',
     *                            'controller' => '',
     *                            'action'     => '',
     *                            'namespace'  => ''
     *                            ]
     *
     * @return RouteInterface
     * @throws Exception
     */
    public function addDelete(
        string $pattern,
        array|string $paths = []
    ): RouteInterface {
        return $this->addRoute($pattern, $paths, "DELETE");
    }

    /**
     * Adds a route to the router that only match if the HTTP method is GET
     *
     * @param string       $pattern
     * @param array|string $paths = [
     *                            'module      => '',
     *                            'controller' => '',
     *                            'action'     => '',
     *                            'namespace'  => ''
     *                            ]
     *
     * @return RouteInterface
     * @throws Exception
     */
    public function addGet(
        string $pattern,
        array|string $paths = []
    ): RouteInterface {
        return $this->addRoute($pattern, $paths, "GET");
    }

    /**
     * Adds a route to the router that only match if the HTTP method is HEAD
     *
     * @param string       $pattern
     * @param array|string $paths = [
     *                            'module      => '',
     *                            'controller' => '',
     *                            'action'     => '',
     *                            'namespace'  => ''
     *                            ]
     *
     * @return RouteInterface
     * @throws Exception
     */
    public function addHead(
        string $pattern,
        array|string $paths = []
    ): RouteInterface {
        return $this->addRoute($pattern, $paths, "HEAD");
    }

    /**
     * Add a route to the router that only match if the HTTP method is OPTIONS
     *
     * @param string       $pattern
     * @param array|string $paths = [
     *                            'module      => '',
     *                            'controller' => '',
     *                            'action'     => '',
     *                            'namespace'  => ''
     *                            ]
     *
     * @return RouteInterface
     * @throws Exception
     */
    public function addOptions(
        string $pattern,
        array|string $paths = []
    ): RouteInterface {
        return $this->addRoute($pattern, $paths, "OPTIONS");
    }

    /**
     * Adds a route to the router that only match if the HTTP method is PATCH
     *
     * @param string       $pattern
     * @param array|string $paths = [
     *                            'module      => '',
     *                            'controller' => '',
     *                            'action'     => '',
     *                            'namespace'  => ''
     *                            ]
     *
     * @return RouteInterface
     * @throws Exception
     */
    public function addPatch(
        string $pattern,
        array|string $paths = []
    ): RouteInterface {
        return $this->addRoute($pattern, $paths, "PATCH");
    }

    /**
     * Adds a route to the router that only match if the HTTP method is POST
     *
     * @param string       $pattern
     * @param array|string $paths = [
     *                            'module      => '',
     *                            'controller' => '',
     *                            'action'     => '',
     *                            'namespace'  => ''
     *                            ]
     *
     * @return RouteInterface
     * @throws Exception
     */
    public function addPost(
        string $pattern,
        array|string $paths = []
    ): RouteInterface {
        return $this->addRoute($pattern, $paths, "POST");
    }

    /**
     * Adds a route to the router that only match if the HTTP method is PURGE
     *
     * @param string       $pattern
     * @param array|string $paths = [
     *                            'module      => '',
     *                            'controller' => '',
     *                            'action'     => '',
     *                            'namespace'  => ''
     *                            ]
     *
     * @return RouteInterface
     * @throws Exception
     */
    public function addPurge(
        string $pattern,
        array|string $paths = []
    ): RouteInterface {
        return $this->addRoute($pattern, $paths, "PURGE");
    }

    /**
     * Adds a route to the router that only match if the HTTP method is PUT
     *
     * @param string       $pattern
     * @param array|string $paths = [
     *                            'module      => '',
     *                            'controller' => '',
     *                            'action'     => '',
     *                            'namespace'  => ''
     *                            ]
     *
     * @return RouteInterface
     * @throws Exception
     */
    public function addPut(
        string $pattern,
        array|string $paths = []
    ): RouteInterface {
        return $this->addRoute($pattern, $paths, "PUT");
    }

    /**
     * Adds a route to the router that only match if the HTTP method is TRACE
     *
     * @param string       $pattern
     * @param array|string $paths = [
     *                            'module      => '',
     *                            'controller' => '',
     *                            'action'     => '',
     *                            'namespace'  => ''
     *                            ]
     *
     * @return RouteInterface
     * @throws Exception
     */
    public function addTrace(
        string $pattern,
        array|string $paths = []
    ): RouteInterface {
        return $this->addRoute($pattern, $paths, "TRACE");
    }

    /**
     * Sets a callback that is called if the route is matched.
     * The developer can implement any arbitrary conditions here
     * If the callback returns false the route is treated as not matched
     *
     * @param callable $beforeMatch
     *
     * @return GroupInterface
     */
    public function beforeMatch(callable $beforeMatch): GroupInterface
    {
        $this->beforeMatch = $beforeMatch;

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
     * Returns the 'before match' callback if any
     *
     * @return callable|null
     */
    public function getBeforeMatch(): callable|null
    {
        return $this->beforeMatch;
    }

    /**
     * Returns the hostname restriction
     *
     * @return string
     */
    public function getHostname(): string
    {
        return $this->hostname;
    }

    /**
     * Returns the common paths defined for this group
     *
     * @return array|string
     */
    public function getPaths(): array|string
    {
        return $this->paths;
    }

    /**
     * Returns the common prefix for all the routes
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Returns the routes added to the group
     *
     * @return RouteInterface[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Set a hostname restriction for all the routes in the group
     *
     * @param string $hostname
     *
     * @return GroupInterface
     */
    public function setHostname(string $hostname): GroupInterface
    {
        $this->hostname = $hostname;

        return $this;
    }

    /**
     * Set common paths for all the routes in the group
     *
     * @param array|string $paths
     *
     * @return GroupInterface
     */
    public function setPaths(array|string $paths): GroupInterface
    {
        $this->paths = $paths;

        return $this;
    }

    /**
     * Set a common uri prefix for all the routes in $this group
     *
     * @param string $prefix
     *
     * @return GroupInterface
     */
    public function setPrefix(string $prefix): GroupInterface
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Adds a route applying the common attributes
     *
     * @param string       $pattern
     * @param array|string $paths = [
     *                            'module => '',
     *                            'controller' => '',
     *                            'action' => '',
     *                            'namespace' => ''
     *                            ]
     * @param array|string $httpMethods
     *
     * @return RouteInterface
     * @throws Exception
     */
    protected function addRoute(
        string $pattern,
        array|string $paths = [],
        array|string $httpMethods = []
    ): RouteInterface {
        /**
         * Check if the paths need to be merged with current paths
         */
        $mergedPaths = $this->paths;
        if (is_array($this->paths)) {
            if (is_string($paths)) {
                $processedPaths = Route::getRoutePaths($paths);
            } else {
                $processedPaths = $paths;
            }

            /**
             * Merge the paths with the default paths
             */
            $mergedPaths = array_merge($this->paths, $processedPaths);
        }

        /**
         * Every route is internally stored as a Phalcon\Mvc\Router\Route
         */
        $route = new Route(
            $this->prefix . $pattern,
            $mergedPaths,
            $httpMethods
        );

        $this->routes[] = $route;

        $route->setGroup($this);

        return $route;
    }
}
