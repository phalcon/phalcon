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
 * Groups Micro-Mvc handlers as controllers
 *
 *```php
 * $app = new \Phalcon\Mvc\Micro();
 *
 * $collection = new Collection();
 *
 * $collection->setHandler(
 *     new PostsController()
 * );
 *
 * $collection->get('/posts/edit/{id}', 'edit');
 *
 * $app->mount($collection);
 *```
 *
 * @phpstan-import-type mvc_micro_handlers from MvcTypes
 */
class Collection implements CollectionInterface
{
    /**
     * @var callable
     */
    protected mixed $handler;
    /**
     * @phpstan-var mvc_micro_handlers
     */
    protected array $handlers = [];
    protected bool $isLazy    = false;
    protected string $prefix  = '';

    /**
     * Maps a route to a handler that only matches if the HTTP method is DELETE.
     */
    public function delete(
        string $routePattern,
        mixed $handler,
        string | null $name = null
    ): CollectionInterface {
        $this->addMap('DELETE', $routePattern, $handler, $name);

        return $this;
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is GET.
     */
    public function get(
        string $routePattern,
        mixed $handler,
        string | null $name = null
    ): CollectionInterface {
        $this->addMap('GET', $routePattern, $handler, $name);

        return $this;
    }

    /**
     * Returns the main handler
     *
     * @return mixed
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Returns the registered handlers
     *
     * @phpstan-return mvc_micro_handlers
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }

    /**
     * Returns the collection prefix if any
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is HEAD.
     */
    public function head(
        string $routePattern,
        mixed $handler,
        string | null $name = null
    ): CollectionInterface {
        $this->addMap('HEAD', $routePattern, $handler, $name);

        return $this;
    }

    /**
     * Returns if the main handler must be lazy loaded
     */
    public function isLazy(): bool
    {
        return $this->isLazy;
    }

    /**
     * Maps a route to a handler.
     */
    public function map(
        string $routePattern,
        mixed $handler,
        string | null $name = null
    ): CollectionInterface {
        $this->addMap('', $routePattern, $handler, $name);

        return $this;
    }

    /**
     * Maps a route to a handler via methods.
     *
     * ```php
     * $collection->mapVia(
     *     '/test',
     *     'indexAction',
     *     ['POST', 'GET'],
     *     'test'
     * );
     * ```
     */
    public function mapVia(
        string $routePattern,
        mixed $handler,
        mixed $method,
        string | null $name = null
    ): CollectionInterface {
        $this->addMap($method, $routePattern, $handler, $name);

        return $this;
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is
     * OPTIONS.
     */
    public function options(
        string $routePattern,
        mixed $handler,
        string | null $name = null
    ): CollectionInterface {
        $this->addMap('OPTIONS', $routePattern, $handler, $name);

        return $this;
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is PATCH.
     */
    public function patch(
        string $routePattern,
        mixed $handler,
        string | null $name = null
    ): CollectionInterface {
        $this->addMap('PATCH', $routePattern, $handler, $name);

        return $this;
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is POST.
     */
    public function post(
        string $routePattern,
        mixed $handler,
        string | null $name = null
    ): CollectionInterface {
        $this->addMap('POST', $routePattern, $handler, $name);

        return $this;
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is PUT.
     */
    public function put(
        string $routePattern,
        mixed $handler,
        string | null $name = null
    ): CollectionInterface {
        $this->addMap('PUT', $routePattern, $handler, $name);

        return $this;
    }

    /**
     * Sets the main handler.
     */
    public function setHandler(mixed $handler, bool $isLazy = false): CollectionInterface
    {
        /** @var callable $handler */
        $this->handler = $handler;
        $this->isLazy  = $isLazy;

        return $this;
    }

    /**
     * Sets if the main handler must be lazy loaded
     */
    public function setLazy(bool $isLazy): CollectionInterface
    {
        $this->isLazy = $isLazy;

        return $this;
    }

    /**
     * Sets a prefix for all routes added to the collection
     */
    public function setPrefix(string $prefix): CollectionInterface
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Internal function to add a handler to the group.
     */
    protected function addMap(
        mixed $method,
        string $routePattern,
        mixed $handler,
        string | null $name = null
    ): void {
        /**
         * @var array<array-key, string>|string $method
         * @var callable|string                 $handler
         */
        $this->handlers[] = [$method, $routePattern, $handler, $name];
    }
}
