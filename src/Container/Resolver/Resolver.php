<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been heavily influenced by CapsulePHP.
 * Additionally, there are implementations from ioc-interop, which is a
 * Composer dependency, and from service-interop and resolver-interop. The
 * latter two are copied and re-implemented here: service-interop is not yet
 * published on Packagist, and resolver-interop requires PHP 8.4 (this project
 * targets PHP 8.1). Once both packages become available and compatible, the
 * copies will be replaced with the actual Composer dependencies.
 *
 * @link    https://github.com/capsulephp/di
 * @license https://github.com/capsulephp/di/blob/3.x/LICENSE.md
 *
 * @link    https://github.com/ioc-interop/interface
 * @license https://github.com/ioc-interop/interface/blob/1.x/LICENSE.md
 *
 * @link    https://github.com/service-interop/interface
 * @license https://github.com/service-interop/interface/blob/1.x/LICENSE.md
 *
 * @link    https://github.com/resolver-interop/interface/tree/1.x
 * @license https://github.com/resolver-interop/interface/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\Container\Resolver;

use Closure;
use Phalcon\Container\Exceptions\CannotResolveParameter;
use Phalcon\Container\Resolver\Lazy\Lazy;
use Phalcon\Contracts\Container\Resolver\ResolverService;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;

use function array_key_exists;
use function call_user_func_array;
use function class_exists;
use function method_exists;

class Resolver implements ResolverService
{
    /**
     * Is this a resolvable class?
     *
     * @param string $className
     *
     * @return bool
     */
    public function isResolvableClass(string $className): bool
    {
        if (!class_exists($className)) {
            return false;
        }

        return (new ReflectionClass($className))->isInstantiable();
    }

    /**
     * Resolve a call
     *
     * @param object   $ioc
     * @param callable $callableObject
     * @param array    $arguments
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function resolveCall(
        object $ioc,
        callable $callableObject,
        array $arguments
    ): mixed {
        $closure    = $callableObject instanceof Closure
            ? $callableObject
            : Closure::fromCallable($callableObject);
        $reflection = new ReflectionFunction($closure);
        $params     = $reflection->getParameters();
        $resolved   = $this->resolveParameters($ioc, $params, $arguments);

        return call_user_func_array($callableObject, $resolved);
    }

    /**
     * Resolve a class
     *
     * @param object $ioc
     * @param string $className
     * @param array  $arguments
     *
     * @return object
     * @throws ReflectionException
     */
    public function resolveClass(
        object $ioc,
        string $className,
        array $arguments
    ): object {
        $reflection  = new ReflectionClass($className);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $reflection->newInstanceArgs([]);
        }

        $params   = $constructor->getParameters();
        $resolved = $this->resolveParameters($ioc, $params, $arguments);

        return $reflection->newInstanceArgs($resolved);
    }

    /**
     * Resolve a method
     *
     * @param object           $ioc
     * @param ReflectionMethod $method
     * @param object           $instance
     *
     * @return void
     * @throws ReflectionException
     */
    public function resolveMethod(
        object $ioc,
        ReflectionMethod $method,
        object $instance
    ): void {
        $params   = $method->getParameters();
        $resolved = $this->resolveParameters($ioc, $params, []);

        $method->invokeArgs($instance, $resolved);
    }

    /**
     * Resolve parameters
     *
     * @param object              $ioc
     * @param ReflectionParameter $parameter
     *
     * @return mixed
     * @throws CannotResolveParameter
     * @throws ReflectionException
     */
    public function resolveParameter(
        object $ioc,
        ReflectionParameter $parameter
    ): mixed {
        $type = $parameter->getType();

        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            $typeName = $type->getName();

            if (method_exists($ioc, 'has') && $ioc->has($typeName)) {
                return $ioc->get($typeName);
            }
        }

        if ($parameter->isOptional()) {
            return $parameter->getDefaultValue();
        }

        throw new CannotResolveParameter(
            $parameter->getName(),
            $parameter->getDeclaringClass()?->getName() ?? 'unknown'
        );
    }

    public function resolveParameters(
        object $ioc,
        array $parameters,
        array $arguments
    ): array {
        $resolved = [];

        foreach ($parameters as $position => $parameter) {
            $name = $parameter->getName();

            if (array_key_exists($position, $arguments)) {
                $resolved[$position] = $this->resolveArg($ioc, $arguments[$position]);
                continue;
            }

            if (array_key_exists($name, $arguments)) {
                $resolved[$position] = $this->resolveArg($ioc, $arguments[$name]);
                continue;
            }

            $resolved[$position] = $this->resolveParameter($ioc, $parameter);
        }

        return $resolved;
    }

    public function resolveType(
        object $ioc,
        ReflectionType $type
    ): mixed {
        if ($type instanceof ReflectionNamedType) {
            return $type->getName();
        }

        return null;
    }

    private function resolveArg(object $ioc, mixed $arg): mixed
    {
        if ($arg instanceof Lazy) {
            return $arg->resolve($ioc);
        }

        return $arg;
    }
}
