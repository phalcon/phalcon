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

    /**
     * Every Action class this router would try for the given method and path,
     * in the order it tries them. The first that exists wins at match time.
     * The list is not filtered by existence.
     *
     * @return list<class-string>
     */
    public function candidatesFor(string $method, string $path): array
    {
        return array_column($this->deriveCandidates($method, $path), 0);
    }

    public function match(RequestInterface $request): ?RouterMatchInterface
    {
        $path   = $request->getURI(true);
        $method = $request->getMethod();

        $located = $this->locate($method, $path);
        if (is_array($located)) {
            return new RouterMatch($located[0], $located[1], $this->middlewareFor($located[0]));
        }

        $verbs = ['Get', 'Post', 'Put', 'Patch', 'Delete'];
        foreach ($verbs as $other) {
            if (strcasecmp($other, $method) !== 0 && is_array($this->locate($other, $path))) {
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

    /**
     * The single derivation of the routing convention. Every candidate is
     * paired with the request attributes it would leave behind, in try order.
     *
     * @return list<array{0: string, 1: list<string>}>
     */
    protected function deriveCandidates(string $method, string $path): array
    {
        $candidates = [];
        $uri        = trim($path, '/');
        $verb       = ucfirst(strtolower($method));
        $segments   = $uri === '' ? [] : explode('/', $uri);

        if (empty($segments)) {
            $className    = $this->baseNamespace . '\\' . $verb;
            $candidates[] = [$className, []];

            return $candidates;
        }

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

                $candidates[] = [$className, array_slice($segments, $index)];
            }

            $resourceName = $head[$last];
            $className    = $this->baseNamespace
                . $this->toNamespace($head)
                . '\\' . $verb . $this->camelize($resourceName);

            $candidates[] = [$className, array_slice($segments, $index)];

            $index = $index - 1;
        }

        return $candidates;
    }

    protected function locate(string $method, string $path): ?array
    {
        $candidates = $this->deriveCandidates($method, $path);

        foreach ($candidates as $candidate) {
            if (class_exists($candidate[0])) {
                return $candidate;
            }
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
