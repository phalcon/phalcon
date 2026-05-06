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

namespace Phalcon\Auth\Access;

use Phalcon\Auth\Exception;
use Phalcon\Container\ContainerInterface;
use Phalcon\Contracts\Auth\Access\Access;
use Phalcon\Di\DiInterface;

use function is_subclass_of;

/**
 * Service locator for Phalcon\Auth access gates. Utilizes the container to
 * obtain the service. For the Phalcon\Container\Container one can use
 * autowiring. For the Phalcon\Di\Di, one needs to register the gates in it
 * to be used here.
 */
class AccessLocator
{
    /**
     * @var array<string, class-string<Access>>
     */
    protected array $services = [];

    /**
     * @param array<string, class-string<Access>> $services
     */
    public function __construct(
        protected readonly ContainerInterface|DiInterface $container,
        array $services = []
    ) {
        $this->services = $this->getServices();

        foreach ($services as $name => $definition) {
            $this->register($name, $definition);
        }
    }

    /**
     * Return a gate instance. Resolves from the DI container. For this
     * locator, all services are shared.
     */
    public function newInstance(string $name): Access
    {
        $definition = $this->getService($name);

        if (true !== $this->container->has($definition)) {
            $exceptionClass = $this->getExceptionClass();
            throw new $exceptionClass(
                'Service ' . $name . ' was not found in the DI container'
            );
        }

        if ($this->container instanceof DiInterface) {
            return $this->container->getShared($definition);
        }

        return $this->container->get($definition);
    }

    /**
     * Registers (or overrides) a gate class under the given name. The
     * definition must implement Access.
     *
     * @param class-string<Access> $definition
     *
     * @throws Exception
     */
    public function register(string $name, string $definition): static
    {
        if (!is_subclass_of($definition, Access::class)) {
            $exceptionClass = $this->getExceptionClass();
            throw new $exceptionClass(
                'Definition ' . $definition
                . ' must implement ' . Access::class
            );
        }

        $this->services[$name] = $definition;

        return $this;
    }

    protected function getExceptionClass(): string
    {
        return Exception::class;
    }

    /**
     * @throws Exception
     */
    protected function getService(string $name): string
    {
        if (!isset($this->services[$name])) {
            $exceptionClass = $this->getExceptionClass();
            throw new $exceptionClass(
                'Service ' . $name . ' is not registered'
            );
        }

        return $this->services[$name];
    }

    /**
     * @return array<string, class-string<Access>>
     */
    protected function getServices(): array
    {
        return [
            'auth'  => Auth::class,
            'guest' => Guest::class,
        ];
    }
}