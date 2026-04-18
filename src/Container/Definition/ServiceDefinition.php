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

namespace Phalcon\Container\Definition;

use Phalcon\Container\Exception\Invalid;
use ReflectionClass;

use function in_array;
use function method_exists;

class ServiceDefinition
{
    protected array $arguments       = [];
    protected object|null $container    = null;
    protected string|null $class     = null;
    protected array $constructorArgs = [];
    protected array $extenders       = [];
    protected mixed $factory         = null;
    protected bool $frozen           = false;
    protected bool $isCacheable      = false;
    protected string $lifetime       = ServiceLifetime::SCOPED;
    protected mixed $raw             = null;
    protected string $serviceName;
    protected array $tags            = [];
    protected string $type;

    public function __construct(string $serviceName, string $type, mixed $raw = null)
    {
        $this->raw         = $raw;
        $this->serviceName = $serviceName;
        $this->type        = $type;
    }

    public function addExtender(callable $extender): static
    {
        $this->checkFrozen();
        $this->extenders[] = $extender;

        return $this;
    }

    public function addTag(string $tag): static
    {
        $this->checkFrozen();

        if (!in_array($tag, $this->tags, true)) {
            $this->tags[] = $tag;
        }

        if ($this->container !== null && method_exists($this->container, 'registerTag')) {
            $this->container->registerTag($tag, $this->serviceName);
        }

        return $this;
    }

    public function buildService(object $container): object
    {
        if ($this->hasFactory()) {
            $instance = ($this->factory)($container);
        } else {
            $class      = $this->class ?? $this->serviceName;
            $args       = $this->resolveArgs($container, $this->constructorArgs);
            $reflection = new ReflectionClass($class);
            $instance   = $reflection->newInstanceArgs($args);
        }

        foreach ($this->extenders as $extender) {
            $instance = $extender($instance, $container);
        }

        return $instance;
    }

    public function freeze(object $container): void
    {
        if ($this->frozen) {
            return;
        }

        if (
            $this->type === DefinitionType::STRING &&
            method_exists($container, 'isAutowireEnabled') &&
            $container->isAutowireEnabled()
        ) {
            $class       = $this->class ?? $this->serviceName;
            $reflection  = new ReflectionClass($class);
            $constructor = $reflection->getConstructor();
            $params      = $constructor !== null ? $constructor->getParameters() : [];

            if (method_exists($container, 'getResolver')) {
                $this->constructorArgs = $container->getResolver()->resolveParameters(
                    $container,
                    $params,
                    $this->arguments
                );
            }
        } elseif ($this->type === DefinitionType::STRING && !empty($this->arguments)) {
            $this->constructorArgs = $this->arguments;
        }

        $this->frozen = true;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getClass(): string
    {
        if ($this->class === null) {
            throw Invalid::noClassSet($this->serviceName);
        }

        return $this->class;
    }

    public function getConstructorArgs(): array
    {
        return $this->constructorArgs;
    }

    public function getExtenders(): array
    {
        return $this->extenders;
    }

    public function getFactory(): callable
    {
        if ($this->factory === null) {
            throw Invalid::noFactorySet($this->serviceName);
        }

        return $this->factory;
    }

    public function getLifetime(): string
    {
        return $this->lifetime;
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function hasClass(): bool
    {
        return $this->class !== null;
    }

    public function hasExtenders(): bool
    {
        return !empty($this->extenders);
    }

    public function hasFactory(): bool
    {
        return $this->factory !== null;
    }

    public function isCacheable(): bool
    {
        return $this->isCacheable && $this->frozen;
    }

    public function isFrozen(): bool
    {
        return $this->frozen;
    }

    public function setArgument(int|string $param, mixed $value): static
    {
        $this->checkFrozen();
        $this->arguments[$param] = $value;

        return $this;
    }

    public function setContainer(object $container): static
    {
        $this->container = $container;

        return $this;
    }

    public function setClass(string $class): static
    {
        $this->checkFrozen();
        $this->class = $class;

        return $this;
    }

    public function setExtenders(array $extenders): static
    {
        $this->checkFrozen();
        $this->extenders = $extenders;

        return $this;
    }

    public function setFactory(callable $factory): static
    {
        $this->checkFrozen();
        $this->factory = $factory;

        return $this;
    }

    public function setIsCacheable(bool $isCacheable): static
    {
        $this->checkFrozen();
        $this->isCacheable = $isCacheable;

        return $this;
    }

    public function setLifetime(string $lifetime): static
    {
        $this->checkFrozen();
        $this->lifetime = $lifetime;

        return $this;
    }

    public function unsetClass(): static
    {
        $this->checkFrozen();
        $this->class = null;

        return $this;
    }

    public function unsetExtenders(): static
    {
        $this->checkFrozen();
        $this->extenders = [];

        return $this;
    }

    public function unsetFactory(): static
    {
        $this->checkFrozen();
        $this->factory = null;

        return $this;
    }

    protected function checkFrozen(): void
    {
        if ($this->frozen) {
            throw Invalid::frozenDefinition($this->serviceName);
        }
    }

    private function resolveArgs(object $container, array $args): array
    {
        $resolved = [];

        foreach ($args as $key => $arg) {
            if (is_object($arg) && method_exists($arg, 'resolve')) {
                $resolved[$key] = $arg->resolve($container);
            } else {
                $resolved[$key] = $arg;
            }
        }

        return $resolved;
    }
}
