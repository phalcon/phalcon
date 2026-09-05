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
     *
     * @param string      $routePattern
     * @param mixed       $handler
     * @param string|null $name
     *
     * @return CollectionInterface
     */
    public function delete(
        string $routePattern,
        mixed $handler,
        string | null $name = null
    ): CollectionInterface;

    /**
     * Maps a route to a handler that only matches if the HTTP method is GET
     *
     * @param string      $routePattern
     * @param mixed       $handler
     * @param string|null $name
     *
     * @return CollectionInterface
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
     * @return array
     *
     * @phpstan-return mvc_micro_handlers
     */
    public function getHandlers(): array;

    /**
     * Returns the collection prefix if any
     *
     * @return string
     */
    public function getPrefix(): string;

    /**
     * Maps a route to a handler that only matches if the HTTP method is HEAD
     *
     * @param string      $routePattern
     * @param mixed       $handler
     * @param string|null $name
     *
     * @return CollectionInterface
     */
    public function head(
        string $routePattern,
        mixed $handler,
        string | null $name = null
    ): CollectionInterface;

    /**
     * Returns if the main handler must be lazy loaded
     *
     * @return bool
     */
    public function isLazy(): bool;

    /**
     * Maps a route to a handler
     *
     * @param string      $routePattern
     * @param mixed       $handler
     * @param string|null $name
     *
     * @return CollectionInterface
     */
    public function map(
        string $routePattern,
        mixed $handler,
        string | null $name = null
    ): CollectionInterface;

    /**
     * Maps a route to a handler that only matches if the HTTP method is OPTIONS
     *
     * @param string      $routePattern
     * @param mixed       $handler
     * @param string|null $name
     *
     * @return CollectionInterface
     */
    public function options(
        string $routePattern,
        mixed $handler,
        string | null $name = null
    ): CollectionInterface;

    /**
     * Maps a route to a handler that only matches if the HTTP method is PATCH
     *
     * @param string      $routePattern
     * @param mixed       $handler
     * @param string|null $name
     *
     * @return CollectionInterface
     */
    public function patch(
        string $routePattern,
        mixed $handler,
        string | null $name = null
    ): CollectionInterface;

    /**
     * Maps a route to a handler that only matches if the HTTP method is POST
     *
     * @param string      $routePattern
     * @param mixed       $handler
     * @param string|null $name
     *
     * @return CollectionInterface
     */
    public function post(
        string $routePattern,
        mixed $handler,
        string | null $name = null
    ): CollectionInterface;

    /**
     * Maps a route to a handler that only matches if the HTTP method is PUT
     *
     * @param string      $routePattern
     * @param mixed       $handler
     * @param string|null $name
     *
     * @return CollectionInterface
     */
    public function put(
        string $routePattern,
        mixed $handler,
        string | null $name = null
    ): CollectionInterface;

    /**
     * Sets the main handler
     *
     * @param mixed $handler
     * @param bool  $isLazy
     *
     * @return CollectionInterface
     */
    public function setHandler(mixed $handler, bool $isLazy = false): CollectionInterface;

    /**
     * Sets if the main handler must be lazy loaded
     *
     * @param bool $isLazy
     *
     * @return CollectionInterface
     */
    public function setLazy(bool $isLazy): CollectionInterface;

    /**
     * Sets a prefix for all routes added to the collection
     *
     * @param string $prefix
     *
     * @return CollectionInterface
     */
    public function setPrefix(string $prefix): CollectionInterface;
}
