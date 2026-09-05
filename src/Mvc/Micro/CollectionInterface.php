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

namespace Phalcon\Mvc\Micro;

use Phalcon\Contracts\Mvc\MvcTypes;

/**
 * Interface for Phalcon\Mvc\Micro\Collection
 *
 * @phpstan-import-type mvc_micro_handlers from MvcTypes
 */
interface CollectionInterface
{
    /**
     * Maps a route to a handler that only matches if the HTTP method is DELETE
     */
    public function delete(
        string $routePattern,
        mixed $handler,
        string | null $name = null
    ): CollectionInterface;

    /**
     * Maps a route to a handler that only matches if the HTTP method is GET
     */
    public function get(
        string $routePattern,
        mixed $handler,
        string | null $name = null
    ): CollectionInterface;

    /**
     * Returns the main handler
     *
     * @return mixed
     */
    public function getHandler();

    /**
     * Returns the registered handlers
     *
     * @phpstan-return mvc_micro_handlers
     */
    public function getHandlers(): array;

    /**
     * Returns the collection prefix if any
     */
    public function getPrefix(): string;

    /**
     * Maps a route to a handler that only matches if the HTTP method is HEAD
     */
    public function head(
        string $routePattern,
        mixed $handler,
        string | null $name = null
    ): CollectionInterface;

    /**
     * Returns if the main handler must be lazy loaded
     */
    public function isLazy(): bool;

    /**
     * Maps a route to a handler
     */
    public function map(
        string $routePattern,
        mixed $handler,
        string | null $name = null
    ): CollectionInterface;

    /**
     * Maps a route to a handler that only matches if the HTTP method is OPTIONS
     */
    public function options(
        string $routePattern,
        mixed $handler,
        string | null $name = null
    ): CollectionInterface;

    /**
     * Maps a route to a handler that only matches if the HTTP method is PATCH
     */
    public function patch(
        string $routePattern,
        mixed $handler,
        string | null $name = null
    ): CollectionInterface;

    /**
     * Maps a route to a handler that only matches if the HTTP method is POST
     */
    public function post(
        string $routePattern,
        mixed $handler,
        string | null $name = null
    ): CollectionInterface;

    /**
     * Maps a route to a handler that only matches if the HTTP method is PUT
     */
    public function put(
        string $routePattern,
        mixed $handler,
        string | null $name = null
    ): CollectionInterface;

    /**
     * Sets the main handler
     */
    public function setHandler(mixed $handler, bool $isLazy = false): CollectionInterface;

    /**
     * Sets if the main handler must be lazy loaded
     */
    public function setLazy(bool $isLazy): CollectionInterface;

    /**
     * Sets a prefix for all routes added to the collection
     */
    public function setPrefix(string $prefix): CollectionInterface;
}
