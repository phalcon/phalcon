<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by sinbadxiii/cphalcon-auth
 * @link    https://github.com/sinbadxiii/cphalcon-auth
 */

declare(strict_types=1);

namespace Phalcon\Support;

use Phalcon\Container\Service\Collection;
use Phalcon\Di\DiInterface;
use Throwable;

use function is_subclass_of;

/**
 * Abstract base class for service locators.
 *
 * Provides a unified way to register, validate, and resolve services
 * from a DI container, with support for both legacy Di and new Container.
 *
 * @template T of object
 */
abstract class AbstractLocator
{
    /**
     * @var array<string, class-string<T>>
     */
    protected array $services = [];

    /**
     * @param array<string, class-string<T>> $services
     */
    public function __construct(
        protected readonly Collection|DiInterface $container,
        array $services = []
    ) {
        $this->services = $this->getServices();

        foreach ($services as $name => $definition) {
            $this->register($name, $definition);
        }
    }

    /**
     * Retrieve a service instance from the container.
     *
     * @param array<int|string, mixed> $arguments
     *
     * @return T
     */
    public function newInstance(string $name, array $arguments = []): object {

        $definition = $this->getService($name);

        if (true !== $this->container->has($definition)) {
            $exceptionClass = $this->getExceptionClass();
            throw new $exceptionClass(
                'Service ' . $name . ' was not found in the DI container'
            );
        }

        // Handle legacy Di (shared) vs new Container (get)
        if ($this->container instanceof DiInterface) {
            /** @var T */
            return $this->container->getShared($definition, $arguments);
        }

        /** @var T */
        return $this->container->get($definition);
    }

    /**
     * Register a service or override an existing one.
     *
     * @param class-string<T> $definition
     *
     * @throws Exception
     */
    public function register(string $name, string $definition): static {

        $interfaceClass = $this->getInterfaceClass();

        if (! is_subclass_of($definition, $interfaceClass)) {
            $exceptionClass = $this->getExceptionClass();
            throw new $exceptionClass(
                'Definition ' . $definition
                . ' must implement ' . $interfaceClass
            );
        }

        $this->services[$name] = $definition;

        return $this;
    }

    /**
     * Get the exception class to throw on errors.
     *
     * @return class-string<Throwable>
     */
    abstract protected function getExceptionClass(): string;

    /**
     * Get the interface/class that all registered services must implement.
     * This allows different locators to enforce different contracts.
     *
     * @return class-string
     */
    abstract protected function getInterfaceClass(): string;

    /**
     * Get the service class name for a given name.
     *
     * @throws Exception
     */
    protected function getService(string $name): string {

        if (! isset($this->services[$name])) {
            $exceptionClass = $this->getExceptionClass();
            throw new $exceptionClass(
                'Service ' . $name . ' is not registered'
            );
        }

        return $this->services[$name];
    }

    /**
     * Get the default services for this locator.
     *
     * @return array<string, class-string<T>>
     */
    abstract protected function getServices(): array;
}