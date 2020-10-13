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

trait DiArrayAccessTrait
{
    /**
     * Resolves a service, the resolved service is stored in the DI, subsequent
     * requests for this service will return the same instance
     *
     * @param string     $name
     * @param array|null $parameters
     *
     * @return mixed|InjectionAwareInterface|null
     * @throws Exception
     */
    abstract public function getShared(string $name, array $parameters = null);

    /**
     * Check whether the DI contains a service by a name
     *
     * @param string $name
     *
     * @return bool
     */
    abstract public function has(string $name): bool;

    /**
     * Allows to obtain a shared service using the array syntax
     *
     *```php
     * var_dump($di["request"]);
     *```
     *
     * @param mixed $name
     *
     * @return mixed|InjectionAwareInterface|null
     * @throws Exception
     */
    public function offsetGet($name)
    {
        return $this->getShared($name);
    }

    /**
     * Check if a service is registered using the array syntax
     *
     * @param mixed $name
     *
     * @return bool
     */
    public function offsetExists($name): bool
    {
        return $this->has($name);
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
    public function offsetSet($name, $definition): void
    {
        $this->setShared($name, $definition);
    }

    /**
     * Removes a service from the services container using the array syntax
     *
     * @param mixed $name
     */
    public function offsetUnset($name): void
    {
        $this->remove($name);
    }

    /**
     * Removes a service in the services container
     * It also removes any shared instance created for the service
     *
     * @param string $name
     */
    abstract public function remove(string $name): void;

    /**
     * Registers an "always shared" service in the services container
     *
     * @param string $name
     * @param mixed  $definition
     *
     * @return ServiceInterface
     */
    abstract public function setShared(string $name, $definition): ServiceInterface;
}
