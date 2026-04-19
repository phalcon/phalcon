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

namespace Phalcon\Container;

use Closure;
use IocInterop\Interface\IocContainer;
use Phalcon\Container\Definition\Processor\ClosureProcessor;
use Phalcon\Container\Definition\Processor\ObjectProcessor;
use Phalcon\Container\Definition\Processor\ParameterProcessor;
use Phalcon\Container\Definition\Processor\Processor;
use Phalcon\Container\Definition\Processor\StringProcessor;
use Phalcon\Container\Definition\ServiceDefinition;
use Phalcon\Container\Definition\ServiceLifetime;
use Phalcon\Container\Exception\Invalid;
use Phalcon\Container\Exception\NotFound;
use Phalcon\Container\Resolver\Lazy\Lazy;
use Phalcon\Container\Resolver\Resolver;
use Phalcon\Container\Service\Collection;
use Phalcon\Di\InjectionAwareInterface;

use function array_key_exists;
use function class_exists;
use function in_array;
use function is_object;

class Container implements Collection
{
    protected array $aliases           = [];
    protected bool $autowire           = true;
    protected array $instanceLifetimes = [];
    protected array $instances         = [];
    protected array $parameters        = [];
    protected array $processors        = [];
    protected Resolver $resolver;
    protected array $services          = [];
    protected array $tags              = [];

    public function __construct()
    {
        $this->resolver   = new Resolver();
        $this->processors = [
            new ObjectProcessor(),
            new ClosureProcessor(),
            new StringProcessor(),
        ];
    }

    public function bind(string $interface, string $concrete): ServiceDefinition
    {
        return $this->set($interface, $concrete);
    }

    public function callableGet(string $name): Closure
    {
        return function () use ($name) {
            return $this->get($name);
        };
    }

    public function callableNew(string $name): Closure
    {
        return function () use ($name) {
            return $this->new($name);
        };
    }

    public function extend(string $name, callable $callable): void
    {
        $name = $this->resolveAlias($name);

        if (array_key_exists($name, $this->instances)) {
            throw Invalid::cannotExtendResolved($name);
        }

        if (!array_key_exists($name, $this->services)) {
            throw NotFound::serviceNotFound($name);
        }

        $this->services[$name]->addExtender($callable);
    }

    public function get(string $name): mixed
    {
        $name = $this->resolveAlias($name);

        if (array_key_exists($name, $this->parameters)) {
            return $this->resolveParameter($name);
        }

        if (array_key_exists($name, $this->instances)) {
            return $this->instances[$name];
        }

        return $this->resolve($name, true);
    }

    public function getAlias(string $name): string
    {
        return $this->aliases[$name] ?? '';
    }

    public function getByTag(string $tag): array
    {
        $names  = $this->tags[$tag] ?? [];
        $result = [];

        foreach ($names as $serviceName) {
            $result[] = $this->get($serviceName);
        }

        return $result;
    }

    public function getDefinition(string $name): ServiceDefinition
    {
        if (!array_key_exists($name, $this->services)) {
            throw NotFound::serviceNotFound($name);
        }

        return $this->services[$name];
    }

    public function getInstance(string $name): object
    {
        if (!array_key_exists($name, $this->instances)) {
            throw NotFound::instanceNotFound($name);
        }

        return $this->instances[$name];
    }

    public function getParameter(string $name): mixed
    {
        if (!array_key_exists($name, $this->parameters)) {
            throw NotFound::parameterNotFound($name);
        }

        return $this->resolveParameter($name);
    }

    public function getResolver(): Resolver
    {
        return $this->resolver;
    }

    public function getService(string $serviceName): object
    {
        $result = $this->get($serviceName);

        if (!is_object($result)) {
            throw Invalid::serviceNotFound($serviceName);
        }

        return $result;
    }

    public function has(string $name): bool
    {
        $name = $this->resolveAlias($name);

        if (
            array_key_exists($name, $this->parameters)
            || array_key_exists($name, $this->instances)
            || array_key_exists($name, $this->services)
        ) {
            return true;
        }

        return $this->autowire && $this->resolver->isResolvableClass($name);
    }

    public function hasAlias(string $name): bool
    {
        return array_key_exists($name, $this->aliases);
    }

    public function hasDefinition(string $name): bool
    {
        return array_key_exists($name, $this->services);
    }

    public function hasInstance(string $name): bool
    {
        return array_key_exists($name, $this->instances);
    }

