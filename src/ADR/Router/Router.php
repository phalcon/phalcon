<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Based on the Action Domain Responder pattern
 * @link    https://pmjones.io/adr/
 */

declare(strict_types=1);

namespace Phalcon\ADR\Router;

use Phalcon\ADR\Exceptions\MethodNotAllowed;
use Phalcon\Contracts\ADR\Router\Router as RouterInterface;
use Phalcon\Contracts\ADR\Router\RouterMatch as RouterMatchInterface;
use Phalcon\Http\RequestInterface;

/**
 * Convention router. `method + static path -> Action class`; the path tail
 * becomes positional request attributes. Middleware is resolved from a
 * namespace-prefix map (group semantics); global middleware stays on the
 * pipeline. No route table.
 */
final class Router implements RouterInterface
{
    /**
     * @var string
     */
    protected string $baseNamespace = '';

    /**
     * @var array<string, string[]>
     */
    protected array $middlewareMap = [];

    public function match(RequestInterface $request): ?RouterMatchInterface
    {
        $uri      = trim($request->getURI(true), '/');
        $verb     = ucfirst(strtolower($request->getMethod()));
        $segments = $uri === '' ? [] : explode('/', $uri);

        if (empty($segments)) {
            $className = $this->baseNamespace . '\\' . $verb;

            if (class_exists($className)) {
                return new RouterMatch($className, [], $this->middlewareFor($className));
            }

            return null;
        }

        $located = $this->locate($segments, $verb);
        if (is_array($located)) {
            return new RouterMatch($located[0], $located[1], $this->middlewareFor($located[0]));
        }

        $verbs = ['Get', 'Post', 'Put', 'Patch', 'Delete'];
        foreach ($verbs as $other) {
            if ($other !== $verb && is_array($this->locate($segments, $other))) {
                throw new MethodNotAllowed();
            }
        }

        return null;
    }

    public function setBaseNamespace(string $baseNamespace): RouterInterface
    {
        $this->baseNamespace = rtrim($baseNamespace, '\\');

        return $this;
    }

    public function setMiddlewareMap(array $middlewareMap): RouterInterface
    {
        $this->middlewareMap = $middlewareMap;

        return $this;
    }

    protected function camelize(string $segment): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $segment)));
    }

    protected function locate(array $segments, string $verb): ?array
    {
        $index = count($segments);

        while ($index >= 1) {
            $last = $index - 1;
            $head = array_slice($segments, 0, $index);

            if ($index >= 2) {
                $prev         = $index - 2;
                $resourceName = $head[$prev];
                $operation    = $head[$last];
                $className    = $this->baseNamespace
                    . $this->toNamespace(array_slice($head, 0, $last))
                    . '\\' . $verb . $this->camelize($resourceName) . $this->camelize($operation);

                if (class_exists($className)) {
                    return [$className, array_slice($segments, $index)];
                }
            }

            $resourceName = $head[$last];
            $className    = $this->baseNamespace
                . $this->toNamespace($head)
                . '\\' . $verb . $this->camelize($resourceName);

            if (class_exists($className)) {
                return [$className, array_slice($segments, $index)];
            }

            $index = $index - 1;
        }

        return null;
    }

    protected function middlewareFor(string $className): array
    {
        $stacked = [];
        foreach ($this->middlewareMap as $prefix => $list) {
            $full = $this->baseNamespace . $prefix;

            if (strncmp($className, $full, strlen($full)) === 0) {
                $stacked = array_merge($stacked, $list);
            }
        }

        return $stacked;
    }

    protected function toNamespace(array $segments): string
    {
        $parts = [];
        foreach ($segments as $segment) {
            $parts[] = $this->camelize($segment);
        }

        if (empty($parts)) {
            return '';
        }

        return '\\' . implode('\\', $parts);
    }
}
