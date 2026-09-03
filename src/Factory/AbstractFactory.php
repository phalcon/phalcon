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

namespace Phalcon\Factory;

use Phalcon\Contracts\Factory\FactoryTypes;

use function array_merge;

/**
 * @phpstan-import-type factory_instances from FactoryTypes
 * @phpstan-import-type factory_services from FactoryTypes
 */
abstract class AbstractFactory extends AbstractConfigFactory
{
    /**
     * @phpstan-var factory_services
     */
    protected array $mapper = [];

    /**
     * @phpstan-var factory_instances
     */
    protected array $services = [];

    /**
     * Checks if a service exists and throws an exception
     */
    protected function getService(string $name): mixed
    {
        if (!isset($this->mapper[$name])) {
            throw $this->getException(
                "Service " . $name . " is not registered"
            );
        }

        return $this->mapper[$name];
    }

    /**
     * Returns the adapters for the factory
     *
     * @phpstan-return factory_services
     */
    abstract protected function getServices(): array;

    /**
     * Initialize services/add new services
     *
     * @phpstan-param factory_services $services
     */
    protected function init(array $services = []): void
    {
        $adapters = array_merge($this->getServices(), $services);

        foreach ($adapters as $name => $service) {
            $this->mapper[$name] = $service;

            unset($this->services[$name]);
        }
    }
}
