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
 */
class Collection implements CollectionInterface
{
    /**
     * @var callable
     */
    protected mixed $handler;

    /**
     * @var array
     */
    protected array $handlers = [];

    /**
     * @var bool
     */
    protected bool $isLazy = false;

    /**
     * @var string
     */
    protected string $prefix = '';

    /**
     * Maps a route to a handler that only matches if the HTTP method is DELETE.
     *
     * @param string          $routePattern
     * @param callable|string $handler
     * @param string|null     $name
     *
     * @return CollectionInterface
     */
    public function delete(
        string $routePattern,
        callable | string $handler,
        string | null $name = null
    ): CollectionInterface {
        $this->addMap('DELETE', $routePattern, $handler, $name);

        return $this;
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is GET.
     *
     * @param string          $routePattern
     * @param callable|string $handler
     * @param string|null     $name
     *
     * @return CollectionInterface
     */
    public function get(
        string $routePattern,
        callable | string $handler,
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
    public function getHandler(): mixed
    {
        return $this->handler;
    }

    /**
     * Returns the registered handlers
     *
     * @return array
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }

    /**
     * Returns the collection prefix if any
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is HEAD.
     *
     * @param string          $routePattern
     * @param callable|string $handler
     * @param string|null     $name
     *
     * @return CollectionInterface
     */
    public function head(
        string $routePattern,
        callable | string $handler,
        string | null $name = null
    ): CollectionInterface {
        $this->addMap('HEAD', $routePattern, $handler, $name);

        return $this;
    }

    /**
     * Returns if the main handler must be lazy loaded
     *
     * @return bool
     */
    public function isLazy(): bool
    {
        return $this->isLazy;
    }

    /**
     * Maps a route to a handler.
     *
     * @param string          $routePattern
     * @param callable|string $handler
     * @param string|null     $name
     *
     * @return CollectionInterface
     */
    public function map(
        string $routePattern,
        callable | string $handler,
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
     *
     * @param string          $routePattern
     * @param callable|string $handler
     * @param array|string    $method
     * @param string|null     $name
     *
     * @return CollectionInterface
     */
    public function mapVia(
        string $routePattern,
        callable | string $handler,
        array | string $method,
        string | null $name = null
    ): CollectionInterface {
        $this->addMap($method, $routePattern, $handler, $name);

        return $this;
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is
     * OPTIONS.
     *
     * @param string          $routePattern
     * @param callable|string $handler
     * @param string|null     $name
     *
     * @return CollectionInterface
     */
    public function options(
        string $routePattern,
        callable | string $handler,
        string | null $name = null
    ): CollectionInterface {
        $this->addMap('OPTIONS', $routePattern, $handler, $name);

        return $this;
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is PATCH.
     *
     * @param string          $routePattern
     * @param callable|string $handler
     * @param string|null     $name
     *
     * @return CollectionInterface
     */
    public function patch(
        string $routePattern,
        callable | string $handler,
        string | null $name = null
    ): CollectionInterface {
        $this->addMap('PATCH', $routePattern, $handler, $name);

        return $this;
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is POST.
     *
     * @param string          $routePattern
     * @param callable|string $handler
     * @param string|null     $name
     *
     * @return CollectionInterface
     */
    public function post(
        string $routePattern,
        callable | string $handler,
        string | null $name = null
    ): CollectionInterface {
        $this->addMap('POST', $routePattern, $handler, $name);

        return $this;
    }

    /**
     * Maps a route to a handler that only matches if the HTTP method is PUT.
     *
     * @param string          $routePattern
     * @param callable|string $handler
     * @param string|null     $name
     *
     * @return CollectionInterface
     */
    public function put(
        string $routePattern,
        callable | string $handler,
        string | null $name = null
    ): CollectionInterface {
        $this->addMap('PUT', $routePattern, $handler, $name);

        return $this;
    }

    /**
     * Sets the main handler.
     *
     * @param mixed $handler
     * @param bool  $isLazy
     *
     * @return CollectionInterface
     */
    public function setHandler(mixed $handler, bool $isLazy = false): CollectionInterface
    {
        $this->handler = $handler;
        $this->isLazy  = $isLazy;

        return $this;
    }

    /**
     * Sets if the main handler must be lazy loaded
     *
     * @param bool $isLazy
     *
     * @return CollectionInterface
     */
    public function setLazy(bool $isLazy): CollectionInterface
    {
        $this->isLazy = $isLazy;

        return $this;
    }

    /**
     * Sets a prefix for all routes added to the collection
     *
     * @param string $prefix
     *
     * @return CollectionInterface
     */
    public function setPrefix(string $prefix): CollectionInterface
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Internal function to add a handler to the group.
     *
     * @param array|string    $method
     * @param string          $routePattern
     * @param callable|string $handler
     * @param string|null     $name
     *
     * @return void
     */
    protected function addMap(
        array | string $method,
        string $routePattern,
        callable | string $handler,
        string | null $name = null
    ): void {
        $this->handlers[] = [$method, $routePattern, $handler, $name];
    }
}
