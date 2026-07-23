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

use Phalcon\Container\Exceptions\FrozenDefinition;
use Phalcon\Container\Exceptions\InvalidExtender;
use Phalcon\Container\Exceptions\NoClassSet;
use Phalcon\Container\Exceptions\NoFactorySet;
use Phalcon\Contracts\Container\Resolver\Resolvable;
use ReflectionClass;
use ReflectionException;

use function in_array;
use function method_exists;

class ServiceDefinition
{
    /**
     * @var array<array-key, mixed>
     */
    protected array $arguments = [];
    protected string|null $className = null;
    /**
     * @var array
     */
    protected array $constructorArgs = [];

    protected object|null $container = null;
    /**
     * @var array<array-key, callable>
     */
    protected array $extenders  = [];
    protected mixed $factory    = null;
    protected bool $frozen      = false;
    protected bool $isCacheable = false;
    protected string $lifetime  = ServiceLifetime::SCOPED;
    /**
     * @var array<array-key, string>
     */
    protected array $tags = [];

    public function __construct(
        protected string $serviceName,
        protected string $type,
        protected mixed $raw = null
    ) {
    }

    /**
     * Adds an extender
     *
     * @param callable $extender
     *
     * @return $this
     * @throws FrozenDefinition
     */
    public function addExtender(callable $extender): static
    {
        $this->checkFrozen();
        $this->extenders[] = $extender;

        return $this;
    }

    /**
     * Adds a tag
     *
     * @param string $tag
     *
     * @return $this
     * @throws FrozenDefinition
     */
    public function addTag(string $tag): static
    {
        $this->checkFrozen();

        if (!in_array($tag, $this->tags, true)) {
            $this->tags[] = $tag;
        }

        if (
            $this->container !== null
            && method_exists($this->container, 'setTag')
        ) {
            $this->container->setTag($tag, $this->serviceName);
        }

        return $this;
    }

    /**
     * Builds a service and returns the instance back
     *
     * @param object $container
     *
     * @return object
     * @throws ReflectionException
     */
    public function buildService(object $container): object
    {
        if ($this->hasFactory()) {
            $instance = ($this->factory)($container);
        } else {
            $className  = $this->className ?? $this->serviceName;
            $args       = $this->resolveArgs($container, $this->constructorArgs);
            $reflection = new ReflectionClass($className);
            $instance   = $reflection->newInstanceArgs($args);
        }

        foreach ($this->extenders as $extender) {
            $instance = $extender($instance, $container);
        }

        return $instance;
    }

    /**
     * Freezes the container
     *
     * @param object $container
     *
     * @return void
     * @throws ReflectionException
     */
    public function freeze(object $container): void
    {
        if ($this->frozen) {
            return;
        }

        if (
            $this->type === DefinitionType::STRING_TYPE &&
            method_exists($container, 'isAutowireEnabled') &&
            $container->isAutowireEnabled()
        ) {
            $className   = $this->className ?? $this->serviceName;
            $reflection  = new ReflectionClass($className);
            $constructor = $reflection->getConstructor();
            $params      = $constructor !== null ? $constructor->getParameters() : [];

            if (method_exists($container, 'getResolver')) {
                $this->constructorArgs = $container->getResolver()->resolveParameters(
                    $container,
                    $params,
                    $this->arguments
                );
            }
        } elseif ($this->type === DefinitionType::STRING_TYPE && !empty($this->arguments)) {
            $this->constructorArgs = $this->arguments;
        }

        $this->frozen = true;
    }

    /**
     * Returns the arguments
     *
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Returns the class
     *
     * @return string
     * @throws NoClassSet
     */
    public function getClass(): string
    {
        if ($this->className === null) {
            throw new NoClassSet($this->serviceName);
        }

        return $this->className;
    }

    /**
     * Returns the constructor arguments
     *
     * @return array
     */
    public function getConstructorArgs(): array
    {
        return $this->constructorArgs;
    }

    /**
     * Returns the extenders
     *
     * @return array<array-key, callable>
     */
    public function getExtenders(): array
    {
        return $this->extenders;
    }

    /**
     * Returns the factory
     *
     * @return callable
     * @throws NoFactorySet
     */
    public function getFactory(): callable
    {
        if ($this->factory === null) {
            throw new NoFactorySet($this->serviceName);
        }

        return $this->factory;
    }

    /**
     * Returns the lifetime
     *
     * @return string
     */
    public function getLifetime(): string
    {
        return $this->lifetime;
    }

