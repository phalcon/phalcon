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
 * Interface for Phalcon\Mvc\Router\Route
 */
interface RouteInterface
{
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
     * @param string            $pattern
     * @param array|string|null $paths
     *
     * @return void
     */
    public function reConfigure(
        string $pattern,
        array | string | null $paths = null
    ): void;

    /**
     * Resets the internal route id generator
     *
     * @return void
     */
    public static function reset(): void;

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
     * @param array|string $httpMethods
     *
     * @return RouteInterface
     */
    public function setHttpMethods(array | string $httpMethods): RouteInterface;

    /**
     * Sets the route's name
     *
     * @param string $name
     *
     * @return RouteInterface
     */
    public function setName(string $name): RouteInterface;

    /**
     * Set one or more HTTP methods that constraint the matching of the route
     *
     * @param array|string $httpMethods
     *
     * @return RouteInterface
     */
    public function via(array | string $httpMethods): RouteInterface;
}
