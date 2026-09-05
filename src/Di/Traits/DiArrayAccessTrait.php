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

namespace Phalcon\Di\Traits;

use Phalcon\Di\Exception;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Di\ServiceInterface;
use ReturnTypeWillChange;

trait DiArrayAccessTrait
{
    /**
     * Resolves a service, the resolved service is stored in the DI, subsequent
     * requests for this service will return the same instance
     *
     * @return InjectionAwareInterface|mixed|null
     * @throws Exception
     */
    abstract public function getShared(string $name, array | null $parameters = null);

    /**
     * Check whether the DI contains a service by a name
     */
    abstract public function has(string $name): bool;

    /**
     * Check if a service is registered using the array syntax
     *
     * @param mixed $name
     */
    #[ReturnTypeWillChange]
    public function offsetExists($name): bool
    {
        return $this->has($name);
    }

    /**
     * Allows to obtain a shared service using the array syntax
     *
     *```php
     * var_dump($di["request"]);
     *```
     *
     * @param mixed $name
     *
     * @return InjectionAwareInterface|mixed|null
     * @throws Exception
     */
    #[ReturnTypeWillChange]
    public function offsetGet($name)
    {
        return $this->getShared($name);
    }

    /**
     * Allows to register a shared service using the array syntax
     *
     *```php
     * $di["request"] = new \Phalcon\Http\Request();
     *```
     *
     * @param mixed $name
     * @param mixed $definition
     */
    #[ReturnTypeWillChange]
    public function offsetSet($name, $definition): void
    {
        $this->setShared($name, $definition);
    }

    /**
     * Removes a service from the services container using the array syntax
     *
     * @param mixed $name
     */
    #[ReturnTypeWillChange]
    public function offsetUnset($name): void
    {
        $this->remove($name);
    }

    /**
     * Removes a service in the services container
     * It also removes any shared instance created for the service
     */
    abstract public function remove(string $name): void;

    /**
     * Registers a service in the services container
     */
    abstract public function set(
        string $name,
        mixed $definition,
        bool $shared = false
    ): ServiceInterface;

    /**
     * Registers an "always shared" service in the services container
     */
    public function setShared(string $name, mixed $definition): ServiceInterface
    {
        return $this->set($name, $definition, true);
    }
}
