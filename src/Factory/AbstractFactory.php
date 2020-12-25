<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Factory;

use Phalcon\Config;

abstract class AbstractFactory
{
    /**
     * @var array
     */
    protected $mapper = [];

    /**
     * @var array
     */
    protected $services = [];

    /**
     * Checks the config if it is a valid object
     */
    protected function checkConfig($config) : array
    {
        if( is_object($config) && $config instanceof Config) {
            $config = $config->toArray();
        }
        if (!is_array($config)) {
            throw new Exception(
                "Config must be array or Phalcon\\Config object"
            );
        }
        
        if ( !isset ($config["adapter"])) {
            throw new Exception(
                "You must provide 'adapter' option in factory config parameter."
            );
        }

        return $config;
    }

    /**
     * Returns the adapters for the factory
     */
    abstract protected function getAdapters() : array;

    /**
     * Checks if a service exists and throws an exception
     * TODO: return type mixed?
     */
    protected function getService(string $name)
    {
        $service = $this->mapper[$name] ?? null;
        if (is_null($service)) {
            throw new Exception("Service " . $name . " is not registered");
        }
        return $service;
    }

    /**
     * AdapterFactory constructor.
     */
    protected function init(array $services = []) : void
    {
        $adapters = array_merge($this->getAdapters(), $services);
        foreach($adapters as $name => $service) {
            $this->mapper[$name] = $service;
            unset($this->services[$name]);
        }
    }
}