    public function hasParameter(string $name): bool
    {
        return array_key_exists($name, $this->parameters);
    }

    public function hasService(string $serviceName): bool
    {
        return $this->has($serviceName);
    }

    public function isAutowireEnabled(): bool
    {
        return $this->autowire;
    }

    public function new(string $name): mixed
    {
        $name = $this->resolveAlias($name);

        return $this->resolve($name, false);
    }

    public function newDefinition(string $name): ServiceDefinition
    {
        return new ServiceDefinition($name, 'string');
    }

    public function registerTag(string $tag, string $serviceName): void
    {
        if (!array_key_exists($tag, $this->tags)) {
            $this->tags[$tag] = [];
        }

        if (!in_array($serviceName, $this->tags[$tag], true)) {
            $this->tags[$tag][] = $serviceName;
        }
    }

    public function set(string $name, mixed $definition): ServiceDefinition
    {
        $processor = $this->findProcessor($definition);
        $def       = $processor->process($name, $definition, $this);
        $def->setContainer($this);

        $this->services[$name] = $def;

        return $def;
    }

    public function setAlias(string $name, string $alias): void
    {
        $this->detectCircularAlias($alias, $name);
        $this->aliases[$alias] = $name;
    }

    public function setAutowire(bool $enabled): void
    {
        $this->autowire = $enabled;
    }

    public function setDefinition(string $name, ServiceDefinition $definition): void
    {
        $this->services[$name] = $definition;
    }

    public function setInstance(string $name, object $instance, string $lifetime): void
    {
        $this->instances[$name]         = $instance;
        $this->instanceLifetimes[$name] = $lifetime;
    }

    public function setParameter(string $name, mixed $value): void
    {
        $this->parameters[$name] = $value;
    }

    public function unsetAlias(string $name): void
    {
        unset($this->aliases[$name]);
    }

    public function unsetDefinition(string $name): void
    {
        unset($this->services[$name]);
    }

    public function unsetInstance(string $name): void
    {
        unset($this->instances[$name], $this->instanceLifetimes[$name]);
    }

    public function unsetInstances(string $lifetime): void
    {
        foreach ($this->instanceLifetimes as $name => $lt) {
            if ($lt === $lifetime) {
                unset($this->instances[$name], $this->instanceLifetimes[$name]);
            }
        }
    }

    public function unsetParameter(string $name): void
    {
        unset($this->parameters[$name]);
    }

    private function detectCircularAlias(string $alias, string $target): void
    {
        $current = $target;
        $seen    = [];

        while (true) {
            if ($current === $alias) {
                throw Invalid::circularAlias($alias);
            }

            if (array_key_exists($current, $seen)) {
                break;
            }

            if (!array_key_exists($current, $this->aliases)) {
                break;
            }

            $seen[$current] = true;
            $current        = $this->aliases[$current];
        }
    }

    private function findProcessor(mixed $definition): Processor
    {
        foreach ($this->processors as $processor) {
            if ($processor->canProcess($definition)) {
                return $processor;
            }
        }

        throw Invalid::noProcessorFound();
    }

    private function resolve(string $name, bool $cache): mixed
    {
        if (!array_key_exists($name, $this->services)) {
            if ($this->autowire && class_exists($name)) {
                $this->set($name, $name);
            } else {
                throw NotFound::serviceNotFound($name);
            }
        }

        $definition = $this->services[$name];
        $definition->freeze($this);

        $instance = $definition->buildService($this);

        if ($instance instanceof InjectionAwareInterface) {
            $instance->setDI($this);
        }

        $lifetime = $definition->getLifetime();

        if ($cache && $lifetime !== ServiceLifetime::TRANSIENT) {
            $this->instances[$name]         = $instance;
            $this->instanceLifetimes[$name] = $lifetime;
        }

        return $instance;
    }

    private function resolveAlias(string $name): string
    {
        $seen    = [];
        $current = $name;

        while (array_key_exists($current, $this->aliases)) {
            if (array_key_exists($current, $seen)) {
                throw Invalid::circularAlias($name);
            }

            $seen[$current] = true;
            $current        = $this->aliases[$current];
        }

        return $current;
    }

    private function resolveParameter(string $name): mixed
    {
        $value = $this->parameters[$name];

        if ($value instanceof Lazy) {
            $resolved                = $value->resolve($this);
            $this->parameters[$name] = $resolved;

            return $resolved;
        }

        return $value;
    }
}
