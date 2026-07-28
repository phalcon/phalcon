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

use Phalcon\ADR\Exceptions\ActionDirectoryNotSet;
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
    protected string $actionDirectory = '';

    /**
     * @var string
     */
    protected string $baseNamespace = '';

    /**
     * @var array<string, string[]>
     */
    protected array $middlewareMap = [];

    /**
     * @var string
     */
    protected string $wordSeparator = '-';

    /**
     * Every Action class this router would try for the given method and path,
     * in the order it tries them. The first that exists wins at match time.
     * Namespace descent consults the filesystem, so the list depends on the
     * action directory.
     *
     * @return list<class-string>
     */
    public function candidatesFor(string $method, string $path): array
    {
        return array_column($this->deriveCandidates($method, $path), 0);
    }

    public function match(RequestInterface $request): ?RouterMatchInterface
    {
        if ($this->actionDirectory === '') {
            throw new ActionDirectoryNotSet();
        }

        $path   = $request->getURI(true);
        $method = $request->getMethod();

        $located = $this->locate($method, $path);
        if (is_array($located)) {
            return new RouterMatch($located[0], $located[1], $this->middlewareFor($located[0]));
        }

        foreach ($this->verbs() as $other) {
            if (strcasecmp($other, $method) !== 0 && is_array($this->locate($other, $path))) {
                throw new MethodNotAllowed();
            }
        }

        return null;
    }

    public function pathFor(string $className): ?string
    {
        $prefix = $this->baseNamespace . '\\';

        if (strncmp($className, $prefix, strlen($prefix)) !== 0) {
            return null;
        }

        $parts = explode('\\', substr($className, strlen($prefix)));
        $last  = array_pop($parts);

        if (empty($parts)) {
            return in_array($last, $this->verbs(), true) ? '/' : null;
        }

        $resource  = end($parts);
        $operation = null;

        foreach ($this->verbs() as $verb) {
            if (strncmp($last, $verb, strlen($verb)) !== 0) {
                continue;
            }

            $remainder = substr($last, strlen($verb));

            if (strncmp($remainder, $resource, strlen($resource)) !== 0) {
                continue;
            }

            $operation = substr($last, strlen($verb) + strlen($resource));

            break;
        }

        if ($operation === null) {
            return null;
        }

        $path = '';
        foreach ($parts as $part) {
            $path .= '/' . $this->decamelize($part);
        }

        if ($operation !== '') {
            $path .= '/' . $this->decamelize($operation);
        }

        return $path;
    }

    public function setActionDirectory(string $actionDirectory): RouterInterface
    {
        $this->actionDirectory = rtrim($actionDirectory, DIRECTORY_SEPARATOR);

        return $this;
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

    public function setWordSeparator(string $wordSeparator): RouterInterface
    {
        $this->wordSeparator = $wordSeparator;

        return $this;
    }

    protected function camelize(string $segment): string
    {
        return str_replace(
            $this->wordSeparator,
            '',
            ucwords($segment, $this->wordSeparator)
        );
    }

    protected function decamelize(string $part): string
    {
        return strtolower(
            preg_replace(
                '/([a-z0-9])([A-Z])/',
                '$1' . $this->wordSeparator . '$2',
                $part
            )
        );
    }

    /**
     * The single derivation of the routing convention. Path segments are
     * consumed as namespace segments while the matching directory exists; the
     * class at the stopping depth is probed, preceded by the fused operation
     * form when exactly one segment remains. Every candidate is paired with the
     * request attributes it would leave behind.
     *
     * @return list<array{0: string, 1: list<string>}>
     */
    protected function deriveCandidates(string $method, string $path): array
    {
        $uri      = trim($path, '/');
        $verb     = ucfirst(strtolower($method));
        $segments = $uri === '' ? [] : explode('/', $uri);

        if (empty($segments)) {
            return [[$this->baseNamespace . '\\' . $verb, []]];
        }

        $subNamespace = '';
        $depth        = 0;

        while (!empty($segments)) {
            $candidate = $subNamespace . '\\' . $this->camelize($segments[0]);

            if (!$this->hasSubNamespace($candidate)) {
                break;
            }

            $subNamespace = $candidate;
            $depth++;

            array_shift($segments);
        }

        if ($depth === 0) {
            return [];
        }

        $parts     = explode('\\', ltrim($subNamespace, '\\'));
        $resource  = end($parts);
        $className = $this->baseNamespace . $subNamespace . '\\' . $verb . $resource;

        $candidates = [];

        if (count($segments) === 1) {
            $candidates[] = [$className . $this->camelize($segments[0]), []];
        }

        $candidates[] = [$className, $segments];

        return $candidates;
    }

    protected function hasSubNamespace(string $subNamespace): bool
    {
        if (str_contains($subNamespace, '..')) {
            return false;
        }

        return is_dir(
            $this->actionDirectory
            . str_replace('\\', DIRECTORY_SEPARATOR, $subNamespace)
        );
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

    /**
     * The HTTP verbs the convention recognises, in class-name form.
     *
     * @return list<string>
     */
    protected function verbs(): array
    {
        return ['Get', 'Post', 'Put', 'Patch', 'Delete'];
    }
}
