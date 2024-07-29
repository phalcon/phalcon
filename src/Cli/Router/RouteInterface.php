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

namespace Phalcon\Cli\Router;

/**
 * Interface for Phalcon\Cli\Router\Route
 */
interface RouteInterface
{
    /**
     * Replaces placeholders from pattern returning a valid PCRE regular
     * expression
     *
     * @param string $pattern
     *
     * @return string
     */
    public function compilePattern(string $pattern): string;

    /**
     * Set the routing delimiter
     *
     * @param string $delimiter
     *
     * @return mixed
     */
    public static function delimiter(string $delimiter);

    /**
     * Returns the route's pattern
     *
     * @return string
     */
    public function getCompiledPattern(): string;

    /**
     * Get routing delimiter
     *
     * @return string
     */
    public static function getDelimiter(): string;

    /**
     * Returns the route's description
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Returns the route's name
     *
     * @return string
     */
    public function getName(): string;

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
     * @param string       $pattern
     * @param array|string $paths
     *
     * @return void
     */
    public function reConfigure(string $pattern, array | string $paths = []): void;

    /**
     * Resets the internal route id generator
     *
     * @return void
     */
    public static function reset(): void;

    /**
     * Sets the route's description
     *
     * @param string $description
     *
     * @return RouteInterface
     */
    public function setDescription(string $description): RouteInterface;

    /**
     * Sets the route's name
     *
     * @param string $name
     *
     * @return RouteInterface
     */
    public function setName(string $name): RouteInterface;
}
