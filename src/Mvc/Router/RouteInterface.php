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

use Phalcon\Contracts\Mvc\MvcTypes;

/**
 * Interface for Phalcon\Mvc\Router\Route
 *
 * @phpstan-import-type mvc_router_http_methods from MvcTypes
 * @phpstan-import-type mvc_router_paths from MvcTypes
 * @phpstan-import-type mvc_router_converters from MvcTypes
 * @phpstan-import-type mvc_router_reversed_paths from MvcTypes
 *
 * The route class carries these four members, and the router calls them on
 * every route it holds. They join the interface in the next major; until
 * then the tags below record the contract that all implementations meet.
 *
 * @method RouteInterface        beforeMatch(callable $callback)
 * @method callable|null         getBeforeMatch()
 * @method string|null           getCompiledHostName()
 * @method mvc_router_converters getConverters()
 * @method callable|null         getMatch()
 */
interface RouteInterface
{
    /**
     * Resets the internal route id generator
     *
     * @return void
     */
    public static function reset(): void;
    /**
     * Replaces placeholders from pattern returning a valid PCRE regular expression
     *
     * @param string $pattern
     *
     * @return string
     */
    public function compilePattern(string $pattern): string;

    /**
     * Adds a converter to perform an additional transformation for certain parameter.
     *
     * @param string $name
     * @param mixed  $converter
     *
     * @return RouteInterface
     */
    public function convert(string $name, mixed $converter): RouteInterface;

    /**
     * Returns the route's pattern
     *
     * @return string
     */
    public function getCompiledPattern(): string;

    /**
     * Returns the hostname restriction if any
     *
     * @return string|null
     */
    public function getHostname(): string | null;

    /**
     * Returns the HTTP methods that constraint matching the route
     *
     * @return array|string|null
     *
     * @phpstan-return mvc_router_http_methods|string|null
     */
    public function getHttpMethods(): array | string | null;

    /**
     * Returns the route's name
     *
     * @return string|null
     */
    public function getName(): string | null;

    /**
     * Returns the paths
     *
     * @return array
     *
     * @phpstan-return mvc_router_paths
     */
    public function getPaths(): array;

    /**
     * Returns the route's pattern
     *
     * @return string
     */
    public function getPattern(): string;

    /**
     * Returns the paths using positions as keys and names as values
     *
     * @return array
     *
     * @phpstan-return mvc_router_reversed_paths
     */
    public function getReversedPaths(): array;

    /**
     * Returns the route's id
     *
     * @return string
     */
    public function getRouteId(): string;

    /**
     * Reconfigure the route adding a new pattern and a set of paths
     *
     * @param string $pattern
     * @param mixed  $paths
     *
     *
     * @return void
     */
    public function reConfigure(
        string $pattern,
        mixed $paths = null
    ): void;

    /**
     * Sets a hostname restriction to the route
     *
     * @param string $hostname
     *
     * @return RouteInterface
     */
    public function setHostname(string $hostname): RouteInterface;

    /**
     * Sets a set of HTTP methods that constraint the matching of the route
     *
     * @param mixed $httpMethods
     *
     *
     * @return RouteInterface
     */
    public function setHttpMethods(mixed $httpMethods): RouteInterface;

    /**
     * Sets the route's name
     *
     * @param string $name
     *
     * @return RouteInterface
     */
    public function setName(string $name): RouteInterface;

    /**
     * Sets the route's id (intended for restoring cached routes)
     *
     * @param string $routeId
     *
     * @return RouteInterface
     */
    public function setRouteId(string $routeId): RouteInterface;

    /**
     * Set one or more HTTP methods that constraint the matching of the route
     *
     * @param mixed $httpMethods
     *
     *
     * @return RouteInterface
     */
    public function via(mixed $httpMethods): RouteInterface;
}
