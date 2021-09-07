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

namespace Phalcon\Logger;

use DateTimeZone;
use Phalcon\Config\ConfigInterface;

/**
 * Class LoggerFactory
 *
 * @package Phalcon\Logger
 */
class LoggerFactory
{
    /**
     * @var AdapterFactory
     */
    private AdapterFactory $adapterFactory;

    public function __construct(AdapterFactory $factory)
    {
        $this->adapterFactory = $factory;
    }

    /**
     * Factory to create an instance from a Config object
     *
     * @param array|ConfigInterface $config = [
     *                                      'name'     => 'messages',
     *                                      'adapters' => [
     *                                      'adapter' => 'stream',
     *                                      'name'    => 'file.log',
     *                                      'options' => [
     *                                      'mode'     => 'ab',
     *                                      'option'   => null,
     *                                      'facility' => null
     *                                      ]
     *                                      ]
     *                                      ]
     */
    public function load($config): Logger
    {
        $config   = $this->checkConfig($config);
        $name     = $config['name'];
        $timezone = $config['timezone'] ?? null;
        $options  = $config['options'] ?? [];
        $adapters = $options['adapters'] ?? [];
        $data     = [];

        foreach ($adapters as $adapter) {
            $adapterClass    = $adapter['adapter'];
            $adapterFileName = $adapter['name'];
            $adapterOptions  = $adapter['options'] ?? [];

            $data[] = $this->adapterFactory->newInstance(
                $adapterClass,
                $adapterFileName,
                $adapterOptions
            );
        }

        return $this->newInstance($name, $data, $timezone);
    }

    /**
     * Returns a Logger object
     *
     * @param string            $name
     * @param array             $adapters
     * @param DateTimeZone|null $timezone
     *
     * @return Logger
     */
    public function newInstance(
        string        $name,
        array         $adapters = [],
        ?DateTimeZone $timezone = null
    ): Logger {
        return new Logger($name, $adapters, $timezone);
    }

    /**
     * Checks the config if it is a valid object
     *
     * @param mixed $config
     *
     * @return array
     * @throws Exception
     */
    private function checkConfig($config): array
    {
        if (true === is_object($config) && $config instanceof ConfigInterface) {
            $config = $config->toArray();
        }

        if (true !== is_array($config)) {
            throw new Exception(
                'Config must be array or Phalcon\Config\Config object'
            );
        }

        if (true !== isset($config['name'])) {
            throw new Exception(
                'You must provide "name" option in factory config parameter.'
            );
        }

        return $config;
    }
}