    /**
     * Returns the name of the service
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    /**
     * Returns the tags
     *
     * @return array<array-key, string>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Returns the type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Does it have a class
     *
     * @return bool
     */
    public function hasClass(): bool
    {
        return $this->className !== null;
    }

    /**
     * Do we have extenders
     *
     * @return bool
     */
    public function hasExtenders(): bool
    {
        return !empty($this->extenders);
    }

    /**
     * Does it have a factory
     *
     * @return bool
     */
    public function hasFactory(): bool
    {
        return $this->factory !== null;
    }

    /**
     * Is it cacheable
     *
     * @return bool
     */
    public function isCacheable(): bool
    {
        return $this->isCacheable && $this->frozen;
    }

    /**
     * Is it frozen
     *
     * @return bool
     */
    public function isFrozen(): bool
    {
        return $this->frozen;
    }

    /**
     * Set an argument
     *
     * @param int|string $param
     * @param mixed      $value
     *
     * @return $this
     * @throws FrozenDefinition
     */
    public function setArgument(int|string $param, mixed $value): static
    {
        $this->checkFrozen();
        $this->arguments[$param] = $value;

        return $this;
    }

    /**
     * Set a class
     *
     * @param string $className
     *
     * @return $this
     * @throws FrozenDefinition
     */
    public function setClass(string $className): static
    {
        $this->checkFrozen();
        $this->className = $className;

        return $this;
    }

    /**
     * Set the container
     *
     * @param object $container
     *
     * @return $this
     */
    public function setContainer(object $container): static
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Set extenders
     *
     * @param array<array-key, callable> $extenders
     *
     * @return $this
     * @throws FrozenDefinition
     * @throws InvalidExtender
     */
    public function setExtenders(array $extenders): static
    {
        $this->checkFrozen();

        foreach ($extenders as $key => $extender) {
            if (!is_callable($extender)) {
                throw new InvalidExtender($this->serviceName, (string) $key);
            }
        }

        $this->extenders = $extenders;

        return $this;
    }

    /**
     * Set a factory
     *
     * @param callable $factory
     *
     * @return $this
     * @throws FrozenDefinition
     */
    public function setFactory(callable $factory): static
    {
        $this->checkFrozen();
        $this->factory = $factory;

        return $this;
    }

    /**
     * Set cachable
     * @param bool $isCacheable
     *
     * @return $this
     * @throws FrozenDefinition
     */
    public function setIsCacheable(bool $isCacheable): static
    {
        $this->checkFrozen();
        $this->isCacheable = $isCacheable;

        return $this;
    }

    /**
     * Set lifetime
     *
     * @param string $lifetime
     *
     * @return $this
     * @throws FrozenDefinition
     */
    public function setLifetime(string $lifetime): static
    {
        $this->checkFrozen();
        $this->lifetime = $lifetime;

        return $this;
    }

    /**
     * Unset class
     *
     * @return $this
     * @throws FrozenDefinition
     */
    public function unsetClass(): static
    {
        $this->checkFrozen();
        $this->className = null;

        return $this;
    }

    /**
     * Unset extenders
     *
     * @return $this
     * @throws FrozenDefinition
     */
    public function unsetExtenders(): static
    {
        $this->checkFrozen();
        $this->extenders = [];

        return $this;
    }

    /**
     * Unset the factory
     *
     * @return $this
     * @throws FrozenDefinition
     */
    public function unsetFactory(): static
    {
        $this->checkFrozen();
        $this->factory = null;

        return $this;
    }

    /**
     * Check if frozen
     *
     * @return void
     * @throws FrozenDefinition
     */
    protected function checkFrozen(): void
    {
        if ($this->frozen) {
            throw new FrozenDefinition($this->serviceName);
        }
    }

    /**
     * Resolve arguments
     *
     * @param object $container
     * @param array  $args
     *
     * @return array
     */
    private function resolveArgs(object $container, array $args): array
    {
        $resolved = [];

        foreach ($args as $key => $arg) {
            /**
             * Only a genuine lazy value (Resolvable) is resolved here. A plain
             * object that merely exposes a resolve() method - the container
             * itself, whose resolve() is private - must be passed through
             * untouched, otherwise that private resolve() would be called from
             * this scope.
             */
            if ($arg instanceof Resolvable) {
                $resolved[$key] = $arg->resolve($container);
            } else {
                $resolved[$key] = $arg;
            }
        }

        return $resolved;
    }
}
