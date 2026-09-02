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

use Exception as BaseException;
use Phalcon\Contracts\Factory\FactoryTypes;

use function array_merge;

/**
 * @phpstan-import-type factory_instances from FactoryTypes
 * @phpstan-import-type factory_services from FactoryTypes
 */
abstract class AbstractFactory extends AbstractConfigFactory
{
    /**
     * @var array<string, string>
     *
     * @phpstan-var factory_services
     */
    protected array $mapper = [];

    /**
     * @var array<string, mixed>
     *
     * @phpstan-var factory_instances
     */
    protected array $services = [];

    /**
     * Checks if a service exists and throws an exception
     *
     * @param string $name
     *
     * @return string
     * @throws BaseException
     */
    protected function getService(string $name): string
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
     * @return string[]
     * @phpstan-return factory_services
     */
    abstract protected function getServices(): array;

    /**
     * Initialize services/add new services
     *
     * @param string[] $services
     *
     * @phpstan-param factory_services $services
     *
     * @return void
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
