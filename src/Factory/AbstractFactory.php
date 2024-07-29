<?php

/*
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Phalcon\Factory;

abstract class AbstractFactory extends AbstractConfigFactory
{
    protected array $mapper = [];

    protected array $services = [];

    /**
     * Returns the adapters for the factory
     *
     * @return string[]
     */
    abstract protected function getServices(): array;

    /**
     * Checks if a service exists and throws an exception
     *
     * @param string $name
     *
     * @throws Exception
     * @return mixed
     */
    protected function getService(string $name): mixed
    {
        if (!isset($this->mapper[$name])) {
            throw $this->getException("Service $name is not registered");
        }

        return $this->mapper[$name];
    }

    /**
     * Initialize services/add new services
     *
     * @param array $services
     */
    protected function init(array $services = []): void
    {
        $adapters = $this->getServices();
        $adapters = array_merge($adapters, $services);

        foreach ($adapters as $name => $service) {
            $this->mapper[$name] = $service;
            unset($this->services[$name]);
        }
    }
}
